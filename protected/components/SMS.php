<?php
/**
 * 发送短信
 * @author chensm
 */
class SMS
{
	const SMS_SUCCESS = 1;
	const SMS_ERROR_MOBILE = 2;
	const SMS_ERROR_SEND = 3;

	public static function send($mobile, $msg)
	{
		if (preg_match('/^1[0-9]{10}$/', $mobile)) {
			if (isset($msg) && strlen($msg) > 0) {
				$url = "http://chufa.lmobile.cn/submitdata/service.asmx/g_Submit?sname=dlwwsj00&spwd=dx55tuanw3d4u&scorpid=&sprdid=1012818&sdst=".$mobile."&smsg=".urlencode($msg);

				//$state = Utility::execURL($url);
				$state = PhpClient::get($url);
				if ($state === FALSE) {
					return self::SMS_ERROR_SEND;
					
				} else {
					return self::SMS_SUCCESS;
					
				}
				
			} else {
				return self::SMS_ERROR_SEND;
				
			}
		} else {
			return self::SMS_ERROR_MOBILE;
		}
	}
}
