<?php

/**
 * This is the model class for table "announcement".
 *
 * The followings are the available columns in table 'announcement':
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $create_time
 * @property integer $author_id
 * @property integer $is_delete
 */
class Announcement extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Announcement the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'announcement';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('title, content, start_time, end_time', 'required'),
            array('end_time', 'compare', 'compareAttribute'=>'start_time', 'operator'=>'>', 'message'=>'结束时间必须大于开始时间'),
            array('title', 'length', 'max'=>20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, title, content, start_time, end_time, create_time, author_id, is_delete', 'safe', 'on'=>'search'),
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
            'title' => '公告标题',
            'content' => '公告内容',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'create_time' => '创建时间',
            'author_id' => '作者ID',
            'is_delete' => '是否删除',
        );
    }
    
    protected function beforeSave()
    {
        if (parent::beforeSave())
        {
            if ($this->isNewRecord)
            {
                $this->author_id = Yii::app()->user->id;
                $this->create_time = time();
            }
            return true;
        }
        return false;
    }
    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('title',$this->title,true);
        $criteria->compare('start_time',$this->start_time);
        $criteria->compare('end_time',$this->end_time);
        $criteria->compare('is_delete',$this->is_delete);
        $criteria->order = 'id DESC';
        
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    /**
     * 获取今日公告
     * @author sangxiaolong
     * @return Announcement
     */
    public static function getTodayAnnouncement()
    {
        $model = self::model()->find(array(
            'condition' => 'start_time<:time and end_time>:time and is_delete=0',
            'params' => array(':time' => time()),
            'order' => 'id DESC'
        ));
        return $model;
    }
    
    /**
     * 获取今日公告数量
     * @author chensm
     * @return Announcement
     */
    public static function getTodayAnnouncementCount()
    {
        $model = self::model()->count(array(
            'condition' => 'start_time<:time and end_time>:time and is_delete=0',
            'params' => array(':time' => time()),
        ));
        return $model;
    }
}