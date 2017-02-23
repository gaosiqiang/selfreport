<?php

/**
 * PasswordForm class.
 * PasswordForm is the data structure for keeping
 * user change password form data. It is used by the 'setting' action of 'UserController'.
 */
class PasswordForm extends CFormModel
{
	public $password;
	public $newPassword;
	public $verifyPassword;
	
	private $_identity;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('password, newPassword, verifyPassword','required', 'on'=>'update' ,'message'=>'请输入 {attribute}'),
			array('password', 'authenticate', 'on'=>'update'),
			array('password, newPassword', 'length', 'min'=>8, 'max'=>32, 'tooShort'=>'{attribute}至少8个字符', 'tooLong'=>'{attribute} 最多32个字符'),
			//array('newPassword', 'match', 'pattern'=>'/^(?![0-9_\W]+$)(?![a-zA-Z_\W]+$)(?![a-zA-Z0-9_]+$)[\x20-\x7E]+$/','message'=>'{attribute} 必须含有字母和数字'),
			array('newPassword', 'checkpass'),
			array('verifyPassword','compare','compareAttribute'=>'newPassword','message'=>'两次密码不一致'),
			array('password, newPassword, verifyPassword', 'safe'),
		);
	}
	
	/**
	 * Declares attribute labels.
	 */
	
	public function attributeLabels()
	{
		return array(
			'password' => '原密码',
			'newPassword' => '新密码',
			'verifyPassword' => '确认密码',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate()
	{
		if(!$this->hasErrors())
		{
			$this->_identity = new UserIdentity(Yii::app()->user->name, $this->password);
			if (!$this->_identity->authenticate())
				$this->addError('password', '原密码错误');
		}
	}

	/**
	 * Authenticates the newPassword.
	 * This is the 'checkpass' validator as declared in rules().
	 */
	public function checkpass()
	{
		if(!$this->hasErrors())
		{
			$this->newPassword = Utility::convertStrType($this->newPassword, 'TOSBC');
			$this->verifyPassword = Utility::convertStrType($this->verifyPassword, 'TOSBC');
			if (!preg_match('/^(?![0-9_\W]+$)(?![a-zA-Z_\W]+$)(?![a-zA-Z0-9]+$)[\x20-\x7E]+$/', $this->newPassword))
				$this->addError('newPassword', '密码必须含有字母、数字和符号');
		}
	}

	/**
	 * 修改密码
	 * @author sangxiaolong
	 * @return boolean whether update is successful
	 */
	public function updatePassword()
	{
		$user = Admin::getUserInformation(Yii::app()->user->name);
		$user->password = $user->hashPassword($this->newPassword, $user->salt);
		if ($user->save())
			return true;
		//var_dump($user->getErrors());exit;
		return false;
	}
}