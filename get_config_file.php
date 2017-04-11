<?php
header("Access-Control-Allow-Origin: *");

// 资源配置文件夹目录(根据项目需求自己修改,必须确保目录能被WEB服务器读写)
define("_CONFIGDIR_", dirname(__FILE__) . "/conf/");
// 资源配置文件名
define("_RESCONFIG_", "KCResConfig.php");

// 函数库文件夹目录(根据项目需求自己修改,必须确保目录能被WEB服务器读写)
define("_COMMLIB_", dirname(__FILE__) . "/commlib/");
// 通用函数文件名
define("_COMMONFUNC_", "KCCommonFunc.php");
// PDO数据库类文件名
define("_MYSQLDBCLASS_", "KCMysqlDBClass.php");

if( !array_key_exists('jparams', $_POST) )
{
	if( $argc == 1 )
	{
		echo "php get_config_file.php [all|config|common|mysqldb] appid token config_api\n";
		return;
	}

	if( $argc == 5 )
	{
		// 配置类型
		$argv_type = $argv[1];
		// 应用ID
		$argv_appid = $argv[2];
		// 应用临时令牌
		$argv_token = $argv[3];
		// 配置服务器接口
		$argv_api = $argv[4];

		$_POST = array();
		$_POST['jparams'] = sprintf('{"getType":"%s","app_id":"%s","token":"%s","config_api":"%s"}',
								$argv_type,$argv_appid,$argv_token,$argv_api);
	}
	else
	{
	    echo json_encode_NoUnicode(
				array("status" => 10001,"description" => "请POST jparams参数")
				);
		return;
	}
}

// 接收到的json数据
$jparams = $_POST['jparams'];

//判断json中的参数是否完整,返回array
//////////////////////////////////////////
// 需要的全部参数
$all_params = array('getType' => '0',
					'app_id' => '0',
					'token' => '0',
					'sub_app_id' => '',
					'config_api' => '0');
// 必填参数
$must_params = array("getType","app_id","token","config_api");

$arr_params_v = checkJparams($jparams, $all_params, $must_params);
//////////////////////////////////////////

//参数判断返回结果是否正确
if( $arr_params_v['status'] != 0 )
{
	echo json_encode_NoUnicode($arr_params_v);
	return;
}

$arr_params_v = $arr_params_v['data'];

//////////////////参数判断完成,下面进行数据库操作//////////////////////
//获取当前系统时间
$curtime = date("Y-m-d H:i:s",time());

//创建一个商品数据库函数操作对象
$db_func = "";//new DBFunc(DB_HOST, DB_USER, DB_PWD, DB_NAME);

$arr_retudata = main_func($db_func, $arr_params_v);

//返回数据给用户
echo json_encode_NoUnicode($arr_retudata);
return;

function main_func($db_func, $arr_mysql)
{
	$app_auth_json = array();
    $app_auth_json['app_id'] = $arr_mysql['app_id'];
    $app_auth_json['token'] = $arr_mysql['token'];
    $app_auth_json['getType'] = $arr_mysql['getType'];
    if( isset($arr_mysql['sub_app_id']) 
        && $arr_mysql['sub_app_id'] != '' )
    {
        $app_auth_json['sub_app_id'] = $arr_mysql['sub_app_id'];
    }

	$params = array('jparams' => json_encode($app_auth_json));

	// 发送获取配置信息的请求
	$ret_info = CurlHttp($arr_mysql['config_api'], "post", $params);
	//表示执行curl_http错误
	if( is_array($ret_info) )
	{
		return $ret_info;
	}

	$arr_info = json_decode($ret_info,true);
	if( !is_array($arr_info) )
	{
		return array("status" => 50004,"description" => "返回的不是json格式");
	}

	// 获取配置信息错误
	if( $arr_info['status'] != 0 )
	{
		return $arr_info;
	}

	// 创建配置文件
	if( array_key_exists("configFile", $arr_info) )
	{
		if( !createDir(_CONFIGDIR_) )
		{
			return array("status" => 17001,"description" => "创建目录失败");
		}

		// 创建文件位置
		$ConfigFile_fpath = _CONFIGDIR_ . _RESCONFIG_;
		WriteFile($ConfigFile_fpath, $arr_info['configFile'], 'w');
		chmod($ConfigFile_fpath, 0755);
	}

	// 创建通用函数文件
	if( array_key_exists("commonFile", $arr_info) )
	{
		if( !createDir(_COMMLIB_) )
		{
			return array("status" => 17001,"description" => "创建目录失败");
		}

		// 创建文件位置
		$CommonFile_fpath = _COMMLIB_ . _COMMONFUNC_;
		WriteFile($CommonFile_fpath, $arr_info['commonFile'], 'w');
		chmod($CommonFile_fpath, 0755);
	}

	// 创建配置文件
	if( array_key_exists("mysqldbFile", $arr_info) )
	{
		if( !createDir(_COMMLIB_) )
		{
			return array("status" => 17001,"description" => "创建目录失败");
		}

		// 创建文件位置
		$MysqldbFile_fpath = _COMMLIB_ . _MYSQLDBCLASS_;
		WriteFile($MysqldbFile_fpath, $arr_info['mysqldbFile'], 'w');
		chmod($MysqldbFile_fpath, 0755);
	}

	// 返回结果
	return array("status" => 0, "description" => "ok");

}

function createDir($dirpath)
{
	// 判断文件夹是否存在,不存在则创建
	if( !is_dir($dirpath) && !mkdir($dirpath) )
	{
		return false;
	}

	return true;
}

/* 判断参数是否都正确,并转换成对应的数据库字段数组
 * @json_params:用户输入参数(json格式)
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
function checkJparams($json_params,$all_params,$must_params)
{
	$arr_ret = array("status" => 0,"description" => "");
	$arr_data = array();

	// 将json转换成array
	$arr_params = json_decode($json_params,true);

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

/* 转换成json时让中文不转为unicode
* arr:数组
*/
function json_encode_NoUnicode($arr)
{
	if( !is_array($arr) )
	{
		return $arr;
	}

	$arr_urlencode = arrUrlEncode($arr);
	return urldecode(json_encode($arr_urlencode));
}

function arrUrlEncode($arr)
{
	if( is_array($arr) && (count($arr) > 0) )
	{
		foreach( $arr as $key => $value )
		{
			if( is_string($value) )
			{
				//需要转义的字符
				$value = jsonStrEscape($value);

				$arr[$key] = urlencode($value);
			}
			else if( is_array($value) )
			{
				$arr[$key] = arrUrlEncode($value);
			}
		}
	}

	return $arr;
}

//json字符串转义
function jsonStrEscape($str)
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

//====http api接口数据获取函数====
/*
$url:表示访问的链接地址
$method:表示推送数据的方式,post方式或get方式,默认为get方式
$data:要推送的数据.格式:array/string
$headers:http头,例如:array("host: 127.0.0.1","Content-Type: image/jpeg")
返回值:推送成功,返回网页返回的数据信息.具体数据信息根据$url地址返回结果说明.
       推送失败,返回array数据.
*/
function CurlHttp($url, $method = 'get', $data = '', $headers = array())
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
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
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

/* 写文件
* file:文件路径
* str:内容
* rewrite:重写标志(默认为a+).a+追加方式,w清除旧数据再写
*/
function WriteFile($file,$str,$mode='a+')
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

?>

