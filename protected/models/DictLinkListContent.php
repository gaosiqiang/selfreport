<?php

/**
 * This is the model class for table "dict_link_list_content".
 *
 * The followings are the available columns in table 'dict_link_list_content':
 * @property integer $id
 * @property integer $list_id
 * @property string $first_key
 * @property string $first_value
 * @property string $second_key
 * @property string $second_value
 * @property string $third_key
 * @property string $third_value
 * @property string $fourth_key
 * @property string $fourth_value
 */
class DictLinkListContent extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'dict_link_list_content';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('list_id', 'required'),
			array('list_id', 'numerical', 'integerOnly'=>true),
			array('first_key, second_key, third_key, fourth_key', 'length', 'max'=>100),
			array('first_value, second_value, third_value, fourth_value', 'length', 'max'=>18),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, list_id, first_key, first_value, second_key, second_value, third_key, third_value, fourth_key, fourth_value', 'safe', 'on'=>'search'),
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
			'list_id' => '联动列表ID',
			'first_key' => '一级列表内容项key',
			'first_value' => '一级列表内容项value',
			'second_key' => '二级列表内容项key',
			'second_value' => '二级列表内容项value',
			'third_key' => '三级列表内容项key',
			'third_value' => '三级列表内容项value',
			'fourth_key' => '四级列表内容项key',
			'fourth_value' => '四级列表内容项value',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('list_id',$this->list_id);
		$criteria->compare('first_key',$this->first_key,true);
		$criteria->compare('first_value',$this->first_value,true);
		$criteria->compare('second_key',$this->second_key,true);
		$criteria->compare('second_value',$this->second_value,true);
		$criteria->compare('third_key',$this->third_key,true);
		$criteria->compare('third_value',$this->third_value,true);
		$criteria->compare('fourth_key',$this->fourth_key,true);
		$criteria->compare('fourth_value',$this->fourth_value,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return DictLinkListContent the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
