<?php

 
class base_certificate{

    static $certi= null;

    static function register($data=null){
        $sys_params = base_setup_config::deploy_info();
        $code = md5(microtime());
        base_kvstore::instance('ecos')->store('net.handshake',$code);
        $app_exclusion = app::get('base')->getConf('system.main_app');
        /** 得到框架的总版本号 **/
        $obj_apps = app::get('base')->model('apps');
        $tmp = $obj_apps->getList('*',array('app_id'=>'base'));
        $app_xml = $tmp[0];
        $app_xml['version'] = $app_xml['local_ver'];
		if (defined('CERTIFICATE_SAS') && constant('CERTIFICATE_SAS')){
			$data = array(
				'certi_app'=>'open.reg',
				'app_id' => 'ecos.'.$app_exclusion['app_id'],
				'url' => $data ? $data : kernel::base_url(1),
				'result' => $code,
				'version'=> $app_xml['version'],
			);
		}else{
			$conf = base_setup_config::deploy_info();
			$data = array(
				'certi_app'=>'open.reg',
				'identifier'=>base_enterprise::ent_id(),
				'password'=>base_enterprise::ent_ac(),
				'product_key' => $conf['product_key'],
				'url' => $data ? $data : kernel::base_url(1),
				'result' => $code,
				'version'=> $app_xml['version'],
				'api_ver'=>'1.3',
			);
		}
        $http = kernel::single('base_httpclient');
        $http->set_timeout(6);
        $result = $http->post(
            LICENSE_CENTER,
            $data);
        //todo: 声称获取一个唯一iD，发给飞飞
        $result = json_decode($result,1);
        if($result['res']=='succ'){
            if ($result['info'])
            {
				/*
                if ($result['info']['node_id'])
                {
                    $arr_shop_node_id = array(
                        'node_id' => $result['info']['node_id'],
                    );
                    base_shopnode::set_node_id($arr_shop_node_id,$app_exclusion['app_id']);
                    unset($result['info']['node_id']);
                }
				*/

				//1.3接口不再返回node_id信息
				base_shopnode::register($app_exclusion['app_id']);
                $certificate = $result['info'];
                self::set_certificate($certificate);
            }
        }else{
            return false;
        }
    }
    
    /**
     * 获取证书的版权信息
     * @param string app id
     * @return boolean 成功与否
     */
    static function active_certi_info($app_id='b2c')
    {
        $ceti_app = 'open.certi_info';
        $certi_id = self::certi_id();
        $token = self::token();
        
        $certi_ac = md5($ceti_app.$certi_id.$token);
        $data = array(
            'certi_app'=>$ceti_app,
            'certificate_id'=>$certi_id,
            'certi_ac'=>$certi_ac,
        );
        
        $http = kernel::single('base_httpclient');
        $http->set_timeout(6);
        $result = $http->post(
            LICENSE_CENTER_INFO,
            $data);
        
        $result = json_decode($result, 1);
        if ($result['res'] == 'succ')
        {
            return self::set_certi_info($app_id, json_encode($result['info']));
        }
        else
        {
            kernel::log('Certificate info getting fail, ' . $result['msg']);
            return false;
        }
    }
    
    /**
     * 设置证书的正确权限信息
     * @param string app id
     * @param mixed certi info
     * @return boolean 成功与否
     */
    static function set_certi_info($app_id, $info)
    {
        if (!$app_id || !$info)    
            return false;
        
        return app::get($app_id)->setConf('certi_info', $info);
    }
    
    /**
     * 获取证书的版权信息
     * @param string app id
     * @return string key_type
     */
    static function certi_info($app_id='b2c')
    {
        $certi_info = app::get($app_id)->getConf('certi_info');
        $certi_info = json_decode($certi_info, 1);
        
        return $certi_info['key_type'];
    }

    static function get($code='certificate_id'){
        
        if(!function_exists('get_certificate')){
            if(self::$certi===null){
				if(ECAE_MODE){
					self::$certi = app::get('base')->getConf('certificate');
				}else{
					if(file_exists(ROOT_DIR.'/config/certi.php')){
						require(ROOT_DIR.'/config/certi.php');
						self::$certi = $certificate;
					}
				}
            }
        }else{
            self::$certi = get_certificate();
        }
        
        return self::$certi[$code];
    }
    
    static function active(){
        if(self::get()){
            kernel::log('Using exists certificate: config/certi.php');
        }else{
            kernel::log('Request new certificate');
            self::register();
        }
    }
    
    
    static function set_certificate($certificate){
        if(!function_exists('set_certificate')){
			if(ECAE_MODE){
				app::get('base')->setConf('certificate',$certificate);
			}else{
				return file_put_contents(ROOT_DIR.'/config/certi.php'
					,'<?php $certificate='.var_export($certificate,1).';');
			}
        }else{
            return set_certificate($certificate);
        }
    }
    static function del_certificate(){
        if(is_file(ROOT_DIR.'/config/certi.php'))        
            unlink(ROOT_DIR.'/config/certi.php');
    }
    static function gen_sign($params){
        return strtoupper(md5(strtoupper(md5(self::assemble($params)))));
    }
    
    static function assemble($params) 
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? self::assemble($val) : $val);
        }
        return $sign;
    }//End Function

    static function certi_id(){ return self::get('certificate_id'); }
    
    static function token(){ return self::get('token'); }


}
