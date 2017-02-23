<?php
class AjaxController extends Controller
{
    public function filters()
    {
        return array(
                'accessControl',
                array(
                    'application.filters.AccessFilter',
                ),
            );
    }
    
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
                array('allow',
                        'users'=>array('@'),
                ),
                array('deny',
                        'users'=>array('*'),
                ),
        );
    }

    /**
     * 在后台管理用户权限媒体时,显示媒体菜单树
     * note 2014/04/10 
     */
    public function actionShowMediaMenuTree()
    {
        $handle = Common::getStringParam('handle');
        list($firstCateArr, $secondCateArr, $mediaCateMap) = MediaList::getCateData(true);
        // 设置权限媒体的时候,不用再显示 全部媒体->... 对应的二级媒体 ... 与网络营销+搜索营销 ... 重叠
        if(array_key_exists('全部媒体', $mediaCateMap))
        {
            unset($mediaCateMap['全部媒体']);    
        }
        
        //取媒体name与id的映射关系数组
        $mapping  = MediaList::getMediaIDMapping();
        
        //1.对$mediaCateMap进行过转换,将key由name转为id
        $new_map = array();
        $new_first_cate_arr = array();
        foreach ($mediaCateMap as $key=>$val)
        {
           foreach ($val as $k=>$v){
               //判断$mediaCateMap中的媒体是否存在于 $mapping映射关系数组中
               if(isset($mapping[$key]) && isset($mapping[$k])) 
               {
                   $new_map[$mapping[$key]][$mapping[$k]] = $v;  
               }else
               {// $mediaCateMap中的媒体不存在于 $mapping映射关系数组中,则给空 
                // note 2014/04/12       
                   $new_map = array();
               }
               
           }
           
        }
        
        //2.对一级分类数据进行转换,将key由name转为id
        foreach ($firstCateArr as $key=>$val){
            //$mediaCateMap中的媒体是否存在于 $mapping映射关系数组中
            if(isset($mapping[$key])){
                $new_first_cate_arr[$mapping[$key]] = $val;
            }else{
                $new_first_cate_arr = array();
            }
            
        }
        echo MediaList::generateMediaMenuTree($new_map, $new_first_cate_arr);
        
        echo '<script type="text/javascript">';
        if ($handle == 'authority'){
            $username = Common::getStringParam('username');
            $privileges_obj = Privileges::getPrivileges($username);
            $privileges = isset($privileges_obj->privileges) ? unserialize($privileges_obj->privileges) : array();
            $privilege_medias = array_key_exists(Common::PRIVILEGE_TYPE_MEDIA, $privileges)&&!empty($privileges[Common::PRIVILEGE_TYPE_MEDIA]) ? $privileges[Common::PRIVILEGE_TYPE_MEDIA] : array();

            //此处要考虑两种情况:
            //1用户的权限媒体是全部
            //if((count($medias_menu_list) == 1) && ($medias_menu_list[0] == 'all')){
            if(in_array('all', $privilege_medias)){
                //echo  '$("#media-menu-tree-sub input[type=\'checkbox\']").attr("checked",true);';
                echo  '$("#media-menu-tree-sub input[type=\'checkbox\']").prop("checked",true);';
            }else{
                //2用户的权限媒体是除了'全部'的任何一种情况
                foreach ($privilege_medias as $k=>$v){
                    //echo  '$("#media-menu-tree-sub input[id=\''.$v.'\'][type=\'checkbox\']").attr("checked",true);';
                    echo  '$("#media-menu-tree-sub input[id=\''.$v.'\'][type=\'checkbox\']").prop("checked",true);';
                }
            }    

        }
        echo '</script>';
        exit();
    }
    
    /**
     * 获取城市ID、名称、缩写列表，用于城市提示框
     */
    public function actionAjaxcitiesautocomplete()
    {
        $cities = City::getCitiesInitials();
        echo CJSON::encode($cities);
    }
    
}
