<?php
/**
 * This is the model class for table "admin".
 *
 * The followings are the available columns in table 'admin':
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $salt
 * @property string $email
 */
class Admin extends CActiveRecord
{
    const VALIDATE_LIMIT = 5;            //验证/登录限制次数
    const TOKEN_CHECK_PASS = 0;            //验证通过
    const TOKEN_CHECK_ERROR = 1;        //验证码错
    const TOKEN_CHECK_INVALID = 2;        //验证无效
    const TOKEN_CHECK_OVER_TIMES = 3;    //验证错误次数超限
    const TOKEN_VALID = 1;                //验证码有效
    const TOKEN_INVALID = 0;            //验证码失效
    const TOKEN_VALID_TIME = 300;        //有效期5分钟
    const ACCOUNT_LOCK_MIN = 600;        //帐号锁定时长10分钟 600s
    const ACCOUNT_LOCK_HOUR = 3600;        //帐号锁定时长1小时 3600s
    const ACCOUNT_LOCK_DAY = 1;            //帐号锁定时长1天(自然天)
    const SMS_INTERVAL = 60;            //短信重发间隔60秒
    const COOKIE_VALIDATE_LIMIT = 10;    //免验证失败限制次数
    const COOKIE_VALIDATE_TIME = 43200;    //免验证时长

    //确认密码
    public $verifyPassword;
    //免验证密钥
    private $_validationKey;
    
    /* (non-PHPdoc)
     * @see CActiveRecord::getDbConnection()
    */
    public function getDbConnection() {
        return Yii::app()->udb;
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'admin';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('username, email, mobile', 'unique', 'message'=>'{attribute} 已被使用'),
            array('username, password, email, mobile','required','on'=>'create', 'message'=>'请输入 {attribute}'),
            array('email', 'email','message'=>'邮箱格式不正确'),
            array('username','length','min'=>4, 'max'=>16, 'tooShort'=>'{attribute} 至少4个字符', 'tooLong'=>'{attribute} 最多16个字符'),
            //array('username', 'match', 'pattern'=>'/^[a-zA-Z0-9]+$/','message'=>'{attribute} 不要含有特殊字符'),
            array('password', 'length', 'min'=>8, 'max'=>32, 'tooShort'=>'{attribute} 至少8个字符', 'tooLong'=>'{attribute} 最多32个字符'), //由于admin保存的密码为MD5码，所以不能校验密码明码规则
            //array('password', 'match', 'pattern'=>'/^(?![0-9_\W]+$)(?![a-zA-Z_\W]+$)(?![a-zA-Z0-9]+$)[\x20-\x7E]{8,32}$/','message'=>'{attribute} 必须含有字母、数字和符号'),
            //array('verifyPassword', 'compare', 'on'=>'create', 'compareAttribute'=>'password', 'message'=>'确认密码 不一致'),
            array('mobile', 'match', 'pattern'=>'/^1\d{10}$/','message'=>'{attribute} 格式有误'),
            array('username, email','safe','on' => 'search')
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'username' => '用户名',
            'email' => '邮箱',
            'password' => '密码',
            'verifyPassword' => '确认密码',
            'profile' => '个人信息',
            'login_time' => '上次登录',
            'cities' => '运营城市',
            'mobile' => '手机号码',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria();
        
        $criteria->compare('id', $this->id);
        $criteria->compare('username', $this->username, true);
        $criteria->compare('email', $this->email, true);
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria
        ));
    }
    
    public function beforeSave()
    {
        if (parent::beforeSave())
        {
            if ($this->isNewRecord)
            {
                $this->salt = $this->randSalt();
                $this->password = $this->hashPassword($this->password, $this->salt);
                $this->create_time = time();
            }
            else
            {
                $this->update_time = time();
            }
            return true;
        }
        return false;
    }
    
    /**
     * 生成随机码
     * @author sangxiaolong
     * @param int $length 随机码长度
     * @return string $string
     */
    public function randSalt($length = 6)
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz123456789';
        $string = '';
        for($i=1; $i<=$length; $i++)
        {
            $position=mt_rand()%strlen($chars);
            $string.=substr($chars,$position,1);
        }
        return $string;
    }

    /**
     * 验证用户密码
     * @author sangxiaolong
     * @params string $password 密码
     * @return boolean
     */
    public function validatePassword($password)
    {
        return $this->hashPassword($password,$this->salt)===$this->password;
    }

    /**
     * Generates the password hash.
     * @param string password
     * @return string hash
     */
    public function hashPassword($password,$salt)
    {
        return md5($salt.$password);
    }
    
    /**
     * 根据用户名取得用户信息
     * @author sangxiaolong
     * @param string $username 
     * @return User
     */
    public static function getUserInformation($username)
    {
        $username = strtolower($username);
        $user = self::model()->find('username=:username', array(
            ':username' => $username
        ));
        return $user;
    }

    /**
     * 保存验证密钥
     * @author chensm
     * @return boolean whether update is successful
     */
    public function saveSecretKey($secret_key)
    {
        $this->secret_key = $secret_key;
        if ($this->save())
            return true;
        //var_dump($this->getErrors());exit;
        return false;
    }

    /**
     * 验证用户密钥
     * @author chensm
     * @params string $token 一次性口令
     * @return boolean
     */
    public function validateToken($token)
    {
        $t = explode("-", $this->secret_key);
        $token_db = $t[0];
        $time = $t[1];
        $is_valid = $t[2];
        $error_times = $t[3];
        $now = time();
        
        if ($is_valid == self::TOKEN_VALID)
        {
            if ($error_times < self::VALIDATE_LIMIT)
            {
                if ($now - $time < self::TOKEN_VALID_TIME)
                {
                    if ($token == $token_db)
                    {
                        self::saveSecretKey($token_db."-".$time."-".self::TOKEN_INVALID."-".$error_times);
                        return self::TOKEN_CHECK_PASS;
                    } else {
                        $error_times++;
                        if ($error_times < self::VALIDATE_LIMIT)
                        {
                            self::saveSecretKey($token_db."-".$time."-".$is_valid."-".$error_times);
                            return self::TOKEN_CHECK_ERROR;
                        } else {
                            self::saveSecretKey($token_db."-".$time."-".self::TOKEN_INVALID."-".$error_times);
                            return self::TOKEN_CHECK_OVER_TIMES;
                        }
                    }
                } else {
                    self::saveSecretKey($token_db."-".$time."-".self::TOKEN_INVALID."-".$error_times);
                    return self::TOKEN_CHECK_INVALID;
                }
            } else {
                self::saveSecretKey($token_db."-".$time."-".self::TOKEN_INVALID."-".$error_times);
                return self::TOKEN_CHECK_OVER_TIMES;
            }
        } else {
            return self::TOKEN_CHECK_INVALID;
        }
    }

    /**
     * 锁定用户
     * @author chensm
     * @return boolean whether update is successful
     */
    public function lockUser($lock)
    {
        if ($lock == self::ACCOUNT_LOCK_DAY) {
            $tomorrow = date('Y-m-d',mktime(0,0,0,date("m"),date("d")+1,date("Y")));
            $this->lock_time = strtotime($tomorrow);
        } else {
            $this->lock_time = time()+$lock;
        }
        
        $this->status = 0;
        if ($this->save())
            return $this->lock_time;
        return false;
    }

    /**
     * @return string a randomly generated private key
     */
    protected function generateRandomKey()
    {
        return sprintf('%08x%08x%08x%08x',mt_rand(),mt_rand(),mt_rand(),mt_rand());
    }
    
    public function getValidationKey()
    {
        $this->_validationKey = $this->generateRandomKey();
        return $this->_validationKey;
    }
    
}
