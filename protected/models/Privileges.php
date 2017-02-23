<?php

/**
 * This is the model class for table "privileges".
 *
 * The followings are the available columns in table 'privileges':
 * @property integer $user_id
 * @property string $username
 * @property string $user_group_id
 * @property integer $is_super
 * @property string $privileges
 */
class Privileges extends CActiveRecord
{
    public static $super_admin = array('1','2','3');    //超级权限用户值
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'privileges';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, username', 'required'),
            array('user_id, is_super', 'numerical', 'integerOnly'=>true),
            array('username', 'length', 'max'=>32),
            array('user_group_id, privileges', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('user_id, username, user_group_id, is_super, privileges', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'user_id' => '用户ID',
            'username' => '用户名',
            'user_group_id' => '用户组ID',
            'is_super' => '是否超级管理员，1超级管理员，2报表配置员，3全报表查看用户',
            'privileges' => '用户权限',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('user_id',$this->user_id);
        $criteria->compare('username',$this->username,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Privileges the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 根据用户名取得用户权限
     * @author chensm
     * @param string $username
     * @return User
     */
    public static function getPrivileges($username)
    {
        $username = strtolower($username);
        $user = self::model()->find('username=:username', array(
                ':username' => $username
        ));
        return $user;
    }
    
    /**
     * 获取具有某个城市权限的用户
     * @param int $city_id
     * @return array
     */
    public static function getUserByCity($city_id)
    {
        $result = array();
        $models = self::model()->findAll();
        foreach ($models as $model) {
            $privileges = !empty($model->privileges) ? unserialize($model->privileges) : array();
            $cities = array_key_exists('cities', $privileges)&&!empty($privileges['cities']) ? $privileges['cities'] : array();
            if (in_array($city_id, $cities)) {
                $result[] = $model->username;
            }
        }
        return $result;
    }
    
    /**
     * 获取具有某个用户组权限的用户
     * @param int $user_group_id
     * @return array
     */
    public static function getUserIDByGroup($user_group_id)
    {
        $result = array();
        /* $models = self::model()->findAll('user_group_id=:user_group_id',array(':user_group_id'=>$user_group_id));
        foreach ($models as $model) {
            $result[] = $model->user_id;
        } */
        $models = self::model()->findAll();
        foreach ($models as $model) {
            if (isset($model->user_group_id) && $user_group_id == $model->user_group_id) {
                $result[] = $model->user_id;
            } else {
                $user_group = isset($model->user_group_id)&&!empty($model->user_group_id)&&preg_match('/^a:/', $model->user_group_id) ? unserialize($model->user_group_id) : array();
                if (in_array($user_group_id, $user_group)) {
                    $result[] = $model->user_id;
                }
            }
        }
        return $result;
    }
}
