<?php
//跨域访问的时候才会存在此字段
/*$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if(strstr($origin,'9kacha.com'))
{
    header('Access-Control-Allow-Origin:'.$origin);
    header('Access-Control-Allow-Methods:POST');
    header('Access-Control-Allow-Headers:x-requested-with,content-type');
}*/
header('Access-Control-Allow-Origin:*'); // 允许跨域
$dirpath = dirname(__FILE__);
$updirpath = dirname($dirpath);
define("CURR_DIRPATH", $dirpath);
define("CURR_UPDIRPATH", $updirpath);

require_once(CURR_UPDIRPATH . '/conf/KCResConfig.php');
require_once(CURR_UPDIRPATH . '/commlib/KCCommonFunc.php');
require_once(CURR_UPDIRPATH . '/commlib/KCMysqlDBClass.php');

// 判断POST参数jprams是否存在
if( !array_key_exists('jparams', $_POST) )
{
    echo json_encode_no_unicode(
            array("status" => 10001,
            "description" => "请POST jparams参数")
    );
    return;
}

// 获取参数值
$jparams = $_POST['jparams'];

// 将json格式转换成数组
$arr_json = json_decode($_POST['jparams'],true);

// 不能转成数组,表示接收到的数据不是json格式
if( !is_array( $arr_json ) )
{
    echo json_encode_no_unicode(
        array("status" => 10001, "description" => 'jprams参数值不是json格式')
        );
    return;
}

$curtime_str = date("YmdHis");
$rtoken = md5(KCG_APPSECRET . $curtime_str);
$authparams = array("app_id" => KCG_APPID,
                    "rtoken" => $rtoken,
                    "time" => $curtime_str
                    );
$authmode = 'app';

// 将认证信息加入到请求参数字段中
$arr_json['authparams'] = $authparams;
$arr_json['authmode'] = $authmode;

// 转换成json格式
$params['jparams'] = json_encode($arr_json);

// 发送请求
$ret_info = curl_http(RECOMM_GET_FOOD, "post", $params);

// 返回array，表示发送请求错误
if( is_array($ret_info) )
{
    return array("status" => 50004,
                "description" => "服务器错误" . __LINE__ . "(" . $ret_info['description'] . ")"
                );
}

// 将接收到的json转换成数组
$arr_info = json_decode($ret_info,true);

// 不是数组,表示接收到的数据不是json
if( !is_array($arr_info) )
{
    return array("status" => 50005,
                "description" => "服务器错误,返回结果不是json"
                );
}

// 直接返回接收到的json数据
echo $ret_info;

return;

?>