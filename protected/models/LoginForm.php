<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $verifyCode;
	public $rememberMe;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, password', 'required','on'=>'login', 'message'=>'请输入 {attribute}'),
			array('username, password, verifyCode', 'required','on'=>'login', 'message'=>'请输入 {attribute}'),
			
			// rememberMe needs to be a boolean
			array('rememberMe', 'boolean'),
			// password needs to be authenticated
			array('password', 'authenticate'),
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
			array('username, password, rememberMe', 'safe'),
		);
	}
	
	/**
	 * Declares attribute labels.
	 */
	
	public function attributeLabels()
	{
		return array(
			'username' => '用户名',
			'email' => '邮箱',
			'password' => '密码',
			'verifyCode' => '验证码',
			'rememberMe' => '下次自动登录'
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$user = Admin::getUserInformation($this->username);
			if (isset($user) && $user->lock_time > 0 && $user->lock_time<=time() && $user->status==0)
			{
				$user->updateByPk($user->id, array('status' => 1, 'lock_time' => 0, 'login_failed' => 0));
				$user->status = 1;
				$user->lock_time = 0;
				$user->login_failed = 0;
			}
			$this->_identity = new UserIdentity($this->username, $this->password);
			if (!$this->_identity->authenticate())
			{
				switch ($this->_identity->errorCode)
				{
					case UserIdentity::ERROR_USERNAME_INVALID :
						$this->addError('username', '用户名或密码错误');
						break;
					case UserIdentity::ERROR_PASSWORD_INVALID :
						$this->addError('username', '用户名或密码错误');
						break;
					case UserIdentity::ERROR_FORBIDDEN :
						$this->addError('username', '账户已禁用');
						break;
					case UserIdentity::ERROR_EXPIRED :
						$this->addError('username', '账户已超过60天');
						break;
				}
				
				//密码错登录失败次数
				if ($this->_identity->errorCode == UserIdentity::ERROR_PASSWORD_INVALID)
				{
					//Yii::app()->session['login_error_times'] = isset(Yii::app()->session['login_error_times'])?Yii::app()->session['login_error_times']+1:1;
					$login_failed = $user->login_failed + 1;
					$user->updateByPk($user->id, array('login_failed' => $login_failed));
				}
			}
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if ($this->_identity === null)
		{
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate();
		}
		if ($this->_identity->errorCode === UserIdentity::ERROR_NONE)
		{
			$duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
			Yii::app()->user->login($this->_identity, $duration);
			Admin::model()->updateByPk(Yii::app()->user->id, array(
				'login_time' => time(),
				'status' => 1,
				'lock_time' => 0,
				'login_failed' => 0,
				'sms_times' => 0
			));
			return true;
		}
		else
			return false;
	}
}