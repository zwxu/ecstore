<?php

function post($post_data,$url){

    $header = "Content-type: text/xml";//定义content-type为xml
	echo "<pre>";
	echo "=====Post========<br/>";
	print_r($post_data);
	echo "<br/>=====Post========<br/>";
	echo "</pre>";


		//初始化一个cURL会话
	$ch = curl_init();

	//curl将会获取url站点的内容,设置URL和相应的选项
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// 我们在POST数据哦！
	curl_setopt($ch, CURLOPT_POST, 1);

	// 把post的变量加上
	//传入参数$post_data中有多维数组时需要json_encode后才能传过去
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

	//抓取URL并把它传递给浏览器
	$output = curl_exec($ch);
	
	//关闭cURL资源，并且释放系统资源
	curl_close($ch);
	
	return  $output;
}

function output($output){

	echo "<br/>";
	echo "============返回结结果";
	echo "<pre>";

	print_r(json_decode(trim($output, chr(239).chr(187).chr(191)),true));

	echo "</pre>";
	echo "<br/>============返回结结果end";
	//调试使用
	if ($output === FALSE) {
		echo "cURL Error: " . curl_error($ch);
	}

}

//数据封装
function assemble($params){
    if(!is_array($params))  return null;
    ksort($params, SORT_STRING);
    $sign = '';
    foreach($params AS $key=>$val){
        if(is_null($val))   continue;
        if(is_bool($val))   $val = ($val) ? 1 : 0;
        $sign .= $key . (is_array($val) ? assemble($val) : $val);
    }
    return $sign;
}//End Function

function gen_sign($params,$token){
    return strtoupper(md5(strtoupper(md5(assemble($params))).$token));
}
