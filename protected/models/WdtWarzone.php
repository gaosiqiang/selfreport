<?php
/**
 * This is the model class for table "wdt_warzone".
 *
 * The followings are the available columns in table 'wdt_warzone':
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $update_time
 * @property integer $is_delete
 */
class WdtWarzone extends CActiveRecord
{
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
        return 'wdt_warzone';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, update_time', 'required'),
            array('parent_id, is_delete', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>30),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, parent_id, name, update_time, is_delete', 'safe', 'on'=>'search'),
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
                'warzone' => array(self::HAS_ONE, 'Warzone', '', 'on'=>'warzone.name = t.name', 'joinType' => 'INNER JOIN'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'parent_id' => '0-战区分区,其他-战区分档',
            'name' => '战区名称',
            'update_time' => '更新时间',
            'is_delete' => '是否被删除,0-否,1-是',
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
        // Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('parent_id',$this->parent_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('update_time',$this->update_time,true);
        $criteria->compare('is_delete',$this->is_delete);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return WdtWarzone the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 根据战区ID获取网店通战区
     * @param array $warzone_ids
     */
    public static function getWarzonesByID($warzone_ids)
    {
        $results = array();
        if (!is_array($warzone_ids))
            $warzone_ids = array();
    
        $criteria=new CDbCriteria;
        $criteria->with = 'warzone';
        $criteria->compare('is_delete', 0);
        $criteria->addInCondition('warzone.id', $warzone_ids);
        $models = self::model()->findAll($criteria);
    
        if (!empty($models)) {
            foreach ($models as $model) {
                $results[$model->id] = $model->name;
            }
        }
        return $results;
    }
}
