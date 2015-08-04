<?php

class goodsapi_shopex_shop_login extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    function shopex_shop_login(){
        $params = $this->params;

        //必填应用级参数是否定义
        if( !isset($params['user_name']) || !isset($params['password']) ){
            $error['code'] = null;
            $error['msg'] = '应用级必填参数未填写';
            $this->send_error($error);
        }

        //系统级必填参数是否定义
        if( !isset($params['api_version']) || !isset($params['ac']) ){
            $error['code'] = null;
            $error['msg'] = '系统必填参数未填写';
            $this->send_error($error);
        }elseif($params['api_version'] != $this->api_version){ //api版本是否一致
            $this->send_error('0x011');
        }

        //检查签名是否有效
        $sign = $this->get_sign($params,$this->token);
        if( $sign != $params['ac']){
            $error['code'] = null;
            $error['msg'] = '签名无效';
            $this->send_error($error);
        }

        $password_string = pam_encrypt::get_encrypted_password($params['password'],'shopadmin',array('login_name'=>$params['user_name']));
         $rows = app::get('pam')->model('account')->getList('*',array(
                'login_name'=>$params['user_name'],
                'login_password'=>$password_string,
                'account_type' => 'shopadmin',
                'disabled' => 'false',
                ),0,1);

        if($rows[0]){
             //判断用户是否启用
            $user_data = app::get('desktop')->model('users')->dump(array('user_id'=>$rows[0]['account_id'],'status'=>'1'),'*',array( ':account@pam'=>array('*') ));
            if($user_data){
                app::get('goodsapi')->setConf('shangpintong_login_id',$rows[0]['account_id']);
                if(isset($params['is_admin'])){
                    app::get('goodsapi')->setConf('is_admin',$params['is_admin']);
                }

                $session = md5(time().$rows);
                $filter = array(
                    'prefix' =>  'goodsapi',
                    'key' => 'shangpintong_login_session'.$rows[0]['account_id'],
                    'value' => $session,
                );
                $obj_session = kernel::single('base_session');
                $obj_session->set_sess_id(md5($session));
                $obj_session->set_sess_expires(0); //永久保存
                $obj_session->start();
                $_SESSION['account']['shopadmin'] = '1';
                $_SESSION['account']['user_data'] = $user_data;

                if( app::get('base')->model('kvstore')->save($filter) ){
                    $data['session'] = $session;
                    $this->send_success($data);
                }
            }else{
                $this->send_error(array('msg'=>'管理员账号未启用'));
            }
        }else{
            $this->send_error(array('code'=>'0x001'));
        }
    }
}

