<?php

/* 函数列表
write_file_shell:写文件(调用shell方式)
write_file_bin:写文件(打开写方式)
read_file_data:读文件
json_encode_no_unicode:转换成json时让中文不转为unicode
is_date:判断时间格式是否正确
gen_winetoken:将wine_id生成64位md5值
gen_session_id:生成user_id的session_id
curl_http:http api接口数据获取函数
checkEmail:判断邮箱格式是否正确
get_uuid_php:生成UUID
get_uuid_php_19:获得19位UUID
my_sql_escape_str:转义数组内容
string_exists_zh:判断是否有中文
is_all_zh:判断字符串是否为全中文
expired_time:判断过期时间
array_delete_null:删除数组中的空元素和指定元素
array_delete_key_value_null:删除数组中的空元素、指定key值、指定value值
my_empty:判断是否为空
deldir:删除目录
generate_rand:获取一个随机数
union_str:字符连接
syscmd_php:执行系统命令函数
filer_str_symbol:过滤字符串:将字符串最后标点符号、回车、空格、制表符等不可见字符
                 将符号统一修改成中文标点符号或者英文标点符号
find_str_symbol:判断字符串中是否有标点符号
my_str_replace_a:字符串中的字符替换
json_str_escape:json字符串转义
download_media_file:下载静态文件
resize_image:调整图片大小
chick_params_v:判断参数是否都正确,并转换成对应的数据库字段数组
group_uniq:二维数组统计去重排序
KCAppAuth:酒咔嚓应用认证函数
KCUserAuth:酒咔嚓用户认证函数
*/

/* 写文件
* file:文件路径
* str:内容
* rewrite:重写标志(默认为0).0追加方式,1清除旧数据再写
*/
function write_file_shell($file,$str,$rewrite = 0)
{
    list($a_dec, $a_sec) = explode(" ", microtime());
    $msec = str_replace("0.","",$a_dec);
    // 取小数点后面3位
    $msec = substr($msec,0,3);

    $time = date("Y-m-d H:i:s",$a_sec) . "." . $msec;
    $val = str_replace("'", '', $str);
    if( $rewrite == 0 )
    {
        $write_cmd = "echo '$time' '$val' >> $file";
    }
    else
    {
        $write_cmd = "echo '$time' '$val' > $file";
    }

    exec($write_cmd,$output,$flag);
    return true;
}

/* 写文件
* file:文件路径
* str:内容
* rewrite:重写标志(默认为a+).a+追加方式,w清除旧数据再写
*/
function write_file_bin($file,$str,$mode='a+')
{
    $oldmask = @umask(0);
    $fp = @fopen($file,$mode);
    @flock($fp, 3);
    if(!$fp)
    {
        Return false;
    }
    else
    {
        @fwrite($fp,$str);
        @fclose($fp);
        @umask($oldmask);
        Return true;
    }
}

/* 读文件
* @file:文件路径
* 返回:文件二进制内容
*/
function read_file_data($file)
{
    $handle = fopen($file, 'rb');
    $content = '';
    while(!feof($handle)){
        $content .= fread($handle, 8080);
    }
    fclose($handle);

    return $content;
}

/* 转换成json时让中文不转为unicode
* arr:数组
*/




function json_encode_no_unicode($arr)
{
    if( !is_array($arr) )
    {
        return $arr;
    }
    
    $arr_urlencode = arr_urlencode($arr);
    return urldecode(json_encode($arr_urlencode));
}

function arr_urlencode($arr)
{
    // 要转换的数组
    $t_arr = array();

    if( is_array($arr) && (count($arr) > 0) )
    {
        foreach( $arr as $key => $value )
        {
            if( is_numeric($value) )
            {
                //需要转义的字符
                $t_key = json_str_escape($key);
                $t_value = $value;

                $t_key = urlencode($t_key);
                $t_arr[$t_key] = $t_value;
            }
            else if( is_string($value) )
            {
                //需要转义的字符
                $t_key = json_str_escape($key);
                $t_value = json_str_escape($value);

                $t_key = urlencode($t_key);
                $t_arr[$t_key] = urlencode($t_value);
            }
            else if( is_array($value) )
            {
                //需要转义的字符
                $t_key = json_str_escape($key);

                $t_key = urlencode($t_key);
                $t_arr[$t_key] = arr_urlencode($value);
            }
        }
    }
    
    return $t_arr;
}

/* 判断时间格式是否正确
* date:时间值.
       格式:UNIX时间戳:1430463600,在10到13个字符之间
            字符串时间:2015-05-17 08:00:00/20150517080000
* 返回值:成功UNIX时间戳,失败返回false
*/
function is_date($date)
{
    $date_len = strlen($date);
    //UNIX时间戳
    if( (9 < $date_len) && ($date_len < 14) )
    {
        if( !is_numeric($date) )
        {
            return false;
        }

        $time_str = date("YmdHis",$date);
        $inttime = strtotime($time_str);

        if( $date != $inttime )
        {
            return false;
        }
    }
    //字符串时间
    else if( $date_len >= 8 )
    {
        $inttime = strtotime($date);
        if( $inttime == '' )
        {
            return false;
        }
    }
    else
    {
        return false;
    }

    return $inttime;
}

/* 将wine_id生成64位md5值
* wine_id:商品ID
*/
function gen_winetoken($wine_id, $key = 'chuck')
{
    $encrypt_key = md5($key);

    // 第一次计算获取的结果
    $tmp_key_first = "";

    $ctr = 0;
    for( $i=0; $i < strlen($wine_id); $i++ )
    {
        if ( $ctr == strlen($encrypt_key) )
        {
            $ctr=0;
        }

        $tmp_key_first .= substr($encrypt_key,$ctr,1) . (substr($wine_id,$i,1) ^ substr($encrypt_key,$ctr,1));
        $ctr++;
    }

    // 第二次计算获取的结果
    $tmp_key_second = "";

    $ctr=0;
    for( $i=0; $i < strlen($tmp_key_first); $i++ )
    {
        if( $ctr == strlen($encrypt_key) )
        {
            $ctr=0;
        }
        $tmp_key_second .= substr($tmp_key_first,$i,1) ^ substr($encrypt_key,$ctr,1);
        $ctr++;
    }

    $tmp_key_second = md5(strtoupper(md5($tmp_key_second)));

    return $tmp_key_second.strtoupper(md5($tmp_key_second));
}

/* 生成user_id的session_id
*/
function gen_session_id($user_id)
{
    return md5($user_id . time());
}

//====http api接口数据获取函数====
/*
$url:表示访问的链接地址
$method:表示推送数据的方式,post方式或get方式,默认为get方式
$data:要推送的数据.格式:array/string
$headers:http头,例如:array("host: 127.0.0.1","Content-Type: image/jpeg")
返回值:推送成功,返回网页返回的数据信息.具体数据信息根据$url地址返回结果说明.
       推送失败,返回array数据.
*/
function curl_http($url, $method = 'get', $data = '', $headers = array())
{
    if(array_key_exists('HTTP_USER_AGENT',$_SERVER))
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }
    else
    {
        $user_agent = '';
    }
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    if( !empty($headers) )
    {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    if( ($method == 'post') || ($method == 'POST') )
    {
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作

    if ( ($errno = curl_errno($curl)) )
    {
        $err_str = 'Errno '. $errno;//错误号
        curl_close($curl); // 关闭CURL会话
        return array("status" => 50004,"description" => $err_str);
    }
    
    curl_close($curl); // 关闭CURL会话

    return $tmpInfo; // 返回数据
}


//判断邮箱格式是否正确,返回1为邮箱
function checkEmail($email)
{
    if (!preg_match("/^[a-z0-9]([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?$/" , $email))
    {
        return false;
    }
    
    return true;
}

//生成UUID
function get_uuid_php()
{
    $rand = "";
    $c= "0123456789abcdefghijklmnopqrstuvwxyz";
    $minindex = 0;
    $maxindex = strlen($c) - 1;

    for($i=0; $i<8; $i++)
    {
        $index = rand($minindex,$maxindex);
        $rand.= substr($c,$index,1);
    }

    $pid = getmypid();
    $microTime = microtime();
    list($a_dec, $a_sec) = explode(" ", $microTime);
    $a_dec = str_replace("0.","",$a_dec);
    return $rand . "-" . $pid . "-" . $a_dec . "-" . $a_sec;
}

//获得19位UUID
function get_uuid_php_19()
{
    $rand = "";
    $c= "0123456789abcdefghijklmnopqrstuvwxyz";
    $minindex = 0;
    $maxindex = strlen($c) - 1;

    for($i=0; $i<3; $i++)
    {
        $index = rand($minindex,$maxindex);
        $rand.= substr($c,$index,1);
    }

    $microTime = microtime();
    list($a_dec, $a_sec) = explode(" ", $microTime);
    $a_dec = str_replace("0.","",$a_dec);
    
    $sec_num = 10;
    $a_sec_count = strlen($a_sec);
    $a_sec_index = $a_sec_count - $sec_num;
    $a_sec_index = ($a_sec_index < 0) ? 0:$a_sec_index;
    
    return $rand . substr($a_sec,$a_sec_index,$sec_num) . substr($a_dec,0,6);
}

//转义数组内容
function my_sql_escape_str($arr_sql)
{
    $arr_tmp = array();
    
    if( !is_array($arr_sql) )
    {
        return $arr_sql;
    }
    
    foreach( $arr_sql as $key => $value )
    {
        if( is_string($value) && ($value !== '') )
        {
            $arr_tmp[$key] = mysql_escape_string($value);
        }
        else
        {
            $arr_tmp[$key] = $value;
        }
    }
    
    return $arr_tmp;
}

//判断是否有中文
function string_exists_zh($str)
{
    if( $str == "" )
    {
        return false;
    }

    $pattern = '/[^\x00-\x80]/';

    if(preg_match($pattern,$str))
    {
        return true;
    }
    else
    {
        return false;
    }
}

/* 判断过期时间
 * intime:输入的时间值,格式为20150113141800(年月日时分秒),
 *        或1419240960(1970-01-01 00:00:00到现在的秒数,即UNIX时间戳)。
 * exptime:过期的秒数
 * @return:array("status" = > 状态,"discription" => "描述","time_sec" => "UNIX时间戳");
 *         status:0表示成功,其他为失败。
*/
function expired_time($intime,$exptime)
{
    $arr_ret = array("status" => 10001,"description" => "","time_sec" => "");
    
    $arr_ret['time_sec'] = is_date($intime);
    //判断时间格式错误
    if( $arr_ret['time_sec'] === false )
    {
        $arr_ret['description'] = "时间格式错误";
        return $arr_ret;
    }

    //判断过期时间参数是否为数字
    if( !is_numeric($exptime) )
    {
        $arr_ret['description'] = "过期时间格式错误";
        return $arr_ret;
    }

    $curtime = time();
    $distime = $curtime - $arr_ret['time_sec'];
    //时间过期
    if( ($distime < -$exptime) || ($distime > $exptime)  )
    {
        $arr_ret['description'] = "时间已过期";
        return $arr_ret;
    }

    //时间正确
    $arr_ret['status'] = 0;
    $arr_ret['description'] = "ok";
    return $arr_ret;
}

//删除数组中的空元素
//keep_key:key值设置。0表示按照顺key序排,1表示按照原key排
//arr_keys:数组,要删除的key值。
function array_delete_null($arr,$arr_keys = array(),$keep_key = 1)
{
    if( !is_array($arr) || !is_array($arr_keys) )
    {
        return array();
    }

    $arr_tmp = array();
    
    foreach( $arr as $key => $value )
    {
        if( array_key_exists($key,$arr_keys) )
        {
            continue;
        }
        
        //判断不为空
        if( !my_empty($value)  )
        {
            if( $keep_key == 0 )
            {
                array_push($arr_tmp,$value);
            }
            //保持原有key值
            else
            {
                $arr_tmp[$key] = $value;
            }
        }
    }
    
    return $arr_tmp;
}

//删除数组中的空元素、指定key值、指定value值
//keep_key:key值设置。0表示按照顺key序排,1表示按照原key排
//case_flag:arr中的value和arr_value不分大小写比较.
//arr_keys:数组,要删除的key值。
//arr_value:数组,要删除的value值。
function array_delete_key_value_null($arr,
            $keep_key = 1,
            $case_flag = 0,
            $arr_keys = array(),
            $arr_value = array())
{
    if( !is_array($arr) || 
        !is_array($arr_keys) || 
        !is_array($arr_value)
    )
    {
        return array();
    }

    $arr_case_key = $arr_value;
    //将arr_value的key都转换成小写
    if( $case_flag == 1 )
    {
        $arr_case_key = array();
        foreach( $arr_value as $key_v => $value_v )
        {
            $lower_key = strtolower($key_v);
            $arr_case_key[$lower_key] = $value_v;
        }
    }

    $arr_tmp = array();
    
    foreach( $arr as $key => $value )
    {
        if( array_key_exists($key,$arr_keys) )
        {
            continue;
        }
        
        $tmp_value = $value;
        //将value转成小写
        if( $case_flag == 1 )
        {
            $tmp_value = strtolower($value);
        }
        if( array_key_exists($tmp_value,$arr_case_key) )
        {
            continue;
        }
        
        //判断不为空
        if( !my_empty($value)  )
        {
            if( $keep_key == 0 )
            {
                array_push($arr_tmp,$value);
            }
            //保持原有key值
            else
            {
                $arr_tmp[$key] = $value;
            }
        }
    }
    
    return $arr_tmp;
}

function my_empty($value)
{
    if( is_string($value) && ($value == "") )
    {
        return true;
    }
    else if( is_array($value) && (count($value) < 1) )
    {
        return true;
    }
    else if( is_bool($value) )
    {
        return $value;
    }
    else if( empty($value) )
    {
        true;
    }

    return false;
}

//删除目录
function deldir($dir)
{
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh))
    {
        if($file!="." && $file!="..")
        {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath))
            {
                unlink($fullpath);
            }
            else
            {
                deldir($fullpath);
            }
        }
    }
 
    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir))
    {
        return true;
    }
    else
    {
        return false;
    }
}

//l:表示返回随机字符串的个数
function generate_rand($l)
{
    $rand = "";
    $c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $minindex = 0;
    $maxindex = strlen($c) - 1;
    
    for($i=0; $i<$l; $i++)
    {
        $index = rand($minindex,$maxindex);
        $rand.= substr($c,$index,1);
    }
    
    return $rand; 
}

/*字符连接函数,如果两个字符串一样，
输入一个字符串,如果不一样，输出连接
后的字符串
first:要连接的第一个字符
second:要链接的第二个字符
mid:用户链接的中间字符
*/
function union_str($first,$second,$mid)
{
    $ret_str = "";
    
    if( !empty($first) )
    {
        $ret_str = $first;
        if( !empty($second) && ($second != $first) )
        {
            $ret_str .= $mid;
            $ret_str .= $second;
        }
    }
    else if( !empty($second) )
    {
        $ret_str = $second;
    }
    
    return $ret_str;
}

/* 执行系统命令函数
cmd_str:命令串
*/
function syscmd_php($cmd_str)
{
    //初始结果设置空
    $arr_out = array();
    //默认执行状态错误
    $cmd_status = -1;
    //屏蔽错误日志
    $cmd_str .= " 2>/dev/null";
    @exec($cmd_str,$arr_out,$cmd_status);

    //执行命令错误
    if( $cmd_status != 0 )
    {
        return false;
    }
    
    if( !is_array($arr_out) )
    {
        return array();
    }

    return $arr_out;
}

/* 过滤字符串:将字符串最后标点符号、回车、空格、制表符等不可见字符
 *              将符号统一修改成中文标点符号或者英文标点符号
 * zh_symbol_flag:将字符串中的标点符号改成中文标点,默认中文标点
*/
function filer_str_symbol($str_d, $zh_symbol_flag = 1)
{
    //中文标点符号
    $arr_zh_synbol = array("。","，","；","！","？","《","》","“","”","（","）","：","、","‘","’");
    //英文标点符号
    $arr_en_synbol = array(".",",",";","!","?","<",">","\"","\"","(",")",":","`","'","'");
    //首先将标点符号全部变成英文标点符号
    $t_str_d = str_replace($arr_zh_synbol,$arr_en_synbol,$str_d);
    //获取字符串长度
    $str_d_len = mb_strlen($t_str_d,"utf-8");
    if( $str_d_len > 4096 )
    {
        return "";
    }

    $str_end_index = -1;
    $str_str = "";
    while( ($str_d_len + $str_end_index) >= 0 )
    {
        $str_d_last = mb_substr($t_str_d,$str_end_index,1,"utf-8");
        
        //是中文和英文,去掉后面字符
        if( !eregi("[^\x80-\xff]",$str_d_last) || 
            preg_match("/^[a-zA-Z]+$/",$str_d_last) || 
            is_numeric($str_d_last)
        )
        {
            $sub_str_len = $str_end_index + 1;
            if( $sub_str_len == 0 )
            {
                $sub_str_len = strlen($t_str_d);
            }
            $sub_str = mb_substr($t_str_d,0,$sub_str_len,'utf-8');
            if( $zh_symbol_flag == 1 )
            {
                $sub_str = str_replace($arr_en_synbol,$arr_zh_synbol,$sub_str);
            }
    
            return $sub_str;
        }
        else
        {
            $str_end_index--;
        }
    }
    
    return "";
}

//判断字符串是否为全中文
function is_all_zh($str)
{
    if( !eregi("[^\x80-\xff]","$str") )
    { 
        return true;
    }
    
    return false;
}

/* 判断字符串中是否有标点符号,没有返回空串,有返回标点符号
 * 查找的标点符号:,;、。
 */
function find_str_symbol($str)
{
    $data = array();

    if( preg_match('/,|;|、|。/i', $str, $data) )
    {
        return $data[0];
    }

    return "";
}

/* 字符串中的字符替换
 * input_str:原字符串
 * arr_replace:替换的数组(array(array(),array()))
 *   数组中的参数说明:
 *      arr_find_str:查找的字符串(array("a","b"))
 *      replace_str:替换的字符串
 *   arr_bef_beh_str:查找字符串的前后字符串限制数组(array(array(),array()))
 *     数组参数说明:
 *        bef_str:find_str前面的字符串等于(不等于)该参数的字符('0'表示不参与比较)
 *        beh_str:find_str后面的字符等于(不等于)该参数的字符('0'表示不参与比较)
 *     bef_str_equal_flag:前面字符串判断标志.0表示不等于,1表示等于.
 *     beh_str_equal_flag:后面字符串判断标志.0表示不等于,1表示等于.
 *      replace_count:替换次数
 * once_flag:一次替换标志.0表示替换全部满足条件的字符串,1替换成功一次直接返回
 * 替换数组的例子:查找字符串:干红、干红葡萄酒;字符串前面不为"半"和后面不为"葡"的字符串;
                 替换成:红葡萄酒。
 $arr_replace = 
    array(
        array("arr_find_str" => array("干红","干红葡萄酒"),
            "replace_str" => "红葡萄酒",
            "arr_bef_beh_str" => array(
                                    array("bef_str" => "半",
                                          "beh_str" => "葡",
                                          "bef_str_equal_flag" => 0,
                                          "beh_str_equal_flag" => 0)
                                    ),
            "replace_count" => 10),
    );
*/
function my_str_replace_a($input_str,$arr_replace,$once_flag)
{    
    //赋值给临时字符串
    $str = $input_str;
    //赋值给返回字符串
    $new_str = $input_str;
    
    if( !is_array($arr_replace) )
    {
        return $new_str;
    }

    foreach( $arr_replace as $key => $value )
    {
        $arr_find_str = $value['arr_find_str'];
        $replace_str = $value['replace_str'];
        $arr_bef_beh_str = $value['arr_bef_beh_str'];
        $replace_count = $value['replace_count'];

        if( !is_array($arr_find_str) || empty($arr_find_str) )
        {
            continue;
        }
        
        foreach( $arr_find_str as $key_f => $value_f )
        {
            //需要查找的字符长度
            $str_len = mb_strlen($str,"utf-8");
            
            //要查找的字符串
            $find_str = $value_f;

            //需要查找的字符串长度
            $find_str_len = mb_strlen($find_str,"utf-8");
            //需要替换的字符串长度
            $replace_str_len = mb_strlen($replace_str,"utf-8");
            //替换后的字符长度差值
            $str_len_dis = $replace_str_len - $find_str_len;
            
            //echo "bef_str_len:" . $bef_str_len . ";beh_str_len:" . $beh_str_len . "\n";
            
            //开始查找的位置
            $str_index = 0;
            //查找的结束位置
            $str_end_index = $str_len;

            while( $str_index < $str_end_index )
            {
                /////////从查询位置查找字符串///////
                $str_index = mb_strpos($str,$find_str,$str_index,'utf-8');
                if( $str_index === false )
                {
                    break;
                }
                //////////////////////////////////

                if( $str_index > 0 )
                {
                    $befor_str = mb_substr($str,0,$str_index,'utf-8');
                }
                else
                {
                    $befor_str = "";
                }
    
                //后面字符串的起始位置
                $behind_str_index = $str_index + $find_str_len;
                //后面字符串的长度
                $sub_str_len = $str_len - $behind_str_index;

                //用于比较的字符串
                $str_find = mb_substr($str,$str_index,$find_str_len,'utf-8');
                //比较字符串后面的字符串
                $str_end = mb_substr($str,$behind_str_index,$sub_str_len,'utf-8');
                
                if( !is_array($arr_bef_beh_str) || empty($arr_bef_beh_str) )
                {
                    $arr_bef_beh_str = 
                        array(
                            array("bef_str" => "0",
                                "beh_str" => "0",
                                "bef_str_equal_flag" => 0,
                                "beh_str_equal_flag" => 0
                                )
                        );
                }

                foreach( $arr_bef_beh_str as $key_bf => $value_bf )
                {
                    //查找字符串前面的比较字符串
                    $bef_str = $value_bf['bef_str'];
                    //查找字符串后面的比较字符串
                    $beh_str = $value_bf['beh_str'];
                    $bef_str_equal_flag = $value_bf['bef_str_equal_flag'];
                    $beh_str_equal_flag = $value_bf['beh_str_equal_flag'];
                    
                    //不等于前面字符串的长度
                    $bef_str_len = mb_strlen($bef_str,'utf-8');
                    if( $bef_str_len == 0 )
                    {
                        $bef_str_len = 1;
                    }
                    //不等于后面字符串的长度
                    $beh_str_len = mb_strlen($beh_str,'utf-8');
                    //找到字符串前面的一个字符串
                    $str_find_befor_str = mb_substr($befor_str,-1,$bef_str_len,'utf-8');
                    //找到字符串后面的一个字符
                    $str_find_behind_str = mb_substr($str_end,0,$beh_str_len,'utf-8');

                    if( ($str_find == $find_str) && 
                        ( ($bef_str == '0') || 
                          (($bef_str_equal_flag == 0) && ($str_find_befor_str != $bef_str)) || 
                          (($bef_str_equal_flag == 1) && ($str_find_befor_str == $bef_str)) ) && 
                        ( ($beh_str == '0') || 
                          (($beh_str_equal_flag == 0) && ($str_find_behind_str != $beh_str)) || 
                          (($beh_str_equal_flag == 1) && ($str_find_behind_str == $beh_str)) ) 
                    )
                    {
                        //echo "str_find:" . $str_find . ";find_str" . $find_str . "\n";
                        //echo "str_find_befor_str:" . $str_find_befor_str . ";bef_str:" . $bef_str . "\n";
                        //echo "str_find_behind_str:" . $str_find_behind_str . ";beh_str:" . $beh_str . "\n";
                    
                        $new_str = $befor_str . $replace_str . $str_end;
                        $str = $new_str;
                    
                        //字符串长度(重新设置)
                        $str_len += $str_len_dis;
                        //开始查找的位置(重新设置)
                        $str_index += $replace_str_len;
                        $str_end_index = $str_len;

                        //替换次数减一
                        $replace_count--;
                    
                        break;
                    }
                    else
                    {
                        //查找开始位置移动
                        $str_index++;
                    }
                }

                //替换次数判断
                if( $replace_count <= 0 )
                {
                    break;
                }
            }//while

            //替换成功且只替换一次
            if( $new_str != $str )
            {
                $str = $new_str;
            }
        
            if( $once_flag )
            {
                return $new_str;
            }
            
        }
    }
    
    return $new_str;
}

//json字符串转义
function json_str_escape($str)
{
    if( $str == "" )
    {
        return $str;
    }
    
    $t_str = $str;

    //需要转义的字符
    $t_str = str_replace("\\", "\\\\", $t_str);
    $t_str = str_replace("\"", "\\\"", $t_str);
    $t_str = str_replace("\t", "    ", $t_str);
    $t_str = str_replace("\r", "", $t_str);
    $t_str = str_replace("\n", "\\n", $t_str);

    return $t_str;
}

/* 下载文件 
 * url:文件路径
 * filepath:要保存的文件路径
*/
function download_media_file($url,$filepath)
{
    $imgfile_url = $url;
    
    if( $imgfile_url == "" )
    {
        return array("status" => 10003,"description" => "file is not exists");
    }

    $ret_data = curl_http($imgfile_url,"get","");
    if( $ret_data == -1 )
    {
        return array("status" => 10001,"description" => "curl http is err");
    }
    
    // curl错误
    if( is_array($ret_data) )
    {
        return $ret_data;
    }

    if( $ret_data == "" )
    {
        return array("status" => 10003,"description" => "file is empty");
    }

    $res_img = @imagecreatefromstring($ret_data);
    if( $res_img === false )
    {
        $ret_subs = strstr($ret_data,"404 Not Found");
        if( $ret_subs != "" )
        {
            return array("status" => 10003,"description" => "file is not exists");
        }
        else
        {
            return array("status" => 10004,"description" => "file is not image");
        }
    }

    $ret_flag = write_file_bin($filepath,$ret_data,"w");
    if( $ret_flag == false)
    {
        return array("status" => 10002,"description" => "save file is err");
    }

    return array("status" => 0,"description" => "ok");
}

/* 判断参数是否都正确,并转换成对应的数据库字段数组
 * @json_arr_params:用户输入参数(json格式或者array)
 * @all_params:所有需要的参数字段和值的类型,
             如果是字符串,'0':表示不能为空,'':表示可以为空
             格式：array('app_id' => '0',
                     'rtoken' => '0',
                    'time' => '0',
                    'openid' => '0');
 * @must_params:必填参数字段
             格式:array('app_id',
                     'rtoken',
                    'time');
 * @return:array("status" => 0,"description" => "描述","data" => array());
 *         status:结果状态,0表示正确，其他表示错误码.
 *         description:错误描述.
 *         data:返回正确时的数组结构.
*/
function chick_params_v($json_arr_params,$all_params,$must_params)
{
    $arr_ret = array("status" => 0,"description" => "");
    $arr_data = array();

    if( is_array($json_arr_params) )
    {
        $arr_params = $json_arr_params;
    }
    else
    {
        // 将json转换成array
        $arr_params = json_decode($json_arr_params,true);
    }

    //传入的参数不是json
    if( !is_array($arr_params) )
    {
        $arr_ret['status'] = 10002;
        $arr_ret['description'] = "jparams:不是JSON结构";
        return $arr_ret;
    }

    //判断必填参数
    foreach( $must_params as $value )
    {
        //判断参数是否存在
        if( array_key_exists($value,$arr_params) == false )
        {
            $arr_ret['status'] = 10003;
            $arr_ret['description'] = "{$value}:参数不存在";
            return $arr_ret;
        }
    }

    //赋值判断
    foreach( $all_params as $key => $value )
    {
        // 判断参数是否存在
        if( array_key_exists($key,$arr_params) === false )
        {
            continue;
        }

        // 判断参数类型是否正确
        if( is_int($all_params[$key]) )
        {
            if( !is_int($arr_params[$key]) )
            {
                $arr_ret['status'] = 10005;
                $arr_ret['description'] = "{$key}:参数类型错误";
                return $arr_ret;
            }
        }
        else if( is_string($all_params[$key]) )
        {
            if( !is_string($arr_params[$key]) )
            {
                $arr_ret['status'] = 10005;
                $arr_ret['description'] = "{$key}:参数类型错误";
                return $arr_ret;
            }
            
            if( ($all_params[$key] != "") && ($arr_params[$key] == "") )
            {
                $arr_ret['status'] = 10004;
                $arr_ret['description'] = "{$key}:不能为空";
                return $arr_ret;
            }
        }
        else if( is_array($all_params[$key]) )
        {
            if( !is_array($arr_params[$key]) )
            {
                $arr_ret['status'] = 10005;
                $arr_ret['description'] = "{$key}:参数类型错误";
                return $arr_ret;
            }
        }

        //如果存在,赋值
        $arr_data[$key] = $arr_params[$key];
    }

    $arr_ret['data'] = $arr_data;
    return $arr_ret;
}

/* 调整图片大小
 * img:图片路径
 * wid:调整后的图片宽度
 * hei:调整后的图片高度
 * dstpath:调整后的图片保存路径
 */
function resize_image($img, $wid, $hei,$c,$dstpath)
{
    //临时创建的图象
    $im = "";
    //原始图片路径
    $srcimg = $img;
    //目标图片路径
    $dstimg = $dstpath;
    //原始宽高
    $width = 0;
    $height = 0;
    //改变后的宽高
    $resize_width = $wid;
    $resize_height = $hei;
    //1表示切图放缩
    $cut = $c;
    
    //限制改变后的宽高为1-10000之间
    if( ($wid < 1) || ($wid > 10000) )
    {
        return false;
    }
    
    if( ($hei < 1) || ($hei > 10000) )
    {
        return false;
    }
    
    //图片的类型
    $ret_imginfo = getimagesize($srcimg);
    if( !is_array($ret_imginfo) )
    {
        return false;
    }
    $cache_size = (int)(($ret_imginfo[0] * $ret_imginfo[1] * ($ret_imginfo['bits'])) / 1000000);
    if( $cache_size > 1 )
    {
        if( $cache_size < 128 )
        {
            $cache_size = 128;
        }
        ini_set("memory_limit", $cache_size . "M");
    }
    //根据原图片类型创建图像的存储空间
    if( $ret_imginfo['mime'] == "image/jpeg" )
    {
        $im = imagecreatefromjpeg($srcimg);
    }
    else if( $ret_imginfo['mime'] == "image/gif" )
    {
        $im = imagecreatefromgif($srcimg);
    }
    else if( $ret_imginfo['mime'] == "image/png" )
    {
        $im = imagecreatefrompng($srcimg);
    }
    else
    {
        return false;
    }
    
    //获取图片的宽度和高度
    $width = imagesx($im);
    $height = imagesy($im);
    
    //改变后的图象的比例
    $resize_ratio = ($resize_width)/($resize_height);
    //实际图象的比例
    $ratio = ($width)/($height);
    
    //实现放缩
    if(($cut)=="1")
    //裁图
    {
        if( $ratio >= $resize_ratio )
        //高度优先
        {
            $newimg = imagecreatetruecolor($resize_width,$resize_height);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, $resize_width,$resize_height, (($height)*$resize_ratio), $height);
            ImageJpeg ($newimg,$dstimg);
        }
        if( $ratio < $resize_ratio )
        //宽度优先
        {
            $newimg = imagecreatetruecolor($resize_width,$resize_height);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, $resize_width, $resize_height, $width, (($width)/$resize_ratio));
            ImageJpeg ($newimg,$dstimg);
        }
    }
    else
    //不裁图
    {
        if( $ratio >= $resize_ratio )
        {
            $newimg = imagecreatetruecolor($resize_width,($resize_width)/$ratio);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, $resize_width, ($resize_width)/$ratio, $width, $height);
            ImageJpeg ($newimg,$dstimg);
        }
        if( $ratio < $resize_ratio )
        {
            $newimg = imagecreatetruecolor(($resize_height)*$ratio,$resize_height);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, ($resize_height)*$ratio, $resize_height, $width, $height);
            ImageJpeg ($newimg,$dstimg);
        }
    }
    
    //销毁临时空间
    ImageDestroy($im);
    
    return true;
}

/* 二维数组统计去重排序
 * arr_tmp:传入的二维数组
 * key_uniq:去重的key值
 * count_key:去重后将相同key_uniq值对应数组中的count_key的值相加
 * sort_type:排序,0不排序,1升序,2降序
 */
function group_uniq($arr_tmp, $key_uniq, $count_key,$sort_type = 0)
{
    $arr_all = array();
    $tmp_s = array();
    define("_GROUP_UNIQ_COUNT_KEY_",$count_key);

    foreach( $arr_tmp as $key => $value )
    {
        $wt_str = $value[$key_uniq];

        if( $wt_str == '' )
        {
            continue;
        }
    
        if( !array_key_exists($wt_str, $tmp_s) )
        {
            $tmp_s[$wt_str] = array();
            $tmp_s[$wt_str][_GROUP_UNIQ_COUNT_KEY_] = 0;
        }

        foreach( $value as $key_v => $value_v )
        {
            if( $key_v == _GROUP_UNIQ_COUNT_KEY_ )
            {
                $tmp_s[$wt_str][_GROUP_UNIQ_COUNT_KEY_] += $value_v;
            }
            else
            {
                if( !array_key_exists($key_v, $tmp_s[$wt_str]) )
                {
                    $tmp_s[$wt_str][$key_v] = $value_v;
                }
                else if($tmp_s[$wt_str][$key_v] == '')
                {
                    $tmp_s[$wt_str][$key_v] = $value_v;
                }
            }
        }
    }

    foreach( $tmp_s as $key => $value )
    {
        array_push($arr_all, $value);
    }

    // 升序排列
    if( $sort_type == 1 )
    {
        usort($arr_all, function($a, $b)
        {
            $al = $a[_GROUP_UNIQ_COUNT_KEY_];
            $bl = $b[_GROUP_UNIQ_COUNT_KEY_];

            if ($al == $bl)
            {
                return 0;
            }
            return ($al < $bl) ? -1 : 1;
        }
        );
    }
    // 降序排列
    else if( $sort_type == 2 )
    {
        usort($arr_all, function($a, $b)
        {
            $al = $a[_GROUP_UNIQ_COUNT_KEY_];
            $bl = $b[_GROUP_UNIQ_COUNT_KEY_];

            if ($al == $bl)
            {
                return 0;
            }
            return ($al > $bl) ? -1 : 1;
        }
        );
    }

    return $arr_all;
}

/* 酒咔嚓应用认证函数
 * app_id:应用ID
 * rtoken:生成的凭证
 * time:生成凭证时的时间戳(20160101080808)
 * app_auth_api:应用认证接口
 * uid:酒咔嚓用户ID(认证成功返回酒咔嚓用户基本信息)
 * aid:应用ID(被访问的应用ID)
 * api_file:应用接口文件名(被访问的应用接口文件名称)
 */
function KCAppAuth($app_id,$rtoken,$time,$app_auth_api,$uid = '',$aid = '',$api_file = '')
{
    $app_auth_json = array('app_id' => $app_id,
                            'rtoken' => $rtoken,
                            'time' => $time,
                            'uid' => $uid);

    if( ($aid != '') && ($api_file != '') )
    {
        $app_auth_json = array('app_id' => $app_id,
                            'rtoken' => $rtoken,
                            'time' => $time,
                            'uid' => $uid,
                            'aid' => $aid,
                            'api_file' => $api_file);
    }

    $params = array('jparams' => json_encode($app_auth_json));

    $ret_info = curl_http($app_auth_api, "post", $params);
    // 直接返回array,说明调用时错误
    if( is_array($ret_info) )
    {
        return array("status" => 50004,"description" => "服务器错误" . __LINE__);
    }

    $arr_info = json_decode($ret_info,true);
    if( !is_array($arr_info) )
    {
        return array("status" => 50004,"description" => "服务器错误" . __LINE__);
    }

    return $arr_info;
}

/* 酒咔嚓用户认证函数
 * user_id:用户ID
 * session_id:登录后获得的凭证ID
 * user_auth_api:用户认证接口
 */
function KCUserAuth($user_id, $session_id, $user_auth_api)
{
    $app_auth_json = array('user_id' => $user_id,'session_id' => $session_id);
    $params = array('jparams' => json_encode($app_auth_json));

    $ret_info = curl_http($user_auth_api, "post", $params);
    // 直接返回array,说明调用时错误
    if( is_array($ret_info) )
    {
        return array("status" => 50004,"description" => "服务器错误" . __LINE__);
    }

    $arr_info = json_decode($ret_info,true);
    if( !is_array($arr_info) )
    {
        return array("status" => 50004,"description" => "服务器错误" . __LINE__);
    }

    return $arr_info;
}

?>
