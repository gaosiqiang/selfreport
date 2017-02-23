<?php

/**
 * This is the model class for table "data_source".
 *
 * The followings are the available columns in table 'data_source':
 * @property integer $id
 * @property string $name
 * @property integer $server_ip
 * @property string $database
 * @property string $charset
 * @property string $username
 * @property string $password
 */
class DataSource extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'data_source';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, server_ip, database, username, password, port', 'required'),
            array('name', 'unique'),
            array('name, charset', 'length', 'max'=>18),
            array('server_ip', 'numerical', 'integerOnly'=>true),
            array('database', 'length', 'max'=>100),
            array('username', 'length', 'max'=>50),
            array('password', 'length', 'max'=>255),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, name, server_ip, database, charset, username, password, port', 'safe', 'on'=>'search'),
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
            'id' => 'ID',
            'name' => '数据源名称',
            'server_ip' => '服务器IP',
            'database' => '数据库',
            'charset' => '字符集',
            'username' => '用户名',
            'password' => '密码',
            'port' => '端口号',
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

        if (!empty($this->server_ip)) {
            $ip = ip2long($this->server_ip);
            $ip = $ip !== false ? $ip : 0;
            $criteria->compare('server_ip',$ip);
        }

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return DataSource the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 获取数据源筛选项列表，id=>name
     */
    public static function getSelectList()
    {
        $result = array('0'=>'请选择数据源');
        $list = self::model()->findAll();
        foreach ($list as $ds) {
            $result[$ds->id] = $ds->name;
        }
        return $result;
    }
    
    /**
     * 根据ID获取数据源
     * @param int $id
     * @return array
     */
    public static function getDataSourceByID($id)
    {
        $result = array();
        $model = self::model()->findByPk($id);
        if ($model) {
            $result['id'] = $model->id;
            $result['name'] = $model->name;
            $result['server_ip'] = long2ip($model->server_ip);
            $result['database'] = $model->database;
            $result['charset'] = $model->charset;
            $result['username'] = $model->username;
            $result['password'] = Phpaes::AesDecrypt($model->password);
            $result['port'] = $model->port;
        }
        unset($model);
        return $result;
    }
}
