<?php

class desktop_cert_certcheck
{
    function __construct($app)
        {
        $this->app = $app;
    }
    function check($app)
    {
        $opencheck = false;
        $objCertchecks = kernel::servicelist("desktop.cert.check");
        foreach ($objCertchecks as $objCertcheck)
        {
            if(method_exists($objCertcheck , 'certcheck') && $objCertcheck->certcheck()){
                $opencheck = true;
                break;
            }
        }
        if(!$opencheck || $this->is_internal_ip()) return ;

        $activation_arr = $this->app->getConf('activation_code');
        if($activation_arr) return ;
        else
        {
            echo $this->error_view();
            exit;
        }
    }

    function getform()
    {
        $render = $this->app->render();
        $render->pagedata['res_url'] = $this->app->res_url;
        $render->pagedata['auth_error_msg'] = $auth_error_msg;
        return $render->display('active_code_form.html');
    }

    function error_view($auth_error_msg=null)
    {
        $render = $this->app->render();
        $render->pagedata['res_url'] = $this->app->res_url;
        $render->pagedata['auth_error_msg'] = $auth_error_msg;
        return $render->display('active_code.html');
    }
    /**
      *     ocs :
      *     $method = 'active.do_active'
      *     $ac = 'SHOPEX_ACTIVE'
      *
      *     其它产品默认
      */
    function check_code($code=null,$method='oem.do_active',$ac = 'SHOPEX_OEM')
    {
        if(!$code)return false;
        $certificate_id = base_certificate::certi_id();
        if(!$certificate_id)base_certificate::register();
        $certificate_id = base_certificate::certi_id();
        $token =  base_certificate::token();
        $data = array(
        'certi_app'=>$method,
        'certificate_id'=>$certificate_id,
        'active_key'=>$_POST['auth_code'],
        'ac'=>md5($certificate_id.$ac));
        $result = kernel::single('base_httpclient')->post(LICENSE_CENTER_INFO,$data);
        $result = json_decode($result,true);
        return $result;
    }

    function check_certid()
    {
        $params['certi_app'] = 'open.login';
        $this->Certi = base_certificate::get('certificate_id');
        $this->Token = base_certificate::get('token');
        $params['certificate_id']  = $this->Certi;
        $params['format'] = 'json';
        /** 增加反查参数result和反查基础地址url **/
        $code = md5(microtime());
        base_kvstore::instance('ecos')->store('net.login_handshake',$code);
        $params['result'] = $code;
        $obj_apps = app::get('base')->model('apps');
        $tmp = $obj_apps->getList('*',array('app_id'=>'base'));
        $app_xml = $tmp[0];
        $params['version'] = $app_xml['local_ver'];
        $params['url'] = kernel::base_url(1);
        /** end **/
        $token = $this->Token;
        $str   = '';
        ksort($params);
        foreach($params as $key => $value){
            $str.=$value;
        }
        $params['certi_ac'] = md5($str.$token);
        $http = kernel::single('base_httpclient');
        $http->set_timeout(20);
        $result = $http->post(LICENSE_CENTER_INFO,$params);
        $api_result = stripslashes($result);
        $api_arr = json_decode($api_result,true);
        return $api_arr;
    }
    function listener_login($params)
    {
        $opencheck = false;
        $objCertchecks = kernel::servicelist("desktop.cert.check");
        foreach ($objCertchecks as $objCertcheck)
        {
            if(method_exists($objCertcheck , 'certcheck') && $objCertcheck->certcheck()){
                $opencheck = true;
                break;
            }
        }
        if(!$opencheck || $this->is_internal_ip()) return ;

        if($params['type'] === pam_account::get_account_type('desktop'))
        {
            $result = $this->check_certid();
            if($result['res'] == 'succ' && $result['info']['valid'])
            {
                return ;
            }
            else
            {
                unset($_SESSION['account'][$params['type']]);
                $url = $this->app->base_url(1);
                $code_url = $url.'index.php?app=desktop&ctl=code&act=error_view';
                echo "激活码失效或者检查超时，点击<a href='{$url}'/>重新登录</a>,或者   <a href='{$code_url}' />重新输入激活码</a>激活";
                exit;
            }
        }

    }

    function is_internal_ip()
    {
        return true;
        //return false;
        $ip = $this->remote_addr();
        if($ip=='127.0.0.1' || $ip=='::1'){
            return true;
        }
        $ip = ip2long($ip);
        $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
        $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
        $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
        return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c;
    }


    function remote_addr()
    {
        if(!isset($GLOBALS['_REMOTE_ADDR_'])){
            $addrs = array();

            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
                foreach( array_reverse( explode( ',',  $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) as $x_f )
                {
                    $x_f = trim($x_f);

                    if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f ) )
                    {
                        $addrs[] = $x_f;
                    }
                }
            }

            $GLOBALS['_REMOTE_ADDR_'] = isset($addrs[0])?$addrs[0]:$_SERVER['REMOTE_ADDR'];
        }
        return $GLOBALS['_REMOTE_ADDR_'];
    }
}
