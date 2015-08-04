<?php
 
 
class b2c_ctl_admin_passport extends desktop_controller{
    var $login_times_error=3;

    function login(){
        $this->pagedata['message'] = $_SESSION['loginmsg'];
        unset($_SESSION['loginmsg']);
        $this->pagedata['show_varycode'] = $this->checkVeryCode();
        $this->system->session_close();
        if($_COOKIE["SHOPEX_LOGIN_NAME"]){
            $this->pagedata['username']=$_COOKIE["SHOPEX_LOGIN_NAME"];
            $this->pagedata['save_login_name']=true;
        }
        $this->display('login.html');
    }

    function checkVeryCode()
    {
        if($this->app->getConf('system.admin_verycode') || ($this->app->getConf('system.admin_error_login_times')>$this->login_times_error && intval($this->app->getConf('system.admin_error_login_time')+3600)>time())){
            return true;
        }else{
            return false;
        }
    }

    function dologin(){
        if($this->app->getConf('system.admin_verycode') || $this->app->getConf('system.admin_error_login_times')>$this->login_times_error){
            if(strtolower($_POST["verifycode"]) !== strtolower($_SESSION["RANDOM_CODE"]))
            {
                $_SESSION['loginmsg'] = app::get('b2c')->_("验证码输入错误!");
                header('Location: index.php?app=b2c&ctl=admin_passport&act=login');
                exit;
            }
        }
        $oOpt = &$this->app->model('operators');
        $aResult = $oOpt->tryLogin($_POST);

        if ($aResult){
            if($_POST['save_login_name']){
                setcookie("SHOPEX_LOGIN_NAME",$_POST['usrname'],(time()+86400*10));
            }else{
                setcookie("SHOPEX_LOGIN_NAME","");
            }

            $this->app->setConf('system.admin_error_login_times',0);
            if($_REQUEST['return']){
                header("Location: index.php#".$_REQUEST['return']);
            }else{
                header("Location: index.php");
            }

        }else{
            if(intval($this->app->getConf('system.admin_error_login_time')+3600)>time()){
                $this->app->setConf('system.admin_error_login_times',$this->app->getConf('system.admin_error_login_times')+1);
            }else{

                $this->app->setConf('system.admin_error_login_times',1);
            }
            $this->app->setConf('system.admin_error_login_time',time());
            $_SESSION['loginmsg'] = app::get('b2c')->_('用户名或密码错误!');
            header('Location: index.php?app=b2c&ctl=admin_passport&act=login');
            exit;
        }
    }

    function logout(){
        $this->user->id = 0;
        $_SESSION = array();
        header('Location: index.php?app=b2c&ctl=admin_passport&act=login');
    }

}
