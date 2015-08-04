<?php

define('HTTP_TIME_OUT',-3);
class base_curl{

    var $timeout = 10;
    var $defaultChunk = 4096;
    var $http_ver = '1.1';
    var $hostaddr = null;
    var $default_headers = array(
        'Pragma'=>"no-cache",
        'Cache-Control'=>"no-cache",
        'Connection'=>"close"
        );

    
    function set_timeout($timeout){
        $this->timeout = $timeout;
        return $this;
    }

    function action($action,$url,$headers=null,$callback=null,$data=null,$ping_only=false){
		$action = $action=='post'?true:false;
        $headers = array_merge($this->default_headers,(array)$headers);

        foreach((array)$headers as $k=>$v){
            $set_headers[] .= $k.': '.$v;
        }

		$this->responseBody = '';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'callback_header'));
		curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this,'callback_body'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		if($set_headers)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $set_headers);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->http_ver);
		curl_setopt($ch, CURLOPT_POST, $action=='post'?true:false);

		curl_exec($ch);

		curl_close($ch);

        $this->callback = $callback;
		preg_match('/\d{3}/',$this->responseHeader,$match);
		$this->responseCode = $match[0];
		switch($this->responseCode){
			case 301:
			case 302:
			kernel::log(" Redirect \n\t--> ".$responseHeader['location']);
			return false;

			case 200:
			kernel::log(' OK');
			if($this->callback){
				if(!call_user_func_array($this->callback,array($this,$this->responseBody))){
					break;
				}
			}
			return $this->responseBody;

			case 404:
			kernel::log(' file not found');
			return false;

			default:
			return false;
		}

    }

	function callback_header($curl,$header){
		$this->responseHeader .= $header;
		return strlen($header);
	}
	function callback_body($curl,$content){
		$this->responseBody .= $content;
		return strlen($content);
	}
	function is_addr($ip){
		return preg_match('/^[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}\.[0-9]{1-3}$/',$ip);
	}

	private function microtime(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

}
