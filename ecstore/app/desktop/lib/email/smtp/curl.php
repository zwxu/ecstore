<?php

 
////////////////////////////////////////////////////
class desktop_email_smtp_curl {
    var $SMTP_PORT = 25;
    
    var $CRLF = "\r\n";
    var $do_debug;       # the level of debug to perform

    var $curl;      # the socket to the server
    var $error;          # error if any on the last call
    var $helo_rply;      # the reply the server sent to us for HELO
    
	function desktop_email_smtp_curl() {
        $this->curl = 0;
        $this->error = null;
        $this->helo_rply = null;
        $this->do_debug = 0;
    }

    /*************************************************************
     *                    CONNECTION FUNCTIONS                  *
     ***********************************************************/
	
	private $host;
	private $port;
	private $tval;
    function Connect($host,$port=0,$tval=30) {
        # set the error val to null so there is no confusion
        $this->error = null;
		$this->host = $host;
		$this->port = $port;
		$this->tval = $tval;

        # make sure we are __not__ connected
        if($this->connected()) {
            # ok we are connected! what should we do?
            # for now we will just give an error saying we
            # are already connected
            $this->error =
                array("error" => "Already connected to a server");
            return false;
        }

        if(empty($port)) {
            $port = $this->SMTP_PORT;
        }
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_URL, $host);
		curl_setopt($this->curl, CURLOPT_PORT, $port);
		curl_setopt($this->curl, CURLOPT_TIMEOUT,$tval);

        if(curl_errno($this->curl)) {
            $this->error = array("error" => "Failed to connect to server",
                                 "errno" => curl_errno($this->curl),
                                 "errstr" => curl_error($this->curl));
            if($this->do_debug) {
                echo "SMTP -> ERROR: " . $this->error["error"] .":". $this->error["errstr"] ."(" .$this->error["errno"] .")". $this->CRLF;
            }
            return false;
        }
        return true;
    }

	private $username;
	private $password;
    function Authenticate($username, $password) {
		$this->username = base64_encode($username);
		$this->password = base64_encode($password);
        return true;
    }

    function Connected() {
		return !empty($this->curl);
    }

    function Close() {
        $this->error = null; # so there is no confusion
        $this->helo_rply = null;
		if($this->Connected()) curl_close($this->curl);
    }


    /***************************************************************
     *                        SMTP COMMANDS                       *
     *************************************************************/

    function Data($msg_data) {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called Data() without being connected");
            return false;
        }

		// === 请求 ===
		$content  = "EHLO ".($_SERVER['HTTP_HOST']?$_SERVER['HTTP_HOST']:'localhost').$this->CRLF;
		//$content  = "EHLO mail.shopex.cn".$this->CRLF;
		$content .= "AUTH LOGIN".$this->CRLF.$this->username.$this->CRLF.$this->password.$this->CRLF;
		$content .= "MAIL FROM:<".$this->from.">".$this->CRLF;
		$content .= "RCPT TO:<".$this->to.">".$this->CRLF;
		$content .= "DATA".$this->CRLF.$msg_data.$this->CRLF;
		$content .= ".".$this->CRLF."QUIT".$this->CRLF; 

    	curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $content);
		if($data = curl_exec($this->curl)) {
			//$data = curl_multi_getcontent($this->curl);
		} else {
			$this->error = array("error" => "请求失败");
			return false;
		}

		if($this->do_debug) {
			var_dump($data);
		}

		// === 错误处理 ===
		return $this->_parse($data);
	}

	function _parse($data) {
		$arrData = explode("\r\n",$data);
		// 第一个连接到smtp服务器
		if(($code = substr($arrData[0],0,3)) != 220) {
			$this->error = array("error" => "连接smtp服务器失败","stmp_code"=>$code,"stmp_msg"=>substr($arrData[0],3));
			return false;
		}
		// 第2个向smtp发送
		if(($code = substr($arrData[1],0,3)) != 250) {
			$this->error = array("error" => "连接smtp服务器失败","stmp_code"=>$code,"stmp_msg"=>substr($arrData[1],3));
			return false;
		}
		$flag = 0;
		foreach($arrData as $key=>$row) {
			if(($code = substr($arrData[$key],0,3)) == 334) {
				$flag = $key;
				break;
			}
		}
		$flag = $flag + 2;
		// 用户名&&密码验证失败
		if(($code = substr($arrData[$flag],0,3)) != 235) {
			$this->error = array("error" => "用户名&密码验证失败");
			return false;
		}
		// 发件人
		$flag = $flag + 1;
		if(($code = substr($arrData[$flag],0,3)) != 250) {
			$this->error = array("error" => "发件人错误","stmp_code"=>$code,"stmp_msg"=>substr($arrData[$flag],3));
			return false;
		}
		// 收件人
		$flag = $flag + 1;
		if(($code = substr($arrData[$flag],0,3)) != 250) {
			$this->error = array("error" => "收件人错误","stmp_code"=>$code,"stmp_msg"=>substr($arrData[$flag],3));
			return false;
		}
		// 发送成功
		$flag = $flag + 2;
		if(($code = substr($arrData[$flag],0,3)) != 250) {
			$this->error = array("error" => "发送失败","stmp_code"=>$code,"stmp_msg"=>substr($arrData[$flag],3));
			return false;
		}
		
		return true;
	}

    function Expand($name) {
        $this->error = null; # so no confusion is caused

        if(!$this->Connected()) {
            $this->error = array("error" => "Called Expand() without being connected");
            return false;
        } 
        return true;
    }

    function Hello($host="") {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called Hello() without being connected");
            return false;
        }
        return true;
    }

    function SendHello($hello, $host) {
    	$this->helo_rply = $hello;    
        return true;
    }

    function Help($keyword="") {
        $this->error = null; # to avoid confusion

        if(!$this->connected()) {
            $this->error = array("error" => "Called Help() without being connected");
            return false;
        }
        return $keyword;
    }

	private $from;
    function Mail($from) {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array(
                    "error" => "Called Mail() without being connected");
            return false;
        }
		
		$this->from = $from;
        
        if($this->do_debug) {
            echo "SMTP -> FROM SERVER:" . $this->CRLF . $this->from;
        } 
        return true;
    }

    function Noop() {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called Noop() without being connected");
            return false;
        }
        return true;
    }

    function Quit($close_on_error=true) {
        $this->error = null; # so there is no confusion

        if(!$this->connected()) {
            $this->error = array("error" => "Called Quit() without being connected");
            return false;
        }
		return true;
    }

	private $to;
	function Recipient($to) {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called Recipient() without being connected");
            return false;
        }
		$this->to = $to;
        return true;
    }

    function Reset() {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called Reset() without being connected");
            return false;
        }

		$this->Close();
		return $this->Connect($this->host,$this->port,$this->tval);
    }

    function Send($from) {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called Send() without being connected");
            return false;
        }
        return true;
    }

    function SendAndMail($from) {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called SendAndMail() without being connected");
            return false;
        } 
        return true;
    }

    function SendOrMail($from) {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called SendOrMail() without being connected");
            return false;
        }
        return true;
    }

    function Turn() {
        $this->error = array("error" => "This method, TURN, of the SMTP ".
                                        "is not implemented");
        if($this->do_debug >= 1) {
            echo "SMTP -> NOTICE: " . $this->error["error"] . $this->CRLF;
        }
        return false;
    }

    function Verify($name) {
        $this->error = null; # so no confusion is caused

        if(!$this->connected()) {
            $this->error = array("error" => "Called Verify() without being connected");
            return false;
		}
		return true;
    }

} // end class 
