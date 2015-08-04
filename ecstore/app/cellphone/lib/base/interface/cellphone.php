<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_interface_cellphone{

    var $api_version = '1.0';
    var $params = null;
    //var $is_admin = 0; //1 系统管理员 0普通管理员
    //var $session = '';
    //var $user_id = '';
    var $session='';
	 //DES算法的密钥
    var $des_private_key = 'szmall_b2c_cellphone';



    function __construct(){
        $this->params = $this->get_params($_POST,$_GET);
        
       

        //$this->token = base_certificate::get('token');

        $this->check($this->params);

        $this->params=$this->arrContentReplace($this->params);
       
    }

     //接收处理参数
    function get_params($post,$get){ 
    	if(!empty($post) && empty($get)){
    	   return $post;
    	}else if(empty($post) && !empty($get)){
    	   return $get;
    	}else if(empty($post) && empty($get)){
    	   return array();
    	}else{
           $retval = array_diff_key($get,$post);
           return array_merge_recursive($post,$retval);
    	}

    }


    //检查api调用是否合法
    final function check($params){

        //系统级参数是否定义
        //if( !isset($params['ac']) ||  !isset($params['api_version']) || !isset($params['session'] )){
       if( !isset($params['sign']) ||  !isset($params['api_version'])){
            $error['msg']  = '接口系统级参数必填';
            $this->send(false,null,$error['msg']);
        }



        //api版本是否一致
        if($params['api_version'] != $this->api_version){

          $error['msg']  = 'api版本不一致';
          $this->send(false,null,$error['msg']);
        }

        /*
        if(!$this->check_session($params['session'])){
             $error['msg']  = 'session 已过期，请重新登录';
             $this->send(false,null,$error['msg']);
        }
        */
        if($params['session']){
            /*
           if(defined('SESS_NAME') && constant('SESS_NAME')){
                $sess_key = constant('SESS_NAME');
           }else{
                $sess_key = 's';
           }
           $_COOKIE[$sess_key]=$params['session'];
           */


           kernel::single('base_session')->set_sess_id($params['session']);
           if(base_kvstore::instance('sessions')->fetch($params['session'], $_SESSION) === false){
              $_SESSION = array();
              $this->send('-1',null,'用户信息已超时或退出，请重新登录');
           }

           kernel::single('base_session')->set_sess_expires(0);
           //设置过期时间，单位：分
           kernel::single('base_session')->start();
        }

        //签名是否有效
        //$sign = $this->get_sign($params,$this->token);
        $postdata=$params;
        unset($postdata['sign']);
        $sign = base_certificate::gen_sign($postdata);
        
        /*
        if($sign != $params['sign']){
           $error['msg'] = '签名无效';
           $this->send(false,null,$error['msg']);
        }
        */
        return true;
    }


    /*
    *api检查应用级必填参数是否定义或为空
    *
    *@must_params array 必须检查的参数
    *      key:参数名
    *    value:参数注释
    * */
    final function check_params($must_params){
       if(empty($must_params)) return true;
       $params = $this->params;
       $errormsg1 = '必填参数未定义:';
       $errormsg2 = '必填参数为空:';

       foreach($must_params as $k=>$v){
            if(!isset($params[$k])){
              $this->send(false,null, $errormsg1.$v."(".$k.")");
            }elseif(empty($params[$k])){
              $this->send(false,null, $errormsg2.$v."(".$k.")");
            }
       }

       return true;
    }


    public function send($status,$data,$msg){
       if(!$status){
            $data =null;
       }

       $return_data = array(
            'success' =>$status,
            'msg' => $msg,
            'result'=>$data,
            'process_time' => time(),
        );
if($this->params['debug']=='cell'){
        echo '<pre>';
        print_r($return_data);
}
       echo  json_encode($return_data);
       exit;
    }


    /*
    //ac算法

    function get_sign($params){
       return strtoupper(md5(strtoupper(md5($this->assemble($params)))));
    }


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
    */


    //数据封装
    function assemble($params){
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? assemble($val) : $val);
        }
        return $sign;
    }//End Function





    /*
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
    */

    public  function get_current_member(){
        $obj_members = app::get('b2c')->model('members');
        return $obj_members->get_current_member();
    }

    private function arrContentReplace($array){
        if(is_array($array)){
            foreach($array as $key => $v){
                $array[$key] = $this->arrContentReplace($array[$key]);
            }
        }else{
            $array = strip_tags($array);
        }
        return $array;
    }

    // 获取图片完整路径
    public function get_img_url($image_id,$size){
        $url = cellphone_image_storager::image_path($image_id,$size);
        $url = empty($url)==true?"":$url;
        /*
        if(empty($url)){
            $imageDefault = app::get('cellphone')->getConf('image.set');
            $url = cellphone_image_storager::image_path($imageDefault[strtoupper($size)]['default_image']);
        }
        */
        return $url;
    }

	 //DES加密，偏移量IV同JAVA
    //$str 需要加密的字串
    //$key 密钥，须和手机端统一
    public function des_encode($str,$key){
    	$des = new cellphone_des_des($key);
    	$pwd = $des->encrypt($str);
    	return $pwd;
    }

    //DES解密，偏移量IV同JAVA
    //$str 需要解密的字串
    //$key 密钥，须和手机端统一
    public function des_decode($str,$key){
    	$des = new cellphone_des_des($key);
    	$pld = $des->decrypt($str);
    	return $pld;
    }
}

