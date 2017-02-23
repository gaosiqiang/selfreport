<?php
/**
 * 预生成数据供平台读取（该程序暂时用不到）
 * 执行： php yiic.php PreGenerate dictlist
 * @author chensm
 *
 */
ini_set('memory_limit','1536M');

class PreGenerateCommand extends CConsoleCommand
{
    // 默认action
    public $defaultAction='dictlist';
    
    /******************************************初始化列表筛选项表数据 start*********************************************/
    public function actionDictlist()
    {
        echo 'dictlist start'.date('Y-m-d H:i:s')."\n";
        
        $sql = "insert into dict_list(id, name, type) values(1, '一级分类', 1)";
        Yii::app()->db->createCommand($sql)->execute();
        $sql = "select id, name from dim_category where level=1";
        $result = Yii::app()->pdb->createCommand($sql)->query();
        while (($row = $result->read()) !== false)
        {
            $insert = "insert into dict_list_content(item_id, item, value_type, list_id) values(".$row['id'].", '".$row['name']."', 0, 1)";
            Yii::app()->db->createCommand($insert)->execute();
        }
        
        $sql = "insert into dict_list(id, name, type) values(2, '媒体类型', 1)";
        Yii::app()->db->createCommand($sql)->execute();
        $sql = "select id, media_name from media where level=1";
        $result = Yii::app()->pdb->createCommand($sql)->query();
        while (($row = $result->read()) !== false)
        {
            $insert = "insert into dict_list_content(item_id, item, value_type, list_id) values(".$row['id'].", '".$row['media_name']."', 1, 2)";
            Yii::app()->db->createCommand($insert)->execute();
        }
        
        $sql = "insert into dict_list(id, name, type) values(3, '大区', 1)";
        Yii::app()->db->createCommand($sql)->execute();
        $sql = "select id, name from region order by show_order";
        $result = Yii::app()->pdb->createCommand($sql)->query();
        while (($row = $result->read()) !== false)
        {
            $insert = "insert into dict_list_content(item_id, item, value_type, list_id) values(".$row['id'].", '".$row['name']."', 0, 3)";
            Yii::app()->db->createCommand($insert)->execute();
        }
        
        $sql = "insert into dict_list(id, name, type) values(4, '战区', 1)";
        Yii::app()->db->createCommand($sql)->execute();
        $sql = "select id, name from warzone";
        $result = Yii::app()->pdb->createCommand($sql)->query();
        while (($row = $result->read()) !== false)
        {
            $insert = "insert into dict_list_content(item_id, item, value_type, list_id) values(".$row['id'].", '".$row['name']."', 0, 4)";
            Yii::app()->db->createCommand($insert)->execute();
        }
        
        echo 'dictlist end'.date('Y-m-d H:i:s')."\n";
    }
    /******************************************初始化列表筛选项表数据 end*********************************************/
    
}
