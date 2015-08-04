<?php

 

class base_session{
    
    private $_sess_id;
    private $_sess_key = 's';
    private $_session_started = false;
    private $_sess_expires = 60;
    private $_cookie_expires = 0;
    private $_session_destoryed = false;

    function __construct() 
    {
        if(defined('SESS_NAME') && constant('SESS_NAME'))    $this->_sess_key = constant('SESS_NAME');
        if(defined('SESS_CACHE_EXPIRE') && constant('SESS_CACHE_EXPIRE'))   $this->_sess_expires = constant('SESS_CACHE_EXPIRE');
    }//End Function
    
    public function sess_id(){
        return $this->_sess_id;
    }
    
    public function set_sess_id($sess_id){
        return $this->_sess_id=$sess_id;
    }



    public function set_sess_expires($minute) 
    {
        $this->_sess_expires = $minute;
    }//End Function

    public function set_cookie_expires($minute) 
    {
        $this->_cookie_expires = ($minute > 0) ? $minute : 0;
        if(isset($this->_sess_id)){
            $cookie_path = kernel::base_url();
            $cookie_path = $cookie_path ? $cookie_path : "/";
            header(sprintf('Set-Cookie: %s=%s; path=%s; expires=%s; httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path, gmdate('D, d M Y H:i:s T', time()+$minute*60)), true);
        }
    }//End Function

    public function start($cookie_path=null){
        if($this->_session_started !== true){
			if($cookie_path == null){
				$cookie_path = kernel::base_url();
				$cookie_path = $cookie_path ? $cookie_path : "/";
			}
            if($this->_cookie_expires > 0){
                $cookie_expires = sprintf("expires=%s;",  gmdate('D, d M Y H:i:s T', time()+$this->_cookie_expires*60));
            }else{
                $cookie_expires = '';
            }

            //sess_id可能重复的情况下，将新生成的sess_id的session值设置为空-----weifeng 2013-8-24 13:24
            if(isset($_GET['sess_id'])){
                $this->_sess_id = $_GET['sess_id'];
                if($_COOKIE[$this->_sess_key] != $_GET['sess_id'])
                    header(sprintf('Set-Cookie: %s=%s; path=%s; %s httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path, $cookie_expires), true);
                if(base_kvstore::instance('sessions')->fetch($this->_sess_id, $_SESSION) === false){
                    $_SESSION = array();
                }
            }elseif($_COOKIE[$this->_sess_key]){
                $this->_sess_id = $_COOKIE[$this->_sess_key];
                if(base_kvstore::instance('sessions')->fetch($this->_sess_id, $_SESSION) === false){
                    $_SESSION = array();
                }
            }elseif(!$this->_sess_id){
                $this->_sess_id = md5($_SERVER['SERVER_ADDR'].base_request::get_remote_addr().uniqid().mt_rand(0,9999));
                $_SESSION = array();
                header(sprintf('Set-Cookie: %s=%s; path=%s; %s httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path, $cookie_expires), true);
            }
            
            $this->_session_started = true;
            register_shutdown_function(array(&$this,'close'));
        }
        return true;
    }

    public function close($writeBack = true){
        if(strlen($this->_sess_id) != 32){
            return false;
        }
        if(!$this->_session_started){
            return false;
        }
        $this->_session_started = false;
        if(!$writeBack){
            return false;
        }
        if($this->_session_destoryed){
            return true;
        }else{
            return base_kvstore::instance('sessions')->store($this->_sess_id, $_SESSION, ($this->_sess_expires * 60));
        }
    }
    
    public function destory(){
        if(!$this->_session_started){
            return false;
        }
        $this->_session_started = false;
        $res = base_kvstore::instance('sessions')->store($this->_sess_id, array(), 1);
        if($res){
            $_SESSION = array();
            $this->_session_destoryed = true;
            $cookie_path = kernel::base_url();
            $cookie_path = $cookie_path ? $cookie_path : "/";
            header(sprintf('Set-Cookie: %s=%s; path=%s; httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path), true);
            unset($this->_sess_id);
            return true;
        }else{
            return false;
        }
    }

}
