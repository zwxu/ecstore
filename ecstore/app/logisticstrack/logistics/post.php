<?php
require_once('http_request.php');
function get_info($get)
{
	$data['status'] = 0;
	$http_request = new http_request();
	$params = $_GET;
	$data = $http_request->post($params,'http://api.ex-sandbox.com/php/');
	return $data;
}

function check_sign($params)
{	
	$data['code'] = $params['code'];
	$data['name'] = $params['name'];
	$data['date'] = $params['date'];
	ksort($data);
	$tmp_verfy='';
	foreach($data as $key=>$value){
		$tmp_verfy.=$key.$value;
	}
	$sign_o = strtoupper(md5(strtoupper(md5($tmp_verfy)).$params['cert_id']));
	if($params['sign'] === $sign_o)
		return true;
	else
		return false;
}
?>