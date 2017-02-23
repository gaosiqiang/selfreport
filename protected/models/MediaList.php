<?php

/**
 * This is the model class for table "media".
 *
 * The followings are the available columns in table 'media':
 * @property integer $id
 * @property string $media_name
 */
class MediaList extends CActiveRecord
{
    public static $notAllChildCate = array('搜索营销','网络营销','outer','手机','第三方运营','网站','整站');
    public static $need_summary_arr = array('网络营销','搜索营销','第三方运营');
    
    #下单平台列表
    public static $platList = array(
        'web' => '网站',
        'mobile' => '手机',
        'third' => '第三方平台',
        'jingdong' => '京东',
        'suning' => '苏宁',
        'taobao' => '淘宝',
        'baidutp' => 'baidutp'
    );
    
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
        return 'media';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('media_name', 'length', 'max'=>20),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, media_name', 'safe', 'on'=>'search'),
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
            'media_name' => '具体的含义以业务而定。对于销售部就是城市名，对于运营部就是一个二级菜单',
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
        $criteria->compare('media_name',$this->media_name,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return MediaList the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    
    /**
     * 获取所有的媒体
     * @author chensm
     */
    public static function getAllMedias()
    {
        $medias = self::model()->findAll();
        return $medias;
    }
    
    /**
     * 获取该媒体的子分类
     * @param int $parent_id
     * @param int $level #2:二级分类,3:三级分类
     * @author chensm
     */
    public static function getSubCates($parent_id, $level)
    {
        $cate = self::model()->findAll('parent_id=:parent_id and level=:level', array (
                ':parent_id' => $parent_id,
                ':level' => $level,
        ));
        return $cate;
    }
    
    /**
     * 由id找到父媒体
     * @param int $id
     */
    public static function getCatesBySecond($id){
        $sql = "select * from media where id = $id and level = 1";
        $result_parent_cate = Yii::app()->pdb->createCommand($sql)->queryRow();
        return $result_parent_cate;
    }
    
    /**
     * 获取一级媒体(从表中获取，不包括'全部媒体'和 '整站',方便以后扩展，只用在表中添加即可)
     */
    public static function getFirstCates(){
        $sql = "select media_name from media where parent_id = 0 and level = 1";
        $result_first_cates = Yii::app()->pdb->createCommand($sql)->queryAll();
        $result = array();
        foreach ($result_first_cates as $v){
            $result[] = $v['media_name'];
        }
        return $result;
    }
    
    /**
     * 获取一级媒体'全部媒体'的子媒体
     * eturn arr 一级媒体'全部媒体'对应的二级媒体
     */
    public static function getALLChildCates()
    {    
        $arr = self:: $notAllChildCate;
        $media = "'".implode( "','", $arr ) . "'";
        $sql = "select * from media where media_name not in ($media)";
        $result_all_cate = Yii::app()->pdb->createCommand($sql)->queryAll();
        return $result_all_cate;
    }
    
    
    
    /**
     * 获取媒体分类所有数据
     */
    public static function setCateAllData(&$data,&$map){
        $all = array();
        $ret = self::model()->findAll();

        //分类数据
        $data[1]['全部媒体'] = '全部媒体';
        
        //为'全部媒体'添加媒体
        $all_media = self::getALLChildCates();
        foreach($all_media as $k=>$val){
            $all['全部媒体'][$val['media_name']] = $val['media_name'];
        }
        
        //组成分类map
        foreach($ret as $v){
            $data[$v->level][$v->media_name] = $v->media_name;
            switch ($v->level) {
                case 1 :
                    $level2 = self::getSubCates($v->id, 2);
                    if(count($level2) > 0){//该权限媒体有子媒体时(网络营销+搜索营销+第三方运营+手机)
                        foreach ($level2 as $s) {
                            $map[$v->media_name][$s->media_name] = $s->media_name;
                        }
                    }else{//该权限媒体没有子媒体时(即子媒体跟父媒体一样,一对一) edm->edm, other->other,outer->outer,网站->网站,整站->整站
                        // 去掉本身outer->outer中的二级outer,组成联动：outer -> ...+edm
                        if($v->media_name != 'outer'){//组成: edm->edm, other->other
                            $map[$v->media_name][$v->media_name] = $v->media_name;
                        }
                    }    
                    break;
            }
        }
        
        //合并数据
        $map = $map + $all;
        
        //排序
        foreach ($map as $k=>$v){
            asort($map[$k]);
        }
        
        // 获取该媒体的子分类,同时包括该媒体.如 第三方运营的二级媒体有 京东+苏宁+淘宝 + 第三方运营(当不是all时,管理员给予 第三方运营的权限,该用户才有看到的权限)
        foreach ($map as $k=>$val){
            if(in_array($k,self::$need_summary_arr)){
                $map[$k] = $map[$k] + array($k=>$k);
            }
        }
        
    }
    /**
     * 设置媒体分类数据(有权限控制)
     * 缓存为1天
     */
    public static function setCateData(){
        
        $data = array();
        $map = array();
        $flag = '';
        //判断super级别
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        $medias = Yii::app()->user->getstate(Common::SESSION_MEDIA);
        if(in_array($super, Privileges::$super_admin) || $medias == 1){//取全部列表
            self::setCateAllData($data, $map);   
        }else{ // 进行权限判断
            $rsMedias = array();
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            if (Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD)) {
                $privileges = !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
                $rsMedias = array_key_exists(Common::PRIVILEGE_TYPE_MEDIA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_MEDIA]) ? $privileges[Common::PRIVILEGE_TYPE_MEDIA] : array();
            }
            
            if (isset($rsMedias) && !empty($rsMedias)){
                foreach ($rsMedias as $rs){
                    if ($rs == "all") {
                        //分类数据
                        self::setCateAllData($data, $map);
                        break;
                    }else if (strlen($rs)>0){
                        $data[1]['全部媒体'] = '全部媒体';
                        $media = self::getMediaByID($rs);
                        $level = $media['level'];
                        $name = $media['media_name'];
                        $parent_id = $media['parent_id'];
                        $data[$level][$name] = $name;
                        
                        //当用户的权限媒体为一级媒体时
                        if($level == 1){
                            $level2 = self::getSubCates($rs, 2);
                            if(count($level2) > 0){// 用户权限媒体有子媒体时(网络营销+搜索营销+第三方运营+手机)
                                foreach ($level2 as $s) {
                                    $data[$s->level][$s->media_name] = $s->media_name;
                                    $map[$name][$s->media_name] = $s->media_name; 
                                    $map['全部媒体'][$s->media_name] = $s->media_name;
                                }
                                
                            }else{ //没有子媒体时(edm->edm, other->other,网站->网站,整站->整站)
                                $data[2][$name] = $name;
                                $map[$name][$name] = $name;
                                
                                //当用户的权限媒体符合:1>没有子媒体2>为一级媒体,3>且为'全部媒体'的子媒体时=>,则有:edm->edm, other->other
                                if(in_array($name, array('edm','other'))){
                                    $map['全部媒体'][$name] = $name;
                                }else{ // 符合此条件的只有 网站->网站,整站->整站
                                    $flag = 'type1';    
                                }

                            }
                            
                            // 获取该媒体的子分类,同时包括该媒体.如 第三方运营的二级媒体有 京东+苏宁+淘宝 + 第三方运营
                            // 当不是all时,管理员给予 第三方运营的权限,该用户才有看到的权限
                            if(in_array($name,self::$need_summary_arr)){
                                $map[$name][$name] = $name;
                            }
                        }
                        
                        //当用户的权限媒体为二级媒体时,比如  京东，苏宁,0731tg......
                        if($level == 2){
                            //找出该二级媒体对应的一级媒体
                            $first_cate = self::getCatesBySecond($parent_id);
                            $data[$first_cate['level']][$first_cate['media_name']] = $first_cate['media_name'];
                            $map[$first_cate['media_name']][$name] = $name;
                            $map['全部媒体'][$name] = $name;
                        }
                        
                    }else{ // 用户没有任何权限媒体
                        $data[1] = array();
                        $data[2] = array();
//                         $map['全部媒体'] = array();
                        $flag = 'type2';
                    }
                }
            }else{ 
                // 用户没有任何权限媒体
                $data[1] = array();
                $data[2] = array();
                //$map['全部媒体'] = array();
                $flag = 'type2';
            }
        }
        
        if(isset($data[1]['outer'])){
            unset($data[1]['outer']);
        }
        
        //对map数组进行排序
        //将含数字的和不含数字的拆分为两个数组
        //note: 2014/04/15
        $num_arr = array();
        $string_arr = array();
        $no_permission = array();
        $map_sort = array();
        foreach ($map as $key=>$val){
            //note:2014/04/18 20:28
            //将空数组改为了$flag来衡量,so ...
            foreach ($val as $k=>$v){
                //取首字母进行判断
                if(is_numeric(substr($v, 0,1))){
                    $num_arr[$key][$k] = $v;
                }else{
                    $string_arr[$key][$k] = $v;
                }
            }    
            
        }
        
        //通过$flag来判断...因为前段页面展示的时候,一级分类默认显示'全部媒体',所以制造一个key='全部媒体'
        if($flag == 'type1' || $flag == 'type2'){
            $no_permission['全部媒体'] = array();
        }
        
        //分别对这两个数组进行排序
        //以0...,1...,2...,a...,b...,c...顺序排列 
        foreach ($num_arr as $k=>$v){
            asort($num_arr[$k],SORT_STRING);
        }
        
        foreach ($string_arr as $k=>$v){
            asort($string_arr[$k],SORT_STRING);
        }
        
        //合并数组
        $map_sort = array_merge_recursive($num_arr,$string_arr,$no_permission);
        
        //存入缓存
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA);
        $priv[Common::REDIS_SELFREPORT_MEDIA_CATE] = serialize($data);
        $priv[Common::REDIS_SELFREPORT_MEDIA_CATE_MAP] = serialize($map_sort);
        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA, 12*Common::REDIS_DURATION);
    }
    
    /**
     * 设置媒体列表(无联动)
     */
    public static function setCateMediaNoLinkage(){
        $ret = array();
        //$list = array('all'=>'请选择媒体');
        //判断super级别
        $super = Yii::app()->user->getstate(Common::SESSION_SUPER);
        $medias = Yii::app()->user->getstate(Common::SESSION_MEDIA);
        if(in_array($super, Privileges::$super_admin) || $medias == 1){//取全部列表
            $mediaArr = MediaList::getAllMedias();
            foreach ($mediaArr as $media) {
                $ret[$media->media_name] = $media->media_name;
            }
        }else{
            $rsMedias = array();
            $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT);
            if (Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_PRIVILEGES_SELFREPORT, Common::REDIS_PRIVILEGES_FIELD)) {
                $privileges = !empty($priv[Common::REDIS_PRIVILEGES_FIELD]) ? unserialize($priv[Common::REDIS_PRIVILEGES_FIELD]) : array();
                $rsMedias = array_key_exists(Common::PRIVILEGE_TYPE_MEDIA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_MEDIA]) ? $privileges[Common::PRIVILEGE_TYPE_MEDIA] : array();
            }
            
            if (isset($rsMedias) && !empty($rsMedias))
            {
                foreach ($rsMedias as $rs)
                {
                    if ($rs == "all") {//取全部列表
                        $mediaArr = MediaList::getAllMedias();
                        foreach ($mediaArr as $media) {
                            $ret[$media->media_name] = $media->media_name;
                        }
                        break;
                    } else if (strlen($rs)>0){
                        $media = MediaList::getMediaByID($rs);
                        $level = $media['level'];
                        $name = $media['media_name'];
                        $parent_id = $media['parent_id'];
                        
                        //当用户的权限媒体为一级媒体时
                        if($level == 1){
                            $level2 = MediaList::getSubCates($rs, 2);
                            if(count($level2) > 0){// 用户权限媒体有子媒体时(网络营销+搜索营销+第三方运营+手机)
                                foreach ($level2 as $s) {
                                    $ret[$s->media_name] = $s->media_name; 
                                }
                                
                            }else{ //没有子媒体时(edm->edm, other->other,网站->网站,整站->整站)
                                $ret[$name] = $name;
                            }
                            // 获取该媒体的子分类,同时包括该媒体.如 第三方运营的二级媒体有 京东+苏宁+淘宝 + 第三方运营
                            // 当不是all时,管理员给予 第三方运营的权限,该用户才有看到的权限
                            if(in_array($name,self::$need_summary_arr)){
                                $ret[$name] = $name;
                            }
                        }
                        
                        //当用户的权限媒体为二级媒体时,比如  京东，苏宁,0731tg......
                        if($level == 2){
                            $ret[$name] = $name;
                        }
                    }
                }
            }    
        }
        
        asort($ret);
        //$ret = $list + $ret;
        
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA);
        $priv[Common::REDIS_SELFREPORT_MEDIA_NO_LINKAGE] = serialize($ret);
        Yii::app()->redis->getClient()->setTimeout(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA, 12*Common::REDIS_DURATION);
    }
    
    /**
     * 获取媒体分类列表
     * @param string $flag  列表类型true=>二级联动;false无二级联动
     * @return array
     */
    public static function getCateData($type = true)
    {   
        $priv = new ARedisHash(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA);
        
        if($type){
            $media_cate = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA, Common::REDIS_SELFREPORT_MEDIA_CATE) ? $priv[Common::REDIS_SELFREPORT_MEDIA_CATE] : '';
            $mediaCateMap = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA, Common::REDIS_SELFREPORT_MEDIA_CATE_MAP) ? $priv[Common::REDIS_SELFREPORT_MEDIA_CATE_MAP] : '';
           
            if(empty($media_cate) || empty($mediaCateMap)){
                self::setCateData();
            }
                
            $media_cate = $priv[Common::REDIS_SELFREPORT_MEDIA_CATE];
            $media_cate = unserialize($media_cate);
            
            $mediaCateMap = $priv[Common::REDIS_SELFREPORT_MEDIA_CATE_MAP];
            $mediaCateMap = unserialize($mediaCateMap);
            
            $firstCateArr = $media_cate[1];
            $secondCateArr = $media_cate[2];
            return array($firstCateArr, $secondCateArr, $mediaCateMap);
        }else{
            $media_no_linkage = Yii::app()->redis->getClient()->hExists(Common::REDIS_ACCESS.Yii::app()->user->name.Common::REDIS_SELFREPORT_MEDIA, Common::REDIS_SELFREPORT_MEDIA_NO_LINKAGE) ? $priv[Common::REDIS_SELFREPORT_MEDIA_NO_LINKAGE] : '';
            if(empty($media_no_linkage)){
                self::setCateMediaNoLinkage();
            }
            $media_no_linkage = $priv[Common::REDIS_SELFREPORT_MEDIA_NO_LINKAGE];
            $media_no_linkage = unserialize($media_no_linkage);
            return $media_no_linkage;
        }
    }
    
    
    
    /**
     * 通过id获取媒体
     * @author chensm
     */
    public static function getMediaByID($id)
    {
        $media = self::model()->findByPk($id);
        return $media;
    }
    
    /**
     * 通过name获取该媒体id
     * @author chensm
     */
    public static function getMediaByName($name)
    {
       $sql = "select id from media where media_name = '$name'";
       $media = Yii::app()->pdb->createCommand($sql)->queryRow();
       return $media;
    }
    
    
    /**
     * 生成媒体目录树->媒体权限配置弹出浮层用
     * @param $menu_map
     * @param $first_menu_arr
     */
    public static function generateMediaMenuTree($menu_map, $first_menu_arr)
    {
        $menu_tree = '';
        if (!empty($menu_map) && !empty($first_menu_arr)) {
             $menu_tree = '<div class="col-md-12" style="margin-bottom:20px;width:940px;">';
             $loop = 0;
             foreach ($first_menu_arr as $first_id => $first) {
                 //对一级菜单value赋一个表中不存在的id
                 $temp_id = -1;
                 $temp_value = 'all';
                 if (++$loop == 11) {
                     $loop = 1;
                     $menu_tree .= "</div><div class=\"col-md-12\" style=\"margin-bottom:20px;\">";
                 }
                 $menu_tree .= "<li id=\"$first\" style=\"float:left;\">";
                 if($first_id == 'all_media'){
                     $menu_tree .= "<input id=\"all_media\" type=\"checkbox\" name=\"medias[]\" value=\"".$temp_value."\" style=\"width:20px;float:left;\">";
                 }else{
                     $menu_tree .= "<input id=\"$first_id\" type=\"checkbox\" name=\"medias[]\" value=\"".$temp_id."\" style=\"width:20px;float:left;\">";
                 }
                 
                 $menu_tree .= "<label class=\"media-menu-tree-collapse\" style=\"display:inline\"> - ".$first."</label>";
                             
                 if (array_key_exists($first_id, $menu_map)) {
                     $second_menu_arr = $menu_map[$first_id];
                     if (!empty($second_menu_arr)) {
                         $menu_tree .= "<ul class=\"tab nav\" style=\"display: block;\">";
                         foreach ($second_menu_arr as $second_id => $second) {
                             $menu_tree .= "<li id=\"$second_id\">";
                             $menu_tree .= "<input id=\"$second_id\" type=\"checkbox\" name=\"medias[]\" value=\"".$second_id."\" style=\"width:20px;float:left;\">";
                             $menu_tree .= "<label class=\"media-menu-tree-collapse\" style=\"display:inline\"> - ".$second."</label>";
                             $menu_tree .= "</li>";
                         }
                         $menu_tree .= "</ul>";
                     }
                 }
                 $menu_tree .= "</li>";
             }
             $menu_tree .= "</div>";
         }
         return $menu_tree;
    }
    
    
    /**
     * 媒体名称与id的映射关系
     * note 2014/04/12
     * @return array
     */
    public static function getMediaIDMapping()
    {
        $sql = "select id,media_name from media";
        $data = Yii::app()->pdb->createCommand($sql)->queryAll();
        $mapping = array();
        foreach ($data as $k=>$v){
            $mapping[$v['media_name']] = $v['id'];    
        }
        //添加全部媒体...
        $mapping['全部媒体'] = 'all_media';
        return $mapping;
    }
    
    /**
     * 根据媒体ID获取名称，用于将权限中保存的ID转换为名称
     * @param array $media_ids
     */
    public static function getMediaNameByIDs($media_ids)
    {
        $result = array();
        if(is_array($media_ids) && !empty($media_ids)) {
            $criteria=new CDbCriteria;
            $criteria->addInCondition('id', $media_ids);
            $models = self::model()->findAll($criteria);
            foreach($models as $model){
                $result[] = $model->media_name;
            }
        }
        return $result;
    }
    
}
