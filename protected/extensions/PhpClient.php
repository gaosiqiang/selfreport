<?php
class PhpClient
{
	private static $_ch = null;
	private static $_header;
	private static $_body;
	private static $_cookie_file;
	private static $_error;
	private static $_auth_user;
	private static $_auth_pass;
	private static $_url;
	
	private static $_use_proxy = false;
	private static $_proxy_host;
	private static $_proxy_port;
	private static $_proxy_type = 'HTTP'; // or SOCKS5
	private static $_proxy_auth = 'BASIC'; // or NTLM
	private static $_proxy_user;
	private static $_proxy_pass;
	
	public static $referer;
	public static $timeout = 30;
	public static $verify_ssl = false;
	public static $debug = false;
	public static $followlocation = true;
	
	public static $user_agent = 'Mozilla/5.0 (compatible; Jieshi/1.0; +http://www.jieshi.com/spider/)';

	/**
	 *
	 * @param string $url
	 * @param string $cookie_file
	 * @param array	 $postdate
	 */
	private static function doRequest($url, $cookie_file = '', $postdate = '', $ajax = false)
	{
		self::$_url = $url;
		if(!self::$_ch)
			self::$_ch = curl_init();
		if(self::$_use_proxy)
		{
			curl_setopt(self::$_ch, CURLOPT_PROXYTYPE, self::$_proxy_type == 'HTTP' ? CURLPROXY_HTTP : CURLPROXY_SOCKS5);
			curl_setopt(self::$_ch, CURLOPT_PROXY, self::_proxy_host);
			curl_setopt(self::$_ch, CURLOPT_PROXYPORT, self::$_proxy_port);
			if(self::$_proxy_user)
			{
				curl_setopt($this->_ch, CURLOPT_PROXYAUTH, $this->_proxy_auth == 'BASIC' ? CURLAUTH_BASIC : CURLAUTH_NTLM);
				curl_setopt($this->_ch, CURLOPT_PROXYUSERPWD, "[{$this->_proxy_user}]:[{$this->_proxy_pass}]");
			}
		}
		
		// https
		$bits = parse_url($url);
		if(!self::$verify_ssl || (isset($bits['scheme']) && strtolower($bits['scheme']) == 'https'))
		{
			curl_setopt(self::$_ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt(self::$_ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		
		// header
		$header = array(
			"User_agent: " . self::$user_agent,
			"Accept: application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
			"Cache_control: max-age=0",
			"Accept_language: zh-CN,zh;q=0.8",
			//"Accept_encoding: gzip,deflate,sdch",
			'Accept_charset: utf-8,GBK;q=0.7,*;q=0.3',
			'Connection: keep-alive'
		);
		
		// ajax
		if($ajax)
		{
			$header[] = 'X-Requested-With: XMLHttpRequest';
		}
		
		// referer 
		curl_setopt(self::$_ch, CURLOPT_AUTOREFERER, true);
		if(self::$referer)
		{
			//curl_setopt(self::$_ch, CURLOPT_REFERER, self::$referer);
			$header[] = 'Referer: ' . self::$referer;
			self::$referer = '';
		}
		
		// 
		curl_setopt(self::$_ch, CURLOPT_HTTPHEADER, $header);
		
		// cookie
		if($cookie_file)
		{
			curl_setopt(self::$_ch, CURLOPT_COOKIEJAR, $cookie_file);
			curl_setopt(self::$_ch, CURLOPT_COOKIEFILE, $cookie_file);
			self::$_cookie_file = $cookie_file;
		}
		
		// post
		if(!empty($postdate))
		{
			$_post_data = '';
			foreach($postdate as $key => $value)
			{
				$_post_data .= $key . '=' . $value . '&';
			}
			curl_setopt(self::$_ch, CURLOPT_POST, true);
			curl_setopt(self::$_ch, CURLOPT_POSTFIELDS, $_post_data);
		}
		
		// setopt prames
		curl_setopt(self::$_ch, CURLOPT_URL, $url);
		//curl_setopt(self::$_ch, CURLOPT_USERAGENT, self::$user_agent);
		curl_setopt(self::$_ch, CURLOPT_TIMEOUT, self::$timeout);
		curl_setopt(self::$_ch, CURLOPT_HEADER, true);
		// 
		curl_setopt(self::$_ch, CURLOPT_FRESH_CONNECT, true);
		// 
		curl_setopt(self::$_ch, CURLOPT_RETURNTRANSFER, true);
		// followlocationת
		if(self::$followlocation)
		{
			curl_setopt(self::$_ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt(self::$_ch, CURLOPT_MAXREDIRS, 10);
		}
		
		$response = curl_exec(self::$_ch);
		$errno = curl_errno(self::$_ch);
		
		if($errno > 0)
		{
			self::$_error = curl_error(self::$_ch);
			return false;
		}
		
		$header_size = curl_getinfo(self::$_ch, CURLINFO_HEADER_SIZE);
		self::$_header = substr($response, 0, $header_size);
		self::$_body = substr($response, $header_size);
		self::curl_close();
		return true;
	}

	private static function curl_close()
	{
		curl_close(self::$_ch);
		self::$_ch = null;
	}

	/**
	 *
	 * @param string $url
	 * @param string $cookie_file
	 */
	public static function get($url, $cookie_file = '', $is_ajax = false)
	{
		if(self::doRequest($url, $cookie_file, '', $is_ajax))
		{
			return self::getBody();
		}
		else
		{
			
			return false;
		}
	}

	/**
	 *
	 * @param string $url
	 * @param string $cookie_file
	 * @param array	 $postdata
	 */
	public static function post($url, $cookie_file, $postdata, $is_ajax = false)
	{
		if(self::doRequest($url, $cookie_file, $postdata, $is_ajax))
		{
			return self::getBody();
		}
		else
		{
			echo self::getError();
		}
	}

	/**
	 *
	 * @param string $proxy_host	
	 * @param string $proxy_port	
	 * @param string $proxy_type	
	 * @param string $proxy_auth	
	 * @param string $proxy_user	
	 * @param string $proxy_pass	
	 */
	public static function setProxy($proxy_host, $proxy_port, $proxy_type = 'HTTP', $proxy_auth = 'BASIC', $proxy_user = '', $proxy_pass = '')
	{
		self::$_proxy_host = $proxy_host;
		self::$_proxy_host = $proxy_host;
		self::$_proxy_type = $proxy_type;
		self::$_proxy_auth = $proxy_auth;
		self::$_proxy_user = $proxy_user;
		self::$_proxy_pass = $proxy_pass;
		self::$_use_proxy = true;
	}

	/**
	 *
	 */
	public static function getBody()
	{
		return self::$_body;
	}

	/**
	 *
	 */
	public static function getHeader()
	{
		return self::$_header;
	}

	/**
	 *
	 */
	public static function getCookieFile()
	{
		return self::$_cookie_file;
	}

	public static function getError()
	{
		if(self::$debug && self::$_error)
		{
			return '<div style="border: 1px solid red; padding: 0.5em; margin: 0.5em;"><strong>PHPClient Debug:</strong> <pre>' . self::$_error . '</pre></div>';
		}
		else
		{
			error_log(date('Y-m-d H:i:s', time()) . ':' . self::$_url . '|' . self::$_error . "\n", 3, "../protected/runtime/client_error.log");
		}
	}

}