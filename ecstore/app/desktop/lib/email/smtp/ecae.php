<?php

 
////////////////////////////////////////////////////
class desktop_email_smtp_ecae {
    var $SMTP_PORT = 25;
    
    var $CRLF = "\r\n";
    var $do_debug;       # the level of debug to perform

    var $email;           # the socket to the server
    var $error;          # error if any on the last call
    var $helo_rply;      # the reply the server sent to us for HELO
    
	function desktop_email_smtp_curl() {
        $this->mail = null;
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
        $this->mail = new ecae_mail(); 
        $this->mail->smtp_host($host);
        $this->mail->smtp_port($port);
        $this->mail->smtp_timeout($tval);
        
        return true;
    }

	private $username;
	private $password;
    function Authenticate($username, $password) {
		$this->username = $username;
		$this->password = $password;
        return true;
    }

    function Connected() {
		return !empty($this->mail);
    }

    function Close() {
        $this->error = null; # so there is no confusion
        $this->helo_rply = null;
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

        $this->mail->smtp_username($this->username);
        $this->mail->smtp_password($this->password);

        $this->mail->smtp_option("from",$this->from);
        $this->mail->smtp_option("to",$this->to);
        $this->mail->smtp_option("subject",$msg_data["subject"]);
        $this->mail->smtp_option("Content-Type", "text/html; charset=utf-8; format=flowed");
        $this->mail->smtp_option("Date", date(r));
        $this->mail->smtp_data($msg_data["body"]);

        if($this->mail->send()) {
            return true;
        }
        return $this->_error_process($this->mail->get_errno());
	}
    
    private function _error_process($errno) {
        switch($errno) {
        case 0:
            return true;
        default:
            $arrError = $this->_get_error_msg();
            $msg = $arrError[$errno];
            $this->error = ($msg)? array("error" => $msg): array("error" =>"unknown error");
            return false;
        }
    } // end function _error_process

    private function _get_error_msg() {
        return array(
                100 => "host undefined",
                101 => "port undefined",
                102 => "username undefined",
                103 => "password undefined",
                104 => "body undefined",
                105 => "options empty",
                106 => "from undefined",
                107 => "to undefined",
                108 => "from format error",
                109 => "to format error",
                400 => "init failure",
        );
    } // end function _get_error_msg

    function Expand($name) {
        $this->error = null; # so no confusion is caused

        if(!$this->Connected()) {
            $this->error = array("error" => "called expand() without being connected");
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
        $this->mail->clean();
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
