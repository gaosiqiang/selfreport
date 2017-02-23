<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    const ERROR_NONE = 0; //无错误
    const ERROR_EXPIRED = 3; //账号已过期
    const ERROR_FORBIDDEN = 4; //账号被禁用
    
    /**
     * Authenticates a user.
     * 
     * @return boolean whether authentication succeeds.
     */
    private $_id;
    public function authenticate()
    {
        $user = Admin::getUserInformation($this->username);
        if ($user === null)
        {
            $this->errorCode = self::ERROR_USERNAME_INVALID; //用户名错误
        }
        else if ($user->status == 0)
        {
            $this->errorCode = self::ERROR_FORBIDDEN; //账号被禁用
        }
        else if (time() - $user->create_time > 3600 * 24 * 60)
        {
            $this->errorCode = self::ERROR_EXPIRED; //账号超过60天
        }
        else if (!$user->validatePassword($this->password))
        {
            $this->errorCode = self::ERROR_PASSWORD_INVALID; //密码不正确
        }
        else
        {
            $this->_id = $user->id;
            //$this->setstate('cities', $user->cities);
            //$this->setstate('privileges', $user->privileges);
            $this->errorCode = self::ERROR_NONE;
        }
        return $this->errorCode == self::ERROR_NONE;
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
}