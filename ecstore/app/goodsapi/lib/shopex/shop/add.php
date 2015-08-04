<?php

class goodsapi_shopex_shop_add extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    //增加店铺接口
    function shopex_shop_add(){
        $params = $this->params;

        //检查当前网站的证书是否存在
        $certi = base_certificate::get('certificate_id');
        $token = base_certificate::get('token');
        if(empty($certi) || empty($token)){
            $error['code'] = null;
            $error['msg'] = '网店证书无效';
            $this->send_error($error);
        }

        //必须参数是否定义
        if( !isset($params['certificate_id']) || !isset($params['certificate_salt']) || !isset($params['api_version']) || !isset($params['ac']) ){
            $error['code'] = null;
            $error['msg'] = '必须参数没定义';
            $this->send_error($error);
        }elseif($params['api_version'] && $params['api_version'] != $this->api_version){
            //调用的版本和当前版本是否一致
            $error['code'] = null;
            $error['msg'] = 'api版本不一致';
            $this->send_error($error);
        }

        //检查证书id是否一致
        if(isset($params['certificate_id']) && $params['certificate_id'] != $certi){
            $error['code'] = null;
            $error['msg'] = '证书ID不一致';
            $this->send_error($error);
        }

        //检查签名是否有效
        $sign = $this->get_sign($params,$token);
        if( $sign != $params['ac']){
            $error['code'] = null;
            $error['msg'] = '签名无效';
            $this->send_error($error);
        }

        $site_setting = array(
            'site_name' => 'system.shopname',
            'site_address' => 'store.address',
            'site_phone' => 'store.telephone',
            'site_zip_code' => 'store.zip_code',
            'score_set' => 'site.get_policy.method',
        );

        foreach($site_setting as $key=>$value){
            if($value == 'system.shopname'){
                $data[$key] = app::get('site')->getConf($value);
            }
            $data[$key] = app::get('b2c')->getConf($value);
        }

        $deploy = kernel::single('base_xml')->xml2array(file_get_contents(ROOT_DIR.'/config/deploy.xml'),'base_deploy');
        $data['shop_version'] = $deploy['product_name'].'V'.$deploy['product_version'];
        $data['site_type'] = 2; // 商品通中
        $image_size = IMAGE_MAX_SIZE/1024;//单位为KB
        $data['image_size'] = $image_size ? $image_size : 2048;
        $this->send_success($data);
    }
}

