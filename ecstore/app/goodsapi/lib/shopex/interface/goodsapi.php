<?php

class goodsapi_shopex_interface_goodsapi{

    var $api_version = '1.0';
    var $params = null;
    var $is_admin = 0; //1 系统管理员 0普通管理员
    var $session = '';
    var $user_id = '';
    Var $start_shopex_time = '';


    function __construct(){
        $this->params = $_POST;
        $this->token = base_certificate::get('token');
        $this->is_admin = app::get('goodsapi')->getConf('is_admin');
        $this->user_id = app::get('goodsapi')->getConf('shangpintong_login_id');
        if($_POST['session']){
            $obj_session = kernel::single('base_session');
            $obj_session->set_sess_id(md5($_POST['session']));
            $obj_session->set_sess_expires(0); //永久保存
            $obj_session->start();
        }
    }

    //检查api调用是否合法
    public function check($params){

        //系统级参数是否定义
        if( !isset($params['ac']) ||  !isset($params['api_version']) || !isset($params['session'] )){
            $error['code'] = null;
            $error['msg']  = '接口系统级参数必填';
            $this->send_error($error);
        }

        if(!$this->check_session($params['session'])){
            $error['code'] = '0x020';
            $this->send_error($error);
        }

        //api版本是否一致
        if( $params['api_version'] != $this->api_version){
            $error['code'] = null;
            $error['msg']  = 'api版本不一致';
            $this->send_error($error);
        }

        //签名是否有效
        $sign = $this->get_sign($params,$this->token);
        if($sign != $params['ac']){
            $error['code'] = null;
            $error['msg'] = '签名无效';
            $this->send_error($error);
        }
        return true;
    }

    //检查应用级必填参数是否定义
    public function check_params($must_params){
        if(empty($must_params)) return true;
        $params = $this->params;
        foreach($must_params as $must_params_v){
            if(!isset($params[$must_params_v])){
                $error['code'] = null;
                $error['msg']  = '必填参数未定义';
                $this->send_error('0x003');
            }
        }

        return true;
    }

    /*
     *api调用错误输出
     *
     *@error_data string 错误编码
     *@type stirng 输出类型
     * */
    public function send_error($error,$type='xml'){
        $params = $this->params;
        if(isset($params['return_data']) && empty($params['return_data'])){
            $type = $params['return_data'];
        }

        if( $error['code'] == null ){
            $error_code = null;
            $error_msg  =  $error['msg'];
        }else{
            $arr_error = $this->_error_list();
            $error_code = $error['code'];
            $error_msg  = $arr_error[$error_code];
        }

        $return_data = array(
            'result' => 'fail',
            'msg_code' => $error_code,
            'msg' => $error_msg,
            'shopex_time' => time(),
        );
        switch($type){
            case 'xml':
                     $data = $this->array2xml($return_data,'shopex');
                break;
        }
       echo $data;
       exit;
    }

    /*api调用成功输出
     *
     *@data array and string 输出数据
     *@type string 输出类型
    */
    public function send_success($data,$type='xml'){
        if(isset($params['return_data']) && empty($params['return_data'])){
            $type = $params['return_data'];
        }
        $return_data = array(
            'result' => 'success',
            'msg_code' => null,
            'msg' =>  null,
            'shopex_time' => time(),
            'info' => $data
        );
        switch($type){
            case 'xml':
                    $data = $this->array2xml($return_data,'shopex');
                break;
        }
        echo  $data;
        exit;
    }

    //检查用户是否拥有权限
    function user_permission($user_id,$permission){
        $hasrole = app::get('desktop')->model('hasrole');
        $roles = app::get('desktop')->model('roles');
        $menus = app::get('desktop')->model('menus');
        $sdf = $hasrole->getList('role_id',array('user_id'=>$user_id));
        $pass = array();
        foreach($sdf as $val){
          $pass[] = $roles->dump($val,'*');
        }
        $group = array();

       foreach($pass as $key){
           $work = unserialize($key['workground']);
           if(!$work){
               $error['code'] = '0x006';
               $this->send_error($error);
           }
           foreach($work as $val){
                $group[] = $val;
           }
        }

        if(!is_array($permission) && !in_array($permission,$group)){
            $error['code'] = '0x006';
            $this->send_error($error);
        }
        return $group;
    }


    function _error_list(){
        $error_msg = array(
            '0x001' => '用户名或密码错误',
            '0x002' => '请求/执行超时',
            '0x003' => '数据异常',
            '0x004' => '数据库执行失败',
            '0x005' => '服务器异常',
            '0x006' => '用户权限不够',
            '0x007' => '服务不可以用',
            '0x008' => '方法不可用',
            '0x009' => '签名无效',
            '0x010' => '版本丢失',
            '0x011' => 'API 版本异常',
            '0x012' => 'API 需要升级',
            '0x013' => '网店服务异常',
            '0x014' => '网店空间不足',
            '0x020' => 'session 已过期，请重新登录',
            '0x021' => '数据冲突，本地数据不是最新数据',
        );
        return $error_msg;
    }

    //array转换为xml
    function array2xml($data,$root='root'){
        $xml ='<'.$root.'>';
        $this->_array2xml($data,$xml);
        $xml.='</'.$root.'>';
        return $xml;
    }

    function _array2xml(&$data,&$xml){
        if(is_array($data)){
            foreach($data as $k=>$v){
                if(is_numeric($k)){
                    $xml.='<item>';
                    $xml.=$this->_array2xml($v,$xml);
                    $xml.='</item>';
                }else{
                    $xml.='<'.$k.'>';
                    $xml.=$this->_array2xml($v,$xml);
                    $xml.='</'.$k.'>';
                }
            }
        }elseif(is_string($data)){
            $xml.='<![CDATA['.$data.']]>';
        }else{
            $xml.=$data;
        }

    }

    //ac算法
    function get_sign($params,$token){
        if(empty($token)){
            $error['code'] = null;
            $error['msg']  = '网店证书异常';
            $this->send_error($error);
        }

        if(isset($params['ac'])) unset($params['ac']);
        return md5($this->assemble($params).$token);
    }

    function assemble($params){
        if(!is_array($params))  return null;
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= is_array($val) ? $this->assemble($val) : $val;
        }
        return $sign;
    }

    function check_session($session){
        if( empty($session) ) return false;
        $filter = array(
            'value'=>serialize($session)
        );
        $rs = app::get('base')->model('kvstore')->getList('*',$filter);
        if( $rs ){
            return true;
        }else{
            return false;
        }
    }
}

