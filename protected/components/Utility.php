<?php
/**
 * 工具类
 * @author sangxiaolong
 */
class Utility
{
    /**
     * 获取日期是星期几
     * @author sangxiaolong
     * @param $date
     * @return string
     */
    public static function date2week($date)
    {
        $weekarray = array("日","一","二","三","四","五","六");
        return $weekarray[date("w",strtotime($date))];
    }
    
    /**
     * 将秒转化为时间
     * @param int $sec
     */
    public static function sec2time($sec)
    {
        
        $sec = round($sec / 60);
        if ($sec >= 60)
        {
            $hour = floor($sec / 60);
            $min = $sec % 60;
            $res = $hour . ' 小时 ';
            $min != 0 && $res .= $min . ' 分';
        }
        else
        {
            $res = $sec . ' 分钟';
        }
        return $res;
    }

    /**
     * 将秒转化为分钟
     * @param int $sec
     */
    public static function sec2hour($sec)
    {
        $min = round($sec / 3600, 1);
        return $min;
    }
    
    /**
     * 获取指定月份天数
     * @author sangxiaolong
     * @param string $date
     * @return int
     */
    public static function getDays($date)
    {
        return date('t',strtotime($date));
    }
    
    /**
     * 获取某年某周周几的日期
     * @author chensm
     * @param string $year    ISO-8601 年份, date('o', time)
     * @param string $week    ISO-8601 周数, date('W', time)
     * @param string $day     ISO-8601 周几, date('N', time)
     * @return string
     */
    public static function getDateByWeek($year, $week, $day=null)
    {
        $date = '';
        if (!empty($year) && !empty($week) && preg_match('/\d{1,4}/', $year) && preg_match('/\d{1,2}/', $week)) {
            if ($week > 0 && $week < 54) {
                if (!empty($day)) {
                    $date = date('Y-m-d', strtotime($year.'-W'.$week.'-'.$day));
                } else {
                    $date = date('Y-m-d', strtotime($year.'-W'.$week));
                }
            }
        }
        return $date;
    }
    
    /**
     * 获取上周同天日期，例如今天星期一，则返回上周一的日期
     * @author sangxiaolong
     * @param string $date
     * @return string
     */
    public static function getLastWeek($date)
    {
        return date('Y-m-d', strtotime('-1 week', strtotime($date)));
    }
    
    /**
     * 获取上一月份
     * @author sangxiaolong
     * @param string $month
     * @return string
     */
    public static function getLastMonth($month)
    {
        return date('Y-m', strtotime('-1 month', strtotime($month)));
    }
    
    /**
     * 导出结果为excel文件
     * @author sangxiaolong
     * @param array $datas
     * @param array $table_array
     * @param string $excel_name
     */
    public static function exportExcel($datas, $map, $excel_name)
    {
        if (!$map || !$excel_name || !$datas)
        {
            return false;
        }
        Yii::import('application.extensions.*');
        $objPHPExcel = new PHPExcel();
        
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");
        
        $objPHPExcel->setActiveSheetIndex(0);
        
        // 表头
        $col=0;
        foreach ($map as $k=>$v)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $v);
            $col++;
        }
        
        // 数据
        $row = 2;
        foreach ($datas as $data)
        {
            $col = 0;
            foreach ($map as $k=>$v)
            {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data[$k]);
                $col++;
            }
            $row++;
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // 解决ie下文件名乱码
        //$excel_name = urlencode($excel_name);

        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = urlencode($excel_name);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        header('Content-Type: application/vnd.ms-excel');
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $excel_name . '.xls"');
        } else {
            header('Content-Disposition: attachment; filename="' . $excel_name . '.xls"');
        }
        
        //发送标题强制用户下载文件
        //header('Content-Type: application/vnd.ms-excel');
        //header('Content-Disposition: attachment;filename="' . $excel_name . '.xls"');
        header('Cache-Control: max-age=0');
        
        $objWriter->save('php://output');
    }
    
    /**
     * 判断用户浏览器是否为ie
     * @author sangxiaolong
     * @return bool
     */
    public static function is_ie()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
            return true;
        else
            return false;
    }
    
    /**
     * 导出结果为CSV文件
     * @author chensm
     * @param array $datas
     * @param array $map
     * @param string $excel_name
     */
    public static function exportCSV($datas, $map, $excel_name, $db=null, $offset=0)
    {
        //ini_set('memory_limit', '256M');
        
        $time_limit  = !empty(Yii::app()->params['export_time_limit']) ? Yii::app()->params['export_time_limit'] : Common::EXPORT_TIME_LIMIT;
        set_time_limit($time_limit);    //设置执行超时时间。ps.使用该函数时，需要关闭PHP的安全模式，也就是需要在PHP配置文件中将safe_mode的值设为off。
        
        if (!$map || !$excel_name || !$datas)
        {
            return false;
        }
    
        //发送标题强制用户下载文件
        //解决ie下文件名乱码
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = urlencode($excel_name);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        header('Content-Type: application/csv');
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.csv"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $excel_name . '.csv"');
        } else {
            header('Content-Disposition: attachment; filename="' . $excel_name . '.csv"');
        }
        header('Cache-Control: max-age=0');
    
        $output = fopen('php://output','w') or die("Can't open php://output");
    
        //由于使用EXCEL打开CSV时会使用ANSI编码，即GB2312，所以要先将内容转换编码
        //中文表头转换编码
        foreach ($map as $key => $title) {
            $map[$key] = iconv("UTF-8", "GBK//IGNORE", $title);
        }
        fputcsv($output, $map);    //首先打印表头
    
        $cnt = 0;
        $limit = !empty(Yii::app()->params['export_page_size']) ? Yii::app()->params['export_page_size'] : Common::EXPORT_PAGE_SIZE;
        if(!is_array($datas)&&$datas){
            //传入SQL输出数据
            if (empty($db)) {
                printf("Database connect failed.\n");
                exit();
            }
            
            $sql = $datas;
            $export_page_size = !empty(Yii::app()->params['export_page_size']) ? Yii::app()->params['export_page_size'] : Common::EXPORT_PAGE_SIZE;
            $limit = !empty(Yii::app()->params['export_block_size']) ? Yii::app()->params['export_block_size'] : Common::EXPORT_BLOCK_SIZE;
            $page = $export_page_size%$limit!=0 ? intval(ceil($export_page_size/$limit)) : $export_page_size/$limit;
            
            for ($i=1;$i<=$page;$i++) {
                if ($i==$page) {
                    $last_limit = $export_page_size - ($page - 1) * $limit;
                    $query_sql = "$sql limit $offset,$last_limit";
                } else {
                    $query_sql = "$sql limit $offset,$limit";
                }
                $offset += $limit;
                
                $reports = $db->createCommand($query_sql)->queryAll();
                foreach ($reports as $data) {
                    foreach ($data as $key => $value) {
                        $value = Utility::turnNull($value);
                        $data[$key] = iconv("UTF-8", "GBK//IGNORE", $value);
                    }
                    fputcsv($output, $data);
                }
                
                ob_flush();
                flush();
            }
        } else {
            //传入导出数据输出
            foreach ($datas as $data) {
                ++$cnt;
                if ($limit == $cnt) {
                    ob_flush();
                    flush();
                    $cnt = 0;
                }
                foreach ($data as $key => $value) {
                    $data[$key] = iconv("UTF-8", "GBK//IGNORE", $value);
                }
                fputcsv($output, $data);
            }
        }
    
        fclose($output) or die("Can't close php://output");
    }
    
    /**
     * 导出结果为CSV文件，并附带城市归属信息
     * @author chensm
     * @param array $datas          数据集合或sql语句。sql语句为条件参数绑定形式，结合sql_params使用
     * @param array $map            标题字段
     * @param string $excel_name    文件名称
     * @param array $show_columns   导出字段
     * @param array $city_div_cols  城市归属关系
     * @param array $week_cols      周数字段
     * @param CDbConnection $db     数据库连接
     * @param int $offset           数据偏移
     * @param array $sql_params     sql语句绑定参数
     */
    public static function exportCSVWithCityDivisions($datas, $map, $excel_name,$show_columns,$city_div_cols, $week_cols, $db, $offset, $sql_params, $count)
    {
        $time_limit  = !empty(Yii::app()->params['export_time_limit']) ? Yii::app()->params['export_time_limit'] : Common::EXPORT_TIME_LIMIT;
        set_time_limit($time_limit);    //设置执行超时时间。ps.使用该函数时，需要关闭PHP的安全模式，也就是需要在PHP配置文件中将safe_mode的值设为off。
    
        if (!$map || !$excel_name || !$datas)
        {
            return false;
        }
    
        //发送标题强制用户下载文件
        //解决ie下文件名乱码
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = urlencode($excel_name);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        header('Content-Type: application/csv');
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.csv"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $excel_name . '.csv"');
        } else {
            header('Content-Disposition: attachment; filename="' . $excel_name . '.csv"');
        }
        header('Cache-Control: max-age=0');
    
        $output = fopen('php://output','w') or die("Can't open php://output");
    
        //取城市与归属分区的对应关系
        $city_div_relations = Common::getCityDivisions();
        
        //由于使用EXCEL打开CSV时会使用ANSI编码，即GB2312，所以要先将内容转换编码
        //中文表头转换编码
        foreach ($map as $key => $title) {
            $map[$key] = iconv("UTF-8", "GBK//IGNORE", $title);
        }
        fputcsv($output, $map);    //首先打印表头
    
        $cnt = 0;
        $limit = !empty(Yii::app()->params['export_page_size']) ? Yii::app()->params['export_page_size'] : Common::EXPORT_PAGE_SIZE;
        if(is_string($datas)&&$datas){
            //传入SQL输出数据
            if (empty($db)) {
                printf("Database connect failed.\n");
                exit();
            }
    
            $sql = $datas;
            $export_page_size = !empty(Yii::app()->params['export_page_size']) ? Yii::app()->params['export_page_size'] : Common::EXPORT_PAGE_SIZE;
            $limit = !empty(Yii::app()->params['export_block_size']) ? Yii::app()->params['export_block_size'] : Common::EXPORT_BLOCK_SIZE;
            $page_size = $export_page_size;                 //导出数据量设为预设值，因为如果不满足以下判断，导出数据量只能是预设值
            if ($count <= $export_page_size) {              //数据总量小于预设值时，导出数据量为数据总量
                $page_size = $count;
            } else {
                $difference = $count - $offset;             //数据总量大于预设值时，数据总量-偏移=剩余数量
                if ($difference <= $export_page_size) {     //剩余数量小于预设值时，导出数据量为剩余数量
                    $page_size = $difference;
                }
            }
            $page = $page_size%$limit!=0 ? intval(ceil($page_size/$limit)) : $page_size/$limit;
    
            for ($i=1;$i<=$page;$i++) {
                if ($i==$page) {
                    $last_limit = $page_size - ($page - 1) * $limit;
                    $query_sql = "$sql limit $offset,$last_limit";
                } else {
                    $query_sql = "$sql limit $offset,$limit";
                }
                $offset += $limit;
    
                $command = $db->createCommand($query_sql);
                if (!empty($sql_params)) {
                    foreach ($sql_params as $name => $value) {
                        $command->bindValue($name, $value);
                    }
                }
                
                $reports = $command->queryAll();
                foreach ($reports as $data) {
                    $output_data = array();
                    foreach ($data as $key => $value) {
                        if (array_key_exists($key, $city_div_cols)) {
                            $divs = $city_div_cols[$key];
                            foreach ($divs as $k => $div) {
                                if (in_array($div, $show_columns)) {
                                    $val = Utility::getDivisionByCity($city_div_relations, $value, $div);
                                    $output_data[$div] = iconv("UTF-8", "GBK//IGNORE", $val);
                                }
                            }
                        } elseif (in_array($key, $week_cols)) {
                            $value = Utility::turnWeek($value);
                        }
                        $value = Utility::turnNull($value);
                        $value = Utility::convertCSVData($value);
                        $output_data[$key] = iconv("UTF-8", "GBK//IGNORE", $value);
                    }
                    fputcsv($output, $output_data);
                }
    
                ob_flush();
                flush();
            }
        }
        /*  else {
            //传入导出数据输出
            foreach ($datas as $data) {
                ++$cnt;
                if ($limit == $cnt) {
                    ob_flush();
                    flush();
                    $cnt = 0;
                }
                foreach ($data as $key => $value) {
                    $data[$key] = iconv("UTF-8", "GBK//IGNORE", $value);
                }
                fputcsv($output, $data);
            }
        } */
    
        fclose($output) or die("Can't close php://output");
    }
    
    /**
     * 处理单元数据带逗号、0开头的数字串、双引号的情况
     * @param string $value
     */
    public static function convertCSVData($value) {
        //收尾是双引号"时，无法正常处理
        if (strstr($value, '"') !== false) {
            $value = str_replace('"', '""', $value);
        }
        if (preg_match('/^0[0-9]+/', $value) || strstr($value, ',') !== false){
            $value = '="'.$value.'"';
        }
        return $value;
    }
    
    /**
     * 字符串半角和全角间相互转换
     * @author chensm
     * @param string $str 待转换的字符串
     * @param int $type TODBC:转换为全角；TOSBC，转换为半角
     * @return string 返回转换后的字符串
     */
    public static function convertStrType($str, $type) {
    
        $dbc = array(                                    //全角
                '０' , '１' , '２' , '３' , '４' ,
                '５' , '６' , '７' , '８' , '９' ,
                'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
                'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
                'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
                'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
                'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
                'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
                'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
                'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
                'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
                'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
                'ｙ' , 'ｚ' , '－' , '　' , '：' ,
                '．' , '，' , '／' , '％' , '＃' ,
                '！' , '＠' , '＆' , '（' , '）' ,
                '＜' , '＞' , '＂' , '＇' , '？' ,
                '［' , '］' , '｛' , '｝' , '＼' ,
                '｜' , '＋' , '＝' , '＿' , '＾' ,
                '￥' , '￣' , '｀'
        );
    
        $sbc = array(                                    //半角
                '0', '1', '2', '3', '4',
                '5', '6', '7', '8', '9',
                'A', 'B', 'C', 'D', 'E',
                'F', 'G', 'H', 'I', 'J',
                'K', 'L', 'M', 'N', 'O',
                'P', 'Q', 'R', 'S', 'T',
                'U', 'V', 'W', 'X', 'Y',
                'Z', 'a', 'b', 'c', 'd',
                'e', 'f', 'g', 'h', 'i',
                'j', 'k', 'l', 'm', 'n',
                'o', 'p', 'q', 'r', 's',
                't', 'u', 'v', 'w', 'x',
                'y', 'z', '-', ' ', ':',
                '.', ',', '/', '%', '#',
                '!', '@', '&', '(', ')',
                '<', '>', '"', '\'','?',
                '[', ']', '{', '}', '\\',
                '|', '+', '=', '_', '^',
                '￥','~', '`'
        );
    
        if ( strtoupper($type) == "TODBC" ) {
            return str_replace( $sbc, $dbc, $str ); //半角到全角

        } elseif ( strtoupper($type) == "TOSBC" ) {
            return str_replace( $dbc, $sbc, $str ); //全角到半角

        } else {
            return false;

        }
    }

    /**
     * 校验密码规则
     * 长度：8-32位
     * 必须为同时包含数字、字母、符号的组合字符串
     * 字母区分大小写
     * 不区分全角、半角
     * 不允许出现其他字符。
     * 符号：ASCII中的符号
     * @author chensm
     * @param string $password 待验证的密码
     * @return boolean
     */
    public static function checkPassRules($password)
    {
        $password = self::convertStrType($password, 'TOSBC');
        if (preg_match('/^(?![0-9_\W]+$)(?![a-zA-Z_\W]+$)(?![a-zA-Z0-9]+$)[\x20-\x7E]{8,32}$/', $password))
            return true;
        else
            return false;
    }
    
    /**
     * 根据密码规则随机生成
     * mt_rand()生成一个介于33到126之间的php随机数（即ASCII可见字符），然后用chr()函数转化成字符。
     * @author chensm
     * @return string 生成的密码
     */
    public static function generatePassword()
    {
        $pass = '';
        $len = 16;
        for ($i = 0; $i < $len; $i++)
        {
            $pass .= chr(mt_rand(33, 126));        //生成php随机数
        }
        
        if (!self::checkPassRules($pass))        //不符合密码规则重新生成
            $pass = self::generatePassword();

        return $pass;
    }
    
    /**
     * 清除字符串中的特殊字符、标点符号、空格等(对于特别特殊的字符还没有清除，如、等)
     * @param string $str
     * @return string
     */
    public static function clearString($str)
    {
        $step1 = self::convertStrType($str, 'TOSBC');    //转换为半角字符
        $step2 = preg_replace('/((?=[\x21-\x7e\s]+)[^A-Za-z0-9])/', '', $step1);    //清除符号
        return $step2;
    }
    
    /**
     * 调用外部URL
     * 可能需要设置httpd.conf:
     *     LoadFile c:/php/libeay32.dll
     *     LoadFile c:/php/ssleay32.dll
     * @author chensm
     * @param string $url 待调用URL
     * @return boolean
     */
    public static function execURL($url)
    {
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $url);
        curl_setopt($ch2, CURLOPT_HEADER, false);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        
        $ret=curl_exec($ch2);
//         $info = curl_getinfo($ch2);
//         var_dump($info['url']);
//         var_dump($ret);
        curl_close($ch2);
        
        return $ret;
    }

    /**
     * “除”操作，过滤除数为0的情况
     * @author chensm
     * @param string $dividend 被除数
     * @param string $divisor 除数
     * @return 
     */
    public static function division($dividend, $divisor)
    {
        return $divisor != 0 ? $dividend / $divisor : 0;
    }
    
    /**
     * 输入过滤，如，$_GET $_POST
     * @author chensm
     * @param string $input 待过滤数据
     * @param string $type 过滤类型 : month, date, digit, alpha, alnum
     * @return string
     */
    public static function inputFilter($input, $type)
    {
        switch ($type)
        {
            case 'month' :
                if (preg_match('/^\d{4}\-\d{2}$/', $input))
                    return $input;
                break;
            case 'date' :
                if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $input) || preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $input))
                    return $input;
                break;
            case 'digit' :
                if (ctype_digit($input)) {
                    if ($input == 0)
                        return TRUE;
                    else
                        return $input;
                }
                break;
            case 'alpha' :
                if (ctype_alpha($input)) {
                    if ($input == '')
                        return TRUE;
                    else
                        return $input;
                }
                break;
            case 'alnum' :
                if (ctype_alnum($input)) {
                    if ($input == 0 || $input == '')
                        return TRUE;
                    else
                        return $input;
                }
                break;
        }
        return NULL;
    }

    /**
     * 获取真实IP
     */
    public static function getRealIP() {
        $arr_ip_header = array(
                "HTTP_CDN_SRC_IP",
                "HTTP_PROXY_CLIENT_IP",
                "HTTP_WL_PROXY_CLIENT_IP",
                "HTTP_CLIENT_IP",
                "HTTP_X_FORWARDED_FOR",
                "REMOTE_ADDR",
        );
    
        $client_ip = "";
        foreach ($arr_ip_header as $key) {
            if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != "unknown") {
                $client_ip = $_SERVER[$key];
                break;
            }
        }
        if (false !== strpos($client_ip, ",")) {
            $client_ip = preg_replace("/,.*/", "", $client_ip);
        }
    
        return $client_ip;
    }
    
    /**
     * 获取指定日期的前/后指定周区间，按周一为起始
     * @param date $date                       指定日期
     * @param string $handle                   prev 前, next 后
     * @param int $last                        前/后几周
     * @param string $last_start_search        返回目标周周一的日期
     * @param string $last_end_search          返回目标周周日的日期
     * @return string                          返回目标周区间
     */
    public static function getLastWeekRange($date, $handle='prev', $last=1, &$start_search='', &$end_search='') {
        //$date = date("Y-m-d");  //当前日期
        $first=1;                               //$first =1 表示每周星期一为开始时间 0表示每周日为开始时间
        $w = date("w", strtotime($date));       //获取当前周的第几天 周日是 0 周一 到周六是 1 -6
        $d = $w ? $w - $first : 6;              //如果是周日 -6天
        
        $range_start = $last*7;
        $now_start = date("Ymd", strtotime($date.' - '.$d.' days'));    //本周开始时间
        $now_end = date("Ymd", strtotime($now_start.' + 6 days'));    //本周结束时间
        
        switch ($handle) {
            case 'self' :
                $start_search = date('Y-m-d', strtotime($now_start));
                $end_search = date('Y-m-d', strtotime($now_end));
                return $now_start."-".$now_end;
                break;
            case 'prev' :
                $last_start = date('Ymd',strtotime($now_start.' - '.$range_start.' days'));    //前n周开始时间
                $last_end = date('Ymd',strtotime($last_start.' + 6 days'));        //前n周结束时间
                $start_search = date('Y-m-d', strtotime($last_start));
                $end_search = date('Y-m-d', strtotime($last_end));
                return $last_start."-".$last_end;
                break;
            case 'next' :
                $next_start = date('Ymd',strtotime($now_start.' + '.$range_start.' days'));    //后n周开始时间
                $next_end = date('Ymd',strtotime($next_start.' + 6 days'));        //后n周结束时间
                $start_search = date('Y-m-d', strtotime($next_start));
                $end_search = date('Y-m-d', strtotime($next_end));
                return $next_start."-".$next_end;
                break;
        }
    }
    
    /**
     * 空字段值转换空字符串或其他指定值
     * @param $v        待检验字段
     * @param $defaut   默认返回值
     */
    public static function turnNull($v, $defaut='')
    {
        $array = array(
            '1970-01-01',
            '1970-01-01 00:00:00',
            '1970-01-01 08:00:00',
            '0000-00-00',
            '0000-00-00 00:00:00',
        );
        return $v===null||strtoupper($v)==='NULL'||strtoupper($v)==='NONE'||in_array(trim($v), $array)/*||$v=='0'*/ ? $defaut : $v;
    }
    
    /**
     * 周数转换为周起止日期
     * @param $week    周数 eg.201501
     */
    public static function turnWeek($week)
    {
        $thursday = Utility::getDateByWeek(substr($week, 0, 4), substr($week, 4, 2), 4);    //取参数表示周的周四的日期，用来高亮用户选择周
        return date('Ymd', strtotime('-3 day', strtotime($thursday))).'~'.date('Ymd', strtotime('+3 day', strtotime($thursday)));
    }
    
    /**
     * 对一个给定的二维数组按照指定的键值进行排序
     * @param array $arr      待排序数组
     * @param string $key     排序字段
     * @param string $type    排序方式：正序、倒序
     * @return array
     */
    public static function array_sort($arr,$key,$type='asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$key];
        }
        if(strtolower($type) == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
    
    /**
     * 获取当前时间戳，精确到微秒，用于监控程序耗时
     */
    public static function getMicroTime()
    {
        $microtime = microtime();
        $tmp = explode(' ', $microtime);
        return $tmp[0]+$tmp[1];
    }
    
    /**
     * 组装用户有权限访问的目录
     * @param int $user_id
     * @return array
     */
    public static function assemblePrivilegeMenu($user_id=null)
    {
        if (!isset($user_id))
            $user_id = Yii::app()->user->id;
        
        $privilege_menus = array();
        $privilege = Privileges::model()->findByPk($user_id);
        if ($privilege) {
            if ($privilege->is_super > 0) {
                $menus = Menu::model()->findAll('platform=9 and status=1');
                $privilege_menus = Menu::constructMenuTree($menus);
            } else {
                $user_group = isset($privilege->user_group_id)&&!empty($privilege->user_group_id)&&preg_match('/^a:/', $privilege->user_group_id) ? unserialize($privilege->user_group_id) : array();
                $report_ids = ReportPrivileges::getReportsByUsergroup($user_group);
                if (!empty($report_ids)) {
                    $criteria=new CDbCriteria;
                    $criteria->compare('status', 1);
                    $criteria->compare('platform', 9);
                    $criteria->addInCondition('report_id',$report_ids);
                    $menus = Menu::model()->findAll($criteria);
                    $privilege_menus = Menu::constructMenuTree($menus);
                }
            }
        }
        
        return $privilege_menus;
    }
    
    /**
     * 根据团购业务城市构造相关结构，包括大区、区域、战区等
     * @param array $city_ids 城市ID数组
     * @return array 各项结构数组
     */
    public static function assembleStructByCities($city_ids)
    {
        $area_ids = array();
        $areas = array();
        $area_regions = array();
        $area_name_regions = array();
        $area_cities = array();
        $area_name_cities = array();
        
        $region_ids = array();
        $regions = array();
        $region_cities = array();
        $region_name_cities = array();
        
        $warzone_ids = array();
        $warzones = array();
        $warzone_cities = array();
        $warzone_name_cities = array();
        
        $cities = array();
        
        $city_teams = array();
        $city_name_teams = array();
        
        if (!empty($city_ids)) {
            $criteria=new CDbCriteria;
            $criteria->select = 'id, name, case when area_id=0 or area_id is null or area_id=\'\' then '.Common::UNKNOWN_AREA_ID.' else area_id end as area_id,
                    case when region_id=0 or region_id is null or region_id=\'\' then '.Common::UNKNOWN_REGION_ID.' else region_id end as region_id,
                    case when war_zone=0 or war_zone is null or war_zone=\'\' then '.Common::UNKNOWN_WARZONE_ID.' else war_zone end as war_zone';
            $criteria->addInCondition('id', $city_ids);
            $criteria->compare('status', 1);
            $criteria->compare('city_type', 1);
            $models = City::model()->findAll($criteria);
            
            if (!empty($models)) {
                foreach ($models as $model) {
                    $area_ids[$model->area_id] = $model->area_id;
                    $region_ids[$model->region_id] = $model->region_id;
                    $warzone_ids[$model->war_zone] = $model->war_zone;
            
                    $area_regions[$model->area_id][$model->region_id] = $model->region_id;
            
                    //以ID为键的城市关联结果
                    $area_cities[$model->area_id][$model->id] = $model->name;
                    $region_cities[$model->region_id][$model->id] = $model->name;
                    $warzone_cities[$model->war_zone][$model->id] = $model->name;
            
                    $cities[$model->id] = $model->name;
                }
                unset($models);
                unset($criteria);
            
                //大区列表
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $area_ids);
                $criteria->compare('parent_id', '0');
                $criteria->compare('is_delete', '0');
                $criteria->compare('is_group', '1');
                $models = Region::model()->findAll($criteria);
                if (!empty($models)) {
                    foreach ($models as $model) {
                        $areas[$model->id] = $model->name;
                    }
                }
                if (in_array(Common::UNKNOWN_AREA_ID, $area_ids)) {
                    $areas[Common::UNKNOWN_AREA_ID] = '未知大区';
                }
                unset($models);
                unset($criteria);
            
                //区域列表
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $region_ids);
                $criteria->compare('parent_id', '<>0');
                $criteria->compare('is_delete', '0');
                $criteria->compare('is_group', '1');
                $models = Region::model()->findAll($criteria);
                if (!empty($models)) {
                    foreach ($models as $model) {
                        $regions[$model->id] = $model->name;
                    }
                }
                if (in_array(Common::UNKNOWN_REGION_ID, $region_ids)) {
                    $regions[Common::UNKNOWN_REGION_ID] = '未知区域';
                }
                unset($models);
                unset($criteria);
            
                //战区列表
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $warzone_ids);
                $criteria->compare('parent_id', '0');
                $criteria->compare('is_delete', '0');
                $criteria->compare('is_group', '1');
                $models = WarZone::model()->findAll($criteria);
                if (!empty($models)) {
                    foreach ($models as $model) {
                        $warzones[$model->id] = $model->name;
                    }
                }
                if (in_array(Common::UNKNOWN_WARZONE_ID, $warzone_ids)) {
                    $warzones[Common::UNKNOWN_WARZONE_ID] = '未知战区';
                }
                unset($models);
                unset($criteria);
            
                //大区-区域关联结果
                if (!empty($area_regions)) {
                    foreach ($area_regions as $area_id => $region) {
                        if (array_key_exists($area_id, $areas)) {
                            foreach ($region as $id) {
                                if (array_key_exists($id, $regions)) {
                                    $area_regions[$area_id][$id] = $regions[$id];                    //以id为键
                                    $area_name_regions[$areas[$area_id]][$id] = $regions[$id];       //以name为键
                                }
                            }
                        } else {
                            unset($area_regions[$area_id]);
                        }
                    }
                }
            
                //大区-城市关联结果
                if (!empty($area_cities)) {
                    foreach ($area_cities as $area_id => $city) {
                        if (array_key_exists($area_id, $areas)) {
                            $area_cities[$area_id] = $city;                    //以id为键
                            $area_name_cities[$areas[$area_id]] = $city;       //以name为键
                        } else {
                            unset($area_cities[$area_id]);
                        }
                    }
                }
            
                //区域-城市关联结果
                if (!empty($region_cities)) {
                    foreach ($region_cities as $region_id => $city) {
                        if (array_key_exists($region_id, $regions)) {
                            $region_cities[$region_id] = $city;                    //以id为键
                            $region_name_cities[$regions[$region_id]] = $city;       //以name为键
                        } else {
                            unset($region_cities[$region_id]);
                        }
                    }
                }
            
                //战区-城市关联结果
                if (!empty($warzone_cities)) {
                    foreach ($warzone_cities as $warzone => $city) {
                        if (array_key_exists($warzone, $warzones)) {
                            $warzone_cities[$warzone] = $city;                    //以id为键
                            $warzone_name_cities[$warzones[$warzone]] = $city;       //以name为键
                        } else {
                            unset($warzone_cities[$warzone]);
                        }
                    }
                }
            
                //城市-团队关联结果
                $criteria=new CDbCriteria;
                $criteria->with = 'team';
                $criteria->addInCondition('team.team_type', MappingTeamCity::$types_with_city_for_group);
                $criteria->addInCondition('t.id', $city_ids);
                $criteria->compare('status', 1);
                $criteria->compare('city_type', 1);
                $models = City::model()->findAll($criteria);
                
                if (!empty($models)) {
                    foreach ($models as $model) {
                        if (!empty($model->team)) {
                            foreach ($model->team as $team) {
                                $city_teams[$model->id][$team->team_id] = $team->team_name;
                                $city_name_teams[$model->name][$team->team_id] = $team->team_name;
                            }
                        }
                    }
                }
            
            }
        }
        return array($areas,$regions,$warzones,$cities,$area_regions,$area_name_regions,$area_cities,$area_name_cities,$region_cities,$region_name_cities,$warzone_cities,$warzone_name_cities,$city_teams,$city_name_teams);
    }
    
    /**
     * 根据网店通业务分部构造相关结构，包括大区、区域、战区等
     * @param array $branch_ids 网店通分部ID数组
     * @return array 各项结构数组
     */
    public static function assembleStructByWdtBranches($branch_ids)
    {
        $area_ids = array();
        $areas = array();
        $area_regions = array();
        $area_name_regions = array();
        $area_branches = array();
        $area_name_branches = array();
    
        $region_ids = array();
        $regions = array();
        $region_branches = array();
        $region_name_branches = array();
    
        $warzone_ids = array();
        $warzones = array();
        $warzone_branches = array();
        $warzone_name_branches = array();
    
        $branches = array();
        
        $branch_teams = array();
        $branch_name_teams = array();
    
        if (!empty($branch_ids)) {
            $criteria=new CDbCriteria;
            $criteria->select = 'id, name, case when area_id=0 or area_id is null or area_id=\'\' then '.Common::UNKNOWN_AREA_ID.' else area_id end as area_id,
                    case when region_id=0 or region_id is null or region_id=\'\' then '.Common::UNKNOWN_REGION_ID.' else region_id end as region_id,
                    case when war_zone=0 or war_zone is null or war_zone=\'\' then '.Common::UNKNOWN_WARZONE_ID.' else war_zone end as war_zone';
            $criteria->addInCondition('id', $branch_ids);
            $criteria->compare('is_delete', 0);
            $models = Branch::model()->findAll($criteria);
    
            if (!empty($models)) {
                foreach ($models as $model) {
                    $area_ids[$model->area_id] = $model->area_id;
                    $region_ids[$model->region_id] = $model->region_id;
                    $warzone_ids[$model->war_zone] = $model->war_zone;
    
                    $area_regions[$model->area_id][$model->region_id] = $model->region_id;
    
                    //以ID为键的分部关联结果
                    $area_branches[$model->area_id][$model->id] = $model->name;
                    $region_branches[$model->region_id][$model->id] = $model->name;
                    $warzone_branches[$model->war_zone][$model->id] = $model->name;
    
                    $branches[$model->id] = $model->name;
                }
                unset($models);
                unset($criteria);
    
                //大区列表
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $area_ids);
                $criteria->compare('parent_id', '0');
                $criteria->compare('is_delete', '0');
                //$criteria->compare('is_wdt', '1');
                $models = Region::model()->findAll($criteria);
                if (!empty($models)) {
                    foreach ($models as $model) {
                        $areas[$model->id] = $model->name;
                    }
                }
                if (in_array(Common::UNKNOWN_AREA_ID, $area_ids)) {
                    $areas[Common::UNKNOWN_AREA_ID] = '未知大区';
                }
                unset($models);
                unset($criteria);
    
                //区域列表
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $region_ids);
                $criteria->compare('parent_id', '<>0');
                $criteria->compare('is_delete', '0');
                //$criteria->compare('is_wdt', '1');
                $models = Region::model()->findAll($criteria);
                if (!empty($models)) {
                    foreach ($models as $model) {
                        $regions[$model->id] = $model->name;
                    }
                }
                if (in_array(Common::UNKNOWN_REGION_ID, $region_ids)) {
                    $regions[Common::UNKNOWN_REGION_ID] = '未知区域';
                }
                unset($models);
                unset($criteria);
    
                //战区列表
                $criteria=new CDbCriteria;
                $criteria->addInCondition('id', $warzone_ids);
                $criteria->compare('parent_id', '0');
                $criteria->compare('is_delete', '0');
                //$criteria->compare('is_wdt', '1');
                $models = WarZone::model()->findAll($criteria);
                if (!empty($models)) {
                    foreach ($models as $model) {
                        $warzones[$model->id] = $model->name;
                    }
                }
                if (in_array(Common::UNKNOWN_WARZONE_ID, $warzone_ids)) {
                    $warzones[Common::UNKNOWN_WARZONE_ID] = '未知战区';
                }
                unset($models);
                unset($criteria);
    
                //大区-区域关联结果
                if (!empty($area_regions)) {
                    foreach ($area_regions as $area_id => $region) {
                        if (array_key_exists($area_id, $areas)) {
                            foreach ($region as $id) {
                                if (array_key_exists($id, $regions)) {
                                    $area_regions[$area_id][$id] = $regions[$id];                    //以id为键
                                    $area_name_regions[$areas[$area_id]][$id] = $regions[$id];       //以name为键
                                }
                            }
                        } else {
                            unset($area_regions[$area_id]);
                        }
                    }
                }
    
                //大区-分部关联结果
                if (!empty($area_branches)) {
                    foreach ($area_branches as $area_id => $city) {
                        if (array_key_exists($area_id, $areas)) {
                            $area_branches[$area_id] = $city;                    //以id为键
                            $area_name_branches[$areas[$area_id]] = $city;       //以name为键
                        } else {
                            unset($area_branches[$area_id]);
                        }
                    }
                }
    
                //区域-分部关联结果
                if (!empty($region_branches)) {
                    foreach ($region_branches as $region_id => $city) {
                        if (array_key_exists($region_id, $regions)) {
                            $region_branches[$region_id] = $city;                    //以id为键
                            $region_name_branches[$regions[$region_id]] = $city;       //以name为键
                        } else {
                            unset($region_branches[$region_id]);
                        }
                    }
                }
    
                //战区-分部关联结果
                if (!empty($warzone_branches)) {
                    foreach ($warzone_branches as $warzone => $city) {
                        if (array_key_exists($warzone, $warzones)) {
                            $warzone_branches[$warzone] = $city;                    //以id为键
                            $warzone_name_branches[$warzones[$warzone]] = $city;       //以name为键
                        } else {
                            unset($warzone_branches[$warzone]);
                        }
                    }
                }
    
                //分部-团队关联结果
                $criteria=new CDbCriteria;
                $criteria->with = 'team';
                $criteria->compare('t.is_delete', 0);
                $criteria->addInCondition('team.team_type', MappingTeamCity::$types_with_branch_for_wdt);
                $criteria->addInCondition('t.id', $branch_ids);
                $models = Branch::model()->findAll($criteria);
            
                if (!empty($models)) {
                    foreach ($models as $model) {
                        if (!empty($model->team)) {
                            foreach ($model->team as $team) {
                                $branch_teams[$model->id][$team->team_id] = $team->team_name;
                                $branch_name_teams[$model->name][$team->team_id] = $team->team_name;
                            }
                        }
                    }
                }
            }
        }
        return array($areas,$regions,$warzones,$branches,$area_regions,$area_name_regions,$area_branches,$area_name_branches,$region_branches,$region_name_branches,$warzone_branches,$warzone_name_branches,$branch_teams,$branch_name_teams);
    }
    
    /**
     * 根据城市名称和所属分区字段名取分区值
     * @param array $city_div_relations    城市分区对应关系
     * @param string $city                 城市
     * @param string $div_column           城市分区字段名
     */
    public static function getDivisionByCity($city_div_relations,$city,$div_column)
    {
        $result = '未知';
        if (array_key_exists($city, $city_div_relations)) {
            if (preg_match('/.*_area_[a-z0-9]{16}$/', $div_column)) {
                $result = $city_div_relations[$city]['area'];
            } elseif (preg_match('/.*_region_[a-z0-9]{16}$/', $div_column)) {
                $result = $city_div_relations[$city]['region'];
            } elseif (preg_match('/.*_warzone_[a-z0-9]{16}$/', $div_column)) {
                $result = $city_div_relations[$city]['warzone'];
            } elseif (preg_match('/.*_warstep_[a-z0-9]{16}$/', $div_column)) {
                $result = $city_div_relations[$city]['warstep'];
            }
        }
        return $result;
    }
    
    /**
     * 改变数组值类型
     * @param array $array
     * @param string $type
     */
    public static function changeArrayValueType($array, $type)
    {
        if (is_array($array) && !empty($array)) {
            foreach ($array as $k => $v) {
                switch ($type) {
                    case 'string':
                        $array[$k] = (string)$v;
                        break;
                    case 'int':
                        $array[$k] = intval($v);
                        break;
                }
            }
        }
        return $array;
    }
    
}
