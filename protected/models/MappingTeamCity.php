<?php

/**
 * This is the model class for table "mapping_team_city".
 *
 * The followings are the available columns in table 'mapping_team_city':
 * @property integer $id
 * @property integer $team_id
 * @property string $team_name
 * @property integer $team_type
 * @property integer $branch_id
 * @property integer $city_id
 */
class MappingTeamCity extends CActiveRecord
{
    public static $types_with_branch_for_wdt = array(1,2,3,4,5);    //网店通业务使用的分部团队
    public static $types_with_city_for_group = array(1,2,3,4,5);    //团购业务使用的城市团队
    
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
        return 'mapping_team_city';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, team_id, team_type, branch_id, city_id', 'numerical', 'integerOnly'=>true),
            array('team_name', 'length', 'max'=>30),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, team_id, team_name, team_type, branch_id, city_id', 'safe', 'on'=>'search'),
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
            'team_id' => '团队ID',
            'team_name' => '团队名称',
            'team_type' => '团队类型,1-综合,2-行业,3-网店通,4-酒店,5事业部,0-其他',
            'branch_id' => '分部ID',
            'city_id' => '城市ID',
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

        $criteria->compare('team_id',$this->team_id);
        $criteria->compare('team_name',$this->team_name,true);
        $criteria->compare('team_type',$this->team_type);
        $criteria->compare('branch_id',$this->branch_id);
        $criteria->compare('city_id',$this->city_id);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return MappingTeamCity the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * 根据城市获取团队，用于联动列表
     * @param string|int $branch    参数可能是城市ID或名称
     */
    public static function getLinkedTeamsByCity($city)
    {
        if (is_numeric($city)) {
            $key = Common::REDIS_CITYSTRUCT_CITY_TEAMS;
        } else {
            $key = Common::REDIS_CITYSTRUCT_CITY_NAME_TEAMS;
        }
    
        $city_teams = Common::getOnePrivCityStruct($key);
        return array_key_exists($city, $city_teams) ? $city_teams[$city] : array();
    }
    
    /**
     * 根据分部获取团队，用于联动列表
     * @param string|int $branch    参数可能是分部ID或名称
     */
    public static function getLinkedTeamsByBranch($branch)
    {
        if (is_numeric($branch)) {
            $key = Common::REDIS_CITYSTRUCT_CITY_TEAMS;
        } else {
            $key = Common::REDIS_CITYSTRUCT_CITY_NAME_TEAMS;
        }
    
        $branch_teams = Common::getOnePrivWdtCityStruct($key);
        return array_key_exists($branch, $branch_teams) ? $branch_teams[$branch] : array();
    }
}
