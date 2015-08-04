<?php

 
class pam_passport_oauth implements pam_interface_passport{
    
    public $request_token_url = 'http://www.google.com/accounts/OAuthGetRequestToken';
    public $access_token_url = 'http://term.ie/oauth/example/access_token.php';
    public $oauth_consumer_key = 'anonymous';
    public $oauth_consumer_secret = 'anonymous';
    
    function get_name(){
        return 'Google Account';
    }

     function get_login_form($auth, $appid, $view, $ext_pagedata=array()){
        return '<a href="'.$auth->get_callback_url(__CLASS__).'"><img src="http://www.google.com/intl/en/images/logos/accounts_logo.gif" /></a>';
    }

    function login($auth,&$usrdata){
        $token = $this->request_token();
        $access_url = $this->access_url($token);
        return false;
        echo '<a href="'.$access_url.'">asfds</a>';
    }
    
   function loginout($auth,$backurl="index.php"){
        unset($_SESSION['account'][$this->type]);
        unset($_SESSION['last_error']);
        header('Location: '.$backurl);
    }

    function get_data(){
    }

    function get_id(){
    }

    function get_expired(){
    }

    private function access_url($token){
        $headers = array(
                'oauth_consumer_key'=>$this->oauth_consumer_key,
                'oauth_signature_method'=>'HMAC-SHA1',
                'oauth_timestamp'=>time(),
                'oauth_nonce'=>md5(microtime()),
                'oauth_version'=>'1.0',
                'oauth_token'=>$token['oauth_token'],
            );
        $headers['oauth_signature'] = $this->to_signature_key('GET',$this->access_token_url,$headers,$this->oauth_consumer_secret.'&'.$token['oauth_token_secret']);
        return $this->access_token_url.'?'.utils::http_build_query($headers);
    }

    private function request_token(){
        $headers = array(
                'oauth_consumer_key'=>$this->oauth_consumer_key,
                'oauth_signature_method'=>'HMAC-SHA1',
                'oauth_timestamp'=>time(),
                'oauth_nonce'=>md5(microtime()),
                'oauth_version'=>'1.0',
            );

        $headers['oauth_signature'] = $this->to_signature_key('POST',$this->request_token_url,$headers,$this->oauth_consumer_secret.'&');
        $result = kernel::single('base_httpclient')->post($this->request_token_url,$headers,$data);
        parse_str($result,$return);
        return $return;
    }

    private function to_signature_key($method,$url,$data,$secret){
        ksort($data);
        $data = $method.'&'.urlencode($url).'&'.utils::urlencode(http_build_query($data));
        return $this->hmacsha1($secret,$data);
    }

    private function hmacsha1($key,$data) {
        $blocksize=64;
        $hashfunc='sha1';
        if (strlen($key)>$blocksize)
            $key=pack('H*', $hashfunc($key));
        $key=str_pad($key,$blocksize,chr(0x00));
        $ipad=str_repeat(chr(0x36),$blocksize);
        $opad=str_repeat(chr(0x5c),$blocksize);
        $hmac = pack(
                    'H*',$hashfunc(
                        ($key^$opad).pack(
                            'H*',$hashfunc(
                                ($key^$ipad).$data
                            )
                        )
                    )
                );
        return base64_encode($hmac);
    }


    function get_config(){
        $ret = app::get('pam')->getConf('passport.'.__CLASS__);
        if($ret && isset($ret['shopadmin_passport_status']['value']) && isset($ret['site_passport_status']['value'])){
            return $ret;
        }else{
            $ret = $this->get_setting();
            $ret['passport_id']['value'] = __CLASS__;
            $ret['passport_name']['value'] = $this->get_name();
            $ret['shopadmin_passport_status']['value'] = 'false';
            $ret['site_passport_status']['value'] = 'false';
            $ret['passport_version']['value'] = '1.5';
            app::get('pam')->setConf('passport.'.__CLASS__,$ret);
            return $ret;        
        }
    }
    
    function set_config(&$config){
        $save = app::get('pam')->getConf('passport.'.__CLASS__);
        if(count($config))
            foreach($config as $key=>$value){
                if(!in_array($key,array_keys($save))) continue;
                $save[$key]['value'] = $value;
            }
        return app::get('pam')->setConf('passport.'.__CLASS__,$save);
    }

    function get_setting(){
        return array(
            'passport_id'=>array('label'=>app::get('pam')->_('通行证id'),'type'=>'text','editable'=>false),
            'passport_name'=>array('label'=>app::get('pam')->_('通行证'),'type'=>'text','editable'=>false),
            'shopadmin_passport_status'=>array('label'=>app::get('pam')->_('后台开启'),'type'=>'bool',),
            'site_passport_status'=>array('label'=>app::get('pam')->_('前台开启'),'type'=>'bool',),
            'passport_version'=>array('label'=>app::get('pam')->_('版本'),'type'=>'text','editable'=>false),
        );
    }

}
