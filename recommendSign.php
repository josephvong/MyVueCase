<?php
header("Access-Control-Allow-Origin: *");

//如果没有API请求地址直接返回错误
if (!isset($_POST['api_url']))
{
	echo "{'desc':'params error!'}";
	exit();
}

$params = array();
if (isset($_POST['api_url']))
	$api_url = $_POST['api_url'];
if (isset($_POST['app_key']))
	$app_key = $_POST['app_key'];

//重新组装请求参数
foreach($_POST as $key => $val)
{
	if (($key != 'api_url') && ($key != 'app_key'))
		$params[$key] = $val;
}

//请求参数转json
$post_data = json_encode($params);

//处理签名
$headers = array();
$times = time();
$data_str = $post_data . '&appkey=' . $app_key .'&time=' . $times;
array_push($headers, 'time:' . $times);
array_push($headers, 'sign:' . md5($data_str));

//API请求异常处理
try
{
	$rets = http_post_data($headers, $api_url, $post_data);
}
catch(Exception $e)
{
	echo "{'desc':'params or api error!'}";
	exit();
}

echo $rets;
exit();

//curl请求（header）
function http_post_data($headers, $url, $data_string)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Content-Length: ' . strlen($data_string))
	);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}
?>
