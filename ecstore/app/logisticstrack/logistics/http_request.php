<?php
class http_request{
	
	public function post($post_data,$url)
	{
		ob_start();
		$o="";
		foreach ($post_data as $k=>$v)
		{
			$o.= "$k=".urlencode($v)."&";
		}
		$post_data=substr($o,0,-1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		//为了支持cookie
		//curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_exec($ch);
		$result = ob_get_contents();
		ob_end_clean();
		return json_decode($result,true);
	}
	
	function get($query,$url)
	{
		$info = parse_url($url);#print_r($info);exit;
		$fp = fsockopen($info["host"], 80, $errno, $errstr, 3);
		$head = "GET ".$info['path']."?".$info["query"]." HTTP/1.0\r\n";
		$head .= "Host: ".$info['host']."\r\n";
		$head .= "\r\n";
		$write = fputs($fp, $head);
		while (!feof($fp))
		{
			$line = fread($fp,4096);
			echo $line;
		}
	}
}
?>