<?php

 

class base_shopnode
{
    static $snode= null;
    
    static function register($app_id, $data=null){
        return self::send_to_center($app_id, $data, 'node.reg');
    }

	static function update($app_id, $data=null){
        return self::send_to_center($app_id, $data, 'node.update');
    }
	
	static function send_to_center ($app_id, $data = null, $method = 'node.reg'){

		$app_info = app::get($app_id)->define();
		$obj_app = app::get($app_id);
		// 生成参数...
		$api_data = array(
			'certi_app'=>$method,
			'certificate_id'=>base_certificate::certi_id(),
			'node_type'=>'ecos.'.$app_id,
			'url'=>kernel::base_url(true),
			'version' => $app_info['version'],
			'channel_ver'=>$app_info['api_ver'],
			'api_ver'=>'1.2',
			'format'=>'json',
			'api_url'=>kernel::base_url(1).kernel::url_prefix().'/api',
		);

		//更新时，多带个参数
		if($method == 'node.update'){
			$api_data['node_id'] =  base_shopnode::node_id($app_id);
		}

		ksort($api_data);

		foreach($api_data as $key => $value){
			$str.=$value;
		}
		$api_data['certi_ac'] = strtoupper(md5($str.base_certificate::token()));
		$http = kernel::single('base_httpclient');
		$http->set_timeout(6);
		$result = $http->post(
			LICENSE_CENTER_V,
			$api_data);       
        $result = json_decode($result, true);
        if ($result['res'] == 'succ')
        {
            return self::set_node_id($result['info'], $app_id);
        }
        else
        {
            return false;
        }
	}

    static function get($code='node_id', $app_id='b2c'){
        
        if(!function_exists('get_node_id')){
            if(self::$snode===null){
                if($shopnode = app::get($app_id)->getConf('shop_site_node_id')){
                    self::$snode = unserialize($shopnode);
                }else{
                    self::$snode = array();
                }
            }
        }else{
            self::$snode = get_node_id();
        }
        
        return self::$snode[$code];
    }
    
    static function active($app_id='b2c'){
        if(self::get('node_id', $app_id)){
            kernel::log('Using exists shopnode: kvstore shop_site_node_id');
        }else{
            kernel::log('Request new shopnode');
            self::register($app_id);
        }
    }
    
    static function set_node_id($node_id, $app_id='b2c'){
        if(!function_exists('set_node_id')){
            // 存储kvstore.
            return app::get($app_id)->setConf('shop_site_node_id', serialize($node_id));
        }else{
            return set_node_id($node_id, $app_id);
        }
    }
    
    static function delete_node_id($app_id='b2c')
    {
        if (!function_exists('delete_node_id'))
        {
            return app::get($app_id)->setConf('shop_site_node_id', '');
        }
        else
        {
            return delete_node_id($app_id);
        }
    }
    
    /**
     * 转给接口ac验证用
     * @param array 需要验证的参数
	 * @param string app_id
     * @return string 结构sign
     */
    static function gen_sign($params,$app_id){
        return strtoupper(md5(strtoupper(md5(self::assemble($params))).self::token($app_id)));
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
    
    static function node_id($app_id)
	{ 
		if (!$app_id){
			$config = base_setup_config::deploy_info();		
			foreach($config['package']['app'] as $k=>$app){
				$app_xml = kernel::single('base_xml')->xml2array(file_get_contents(app::get($app['id'])->app_dir.'/app.xml'),'base_app');
				if (isset($app_xml['node_id'])&&$app_xml['node_id']=="true"&&!self::node_id($app['id'])){
					// 获取节点.
					if ($node_id = self::node_id($app['id'])){
						return $node_id;
					}
				}
			}
			return false;
		}
		else
			return self::get('node_id', $app_id); 
	}
    
    static function node_type($app_id='b2c'){ return self::get('node_type', $app_id); }
	
	static function token($app_id='b2c'){ return self::get('token', $app_id); }
}