<?php
/**
 * @author yindingsheng
 * @version 2013年3月29日14:35:16
 * @package messenger
 * @description 由于短信平台接口变更，调整为新的业务接口
 */

class b2c_messenger_smsweb{

    /**
     * 服务器时间接口
     * http://newsms.ex-sandbox.com/sms_webapi/(内网)
     * http://webapi.sms.shopex.cn(线上)
     */
//    private $timeUrl = 'http://webapi.sms.shopex.cn';
//
//    /**
//     * 免登录地址
//     * http://newsms.ex-sandbox.com(内网)
//     * http://sms.shopex.cn(线上)
//     */
//    private $passLoginUrl = 'http://sms.shopex.cn/index.php?';
//
//    /**
//     * 用户短信账户信息获取接口
//     * http://webpy.ex-sandbox.com/(内网)
//     * http://api.sms.shopex.cn(线上)
//     */
//    private $accountUrl = 'http://api.sms.shopex.cn';
//
//    /**
//     * 短信发送接口
//     * http://webpy.ex-sandbox.com/(内网)
//     * http://api.sms.shopex.cn(线上)
//     */
//    private $sendUrl = 'http://api.sms.shopex.cn';

    /**
     * 激活码接口
     * http://newsms.ex-sandbox.com/sms_webapi/(内网)
     * http://webapi.sms.shopex.cn(线上)
     */
    private $codeUrl = 'http://webapi.sms.shopex.cn';

    public function __construct() {
        $this->httpClient = kernel::single('base_httpclient');
        $this->httpClient->set_timeout(6);
		$this->webserviceClient = kernel::single('base_httpclient');

        //启用测试数据
        //$this->testInit();
    }

    /**
     * @description 验证激活码
     * @access public
     * @param void
     * @return void
     */
    public function checkCode(&$msg) {
        $data['certi_app'] = 'sms.check_active';
        $data['entId'] = $this->getEntId();
        $data['entPwd'] = $this->getEntAc();
        $data['active_code'] = app::get('b2c')->getConf('activation_code');
        if(!$data['active_code']){$msg = app::get('b2c')->_('验证码不存在！');return false;}
        $data['license'] = $this->getCerti();
        if($data['license']==false){$msg = app::get('b2c')->_('no license');return false;}

        $data['source'] = $this->getSource();
        $data['version'] = '1.0';
        $data['format'] = 'json';
        $data['timestamp'] = $this->getTime($msg);
        if($data['timestamp']==false)return false;

        $data['certi_ac'] = $this->make_shopex_ac($data,$this->getSourceToken());

        $result = $this->httpClient->post($this->codeUrl,$data);

        if($result['res']=='succ'){
            $msg = app::get('b2c')->_('生成激活码成功');
            return true;
        }elseif($result['res']=='fail'){
            $msg = $result['info'];
            return false;
        }
        return false;
    }

    /**
     * @description 生成激活码
     * @access public
     * @param void
     * @return void
     */
    public function createCode(&$msg) {
        $data['certi_app'] = 'sms.create_code';
        $data['entId'] = $this->getEntId();
        $data['entPwd'] = $this->getEntAc();

        $data['mobile'] = app::get('b2c')->getConf('store.mobile');
        if(!$data['mobile']){$msg = app::get('b2c')->_('商品手机号不能为空！');return false;}
        $data['license'] = $this->getCerti();
        if($data['license']==false){$msg = app::get('b2c')->_('no license');return false;}

        $data['source'] = $this->getSource();
        $data['version'] = '1.0';
        $data['format'] = 'json';
        $data['timestamp'] = $this->getTime($msg);
        if($data['timestamp']==false)return false;
        $data['certi_ac'] = $this->make_shopex_ac($data,$this->getSourceToken());

        $result = $this->httpClient->post($this->codeUrl,$data);
        $result = json_decode($result,true);

        if($result['res']=='succ'){
            $msg = app::get('b2c')->_('生成激活码成功');
            return true;
        }elseif($result['res']=='fail'){
            $msg = $result['info'];
            return false;
        }
        return false;
    }


    /**
     * @description 短信发送
     * @access public
     * @param void
     * @return void
     */
    public function send($contents,$config,&$msg,$sendType=0) {

        $webmsgclient = kernel::single('base_webmsgclient');
		$webserverurl = app::get('desktop')->getConf('sms.config.wsdl');
		$webmsgclient->setSoapCilent($webserverurl);

		$param['strContent'] = $contents[0]['content'];
		$param['strMobile'] = $contents[0]['phones'];
		$param['strUserName'] = app::get('desktop')->getConf('sms.config.username');
		$param['strPassword'] = app::get('desktop')->getConf('sms.config.password');
		$param['publicKey'] = app::get('desktop')->getConf('sms.config.pubkey');
		$param['sendType'] = $sendType;

		if($webmsgclient->sendMsg($param)){
			$msg = app::get('b2c')->_('短信发送成功！');
            return true;
		}else{
			$msg = app::get('b2c')->_('短信发送失败！');
			return false;
		}

    }

    /**
     * @description 获取免登录地址
     * @access public
     * @param void
     * @return void
     */
    public function getSmsBuyUrl() {
        $iBase64 = kernel::single('b2c_messenger_iBase64');

        $data['biz_id'] = $iBase64->encode($this->getSource());
        $data['entid'] = $this->getEntId();
        $data['ac'] = md5($data['entid'].$this->getEntAc());
        $data['t'] = $this->getTime($msg);

        $params['ctl'] = 'sms';
        $params['act'] = 'prdsList';
        $params['source'] = $iBase64->encode(implode('|',$data));

        $url = $this->passLoginUrl.http_build_query($params);
        return $url;
    }

    /**
     * @description 获取用户短信账户信息
     * @access public
     * @param void
     * @return void
     */
    public function getSmsAccount(&$msg) {
        $data['certi_app'] = 'sms.info';
        $data['entId'] = $this->getEntId();
        $data['entPwd'] = $this->getEntAc();
        $data['source'] = $this->getSource();
        $data['version'] = '1.0';
        $data['format'] = 'json';
        $data['timestamp'] = $this->getTime($msg);
        if($data['timestamp']==false) return false;
        $data['certi_ac'] = $this->make_shopex_ac($data,$this->getSourceToken());

        $result = $this->httpClient->post($this->accountUrl,$data);
        $result = json_decode($result,true);
        if($result['res']=='succ'){
            return $result;
        }elseif($result['res']=='fail'){
            $msg = $result['info'];
            return false;
        }
        return false;
    }

    /**
     * @description 获取服务器时间戳
     * @access public
     * @param void
     * @return void
     */
    public function getTime(&$msg) {
        $data['certi_app'] = 'sms.servertime';
        $data['version'] = '1.0';
        $data['format'] = 'json';
        $data['timestamp'] = '';
        $data['certi_ac'] = $this->make_shopex_ac($data,'SMS_TIME');
        $result = $this->httpClient->post($this->timeUrl,$data);

        $result = json_decode($result,true);
        if($result['res']=='succ'){
            return $result['info'];
        }elseif($result['res']=='fail'){
            $msg = $result['info'];
            return false;
        }else{
            $msg = app::get('smstrans')->_('接口地址无法相应！');
            return false;
        }
    }

    /**
     * @description 获取license
     * @access public
     * @param void
     * @return void
     *
     * 测试License: 1997371231
     */
    public function getCerti() {
        if($this->certi){
            return $this->certi;
        }elseif(base_certificate::get('certificate_id')){
            return  base_certificate::get('certificate_id');
        }else{
            return false;
        }
    }

    /**
     * @description 设置license
     * @access public
     * @param string $certi
     * @return void
     */
    public function setCerti($certi) {
        $this->certi = $certi;
    }

    /**
     * @description 获取token
     * @access public
     * @param void
     * @return void
     *
     * 测试Token: efa5552656ed12705a4afb3502405e45d48f948001291de47ac6fd17bd19013c
     */
    public function getToken() {
        if($this->token){
            return $this->token;
        }elseif(base_certificate::get('token')){
            return  base_certificate::get('token');
        }else{
            return false;
        }
    }

    /**
     * @description 设置token
     * @access public
     * @param string $token
     * @return void
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * @description 获取企业帐号
     * @access public
     * @param void
     * @return String
     *
     * 测试企业帐号: 113110510556
     */
    public function getEntId() {
        if($this->entid) {
            return $this->entid;
        }else{
            return base_enterprise::ent_id();
        }
    }

    /**
     * @description 设置企业帐号
     * @access public
     * @param string $entid
     * @return void
     */
    public function setEntId($entid) {
        $this->entid = $entid;
    }

    /**
     * @description 获取企业密码
     * @access public
     * @param void
     * @return String
     *
     * 测试企业密码: md5('123jjzh'.'ShopEXUser');
     */
    public function getEntAc() {
        if($this->entac) {
            return $this->entac;
        }else{
            return base_enterprise::ent_ac();
        }
    }

    /**
     * @description 设置企业密码
     * @access public
     * @param string $entac
     * @return void
     */
    public function setEntAc($entac) {
        $this->entac  =$entac;
    }

    /**
     * @description 获取业务产品ID
     * @access public
     * @param void
     * @return void
     *
     * 测试产品ID:338049
     */
    public function getSource() {
        return defined('SOURCE_ID') ? SOURCE_ID : '373615';
    }

    /**
     * @description 业务产品对应的Token
     * @access public
     * @param void
     * @return void
     *
     * 测试产品token:ac584f6d022ead5f4d8b5d1e6a80a7d1
     */
    public function getSourceToken() {
        return defined('SOURCE_TOKEN') ? SOURCE_TOKEN: 'c634a9b816f26956391a56cd6474b15c';
    }

    public function make_shopex_ac($temp_arr,$token){
        ksort($temp_arr);
        $str = '';
        foreach($temp_arr as $key=>$value){
            if($key!='certi_ac') {
                $str.= $value;
            }
        }
        return strtolower(md5($str.strtolower(md5($token))));
    }

    /**
     * @description 测试初始化
     * @access public
     * @param void
     * @return void
     */
    public function testInit() {

         // 服务器时间接口(内网)
         $this->timeUrl = 'http://newsms.ex-sandbox.com/sms_webapi/';

         // 免登录地址(内网)
        $this->passLoginUrl = 'http://newsms.ex-sandbox.com/index.php?';

        //用户短信账户信息获取接口(内网)
        $this->accountUrl = 'http://webpy.ex-sandbox.com/';

        //短信发送接口(内网)
        $this->sendUrl = 'http://webpy.ex-sandbox.com/';

        //激活码接口(内网)
        $this->codeUrl = 'http://newsms.ex-sandbox.com/sms_webapi/';

        //测试license
        //$this->certi = '1997371231';

        //测试token
        //$this->token = 'efa5552656ed12705a4afb3502405e45d48f948001291de47ac6fd17bd19013c';

        //测试企业帐号
        //$this->entid = '113110510556';

        //测试企业密码
        //$this->entac = md5('123jjzh'.'ShopEXUser');
    }
}
?>
