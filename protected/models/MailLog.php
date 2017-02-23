<?php

/**
 * This is the model class for table "mail_log".
 *
 * The followings are the available columns in table 'mail_log':
 * @property integer $id
 * @property integer $type
 * @property integer $send_time
 * @property string $email
 * @property string $subject
 * @property string $content
 * @property integer $status
 */
class MailLog extends CActiveRecord
{
    //邮件类型
    const TYPE_CREATE_USER = 1;                   //创建用户
    const TYPE_CHANGE_PASSWORD = 2;               //修改密码
    const TYPE_DCP_DATA_MISS = 3;                 //数据平台数据缺失报警
    const TYPE_DES_FLOW_DAILY = 4;                //流量数据日报(决策系统)
    const TYPE_DCP_SJPT_MISS = 5;                 //老数据平台数据缺失报警
    const TYPE_DCP_XSSJ_MISS = 6;                 //销售数据平台数据缺失报警
    const TYPE_DCP_JCFX_MISS = 7;                 //决策系统数据缺失报警
    const TYPE_DCP_DSF_MISS = 8;                  //第三方数据平台数据缺失报警
    const TYPE_DCP_FLOW_MISS = 9;                 //流量分析系统数据缺失报警
    const TYPE_DCP_JCFX_CITYPL_MISS = 10;         //决策系统-盈亏月报数据缺失报警
    const TYPE_DCP_ROUTOFF_FEEDBACK = 11;         //日常任务-网站意见反馈
    const TYPE_DCP_TABLE_SYNC = 12;         //数据平台city表和dim_city表的数据同步
    
    /* (non-PHPdoc)
     * @see CActiveRecord::getDbConnection()
    */
    public function getDbConnection() {
        return Yii::app()->pdb;
    }
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'mail_log';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('email', 'required'),
            array('type, send_time, status', 'numerical', 'integerOnly'=>true),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, type, send_time, email, subject, content, status', 'safe', 'on'=>'search'),
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
            'id' => '自增id',
            'type' => '邮件类型: 1创建用户,2修改密码,3数据平台数据缺失报警,4流量数据日报',
            'send_time' => '发送时间',
            'email' => '邮箱',
            'subject' => '邮件标题',
            'content' => '邮件内容',
            'status' => '发送状态: 1成功,0失败',
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

        $criteria->compare('type',$this->type);
        $criteria->compare('send_time',$this->send_time);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('subject',$this->subject,true);
        $criteria->compare('content',$this->content,true);
        $criteria->compare('status',$this->status);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return MailLog the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 保存短信日志
     */
    public static function createLog($email, $status, $type, $subject='', $content='')
    {
        //SELECT FROM_UNIXTIME(send_time,'%Y-%m-%d %H:%i:%S'), email, content, status FROM `mail_log` ORDER BY send_time DESC;
        $log = new MailLog();
        $log->email = $email;
        $log->status = $status === true ? 1 : 0;
        $log->type = $type;
        $log->subject = $subject;
        $log->content = $content;
        $log->send_time = time();
        
        if ($log->save())
            return true;
        return false;
    }
}
