<?php


class openid_denglu{

    var $commit_url = 'http://www.ecopen.cn/api/';
    var $webtype = '3';

    function __construct(){
        $this->license_id = base_certificate::get('certificate_id');
        $this->license_key = base_certificate::get('token');
        $this->entid = base_enterprise::ent_id();
    }

    //创建站点账号
    function add(){
        if(empty($this->license_id)){
            $result = array(
                'rsp' => 'fail',
                'err_msg'=>'授权证书无效',
            );
            return $result;
        }
        $shopname = app::get('site')->getConf('site.name');
        $host_url = kernel::base_url(1);
        $params['method'] = 'denglu.site.add';
        $params['license_id'] = $this->license_id;
        $params['entid'] = $this->entid;
        $params['name'] = $shopname;
        $params['url'] = $host_url;
        $params['token_url'] = $host_url.'/index.php/trust-callback.html';
        $params['webtype'] = $this->webtype;
        $params['timestamp'] = time();
        $params['sign_method'] = 'md5';
        $params['v'] = '1.0';
        $make_sign = kernel::single('openid_sign')->get_ce_sign($params,$this->license_key);
        $params['sign'] = $make_sign;
        $this->net = kernel::single('base_httpclient');
        $result = $this->net->post($this->commit_url,$params);
        $result = json_decode($result,true);
        if($result['rsp'] == 'succ'){
            app::get('openid')->setConf('appid',$result['data']['appid']);
            app::get('openid')->setConf('appkey',$result['data']['appkey']);
        }
        return $result;
    }

    //获取到当前信任登陆的用户信息
    function get_user(){
        $appid = app::get('openid')->getConf('appid');
        $appkey = app::get('openid')->getConf('appkey');
    	$token = app::get('openid')->getConf('trust_token',$token);
        $obj_sign = kernel::single('openid_sign');
        $params['method'] = 'denglu.user.get';
        $params['appid'] = $appid;
        $params['timestamp'] = time();
        $params['sign_method'] = 'md5';
        $params['v'] = '1.0';
        $params['token'] = $token;
        $make_sign = $obj_sign->get_ce_sign($params,$appkey);
        $params['sign'] = $make_sign;
        $this->net = kernel::single('base_httpclient');
        $result = $this->net->post($this->commit_url,$params);
        $result = json_decode($result,true);
        return $result;
    }

}

