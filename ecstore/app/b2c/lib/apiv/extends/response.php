<?php
/**
 * 路由器基类
 */

class b2c_apiv_extends_response
{
    //api object
    protected $apiv = null;

    public function __construct(&$app){

        $api_info = base_rpc_service::$api_info;

        if($api_info['from_node_id'] && $api_info['from_api_v']){
            $obj_b2c_shop = &app::get('b2c')->model('shop');
            $shop_info = $obj_b2c_shop->getList('node_type', array('node_id'=>$api_info['from_node_id'], 'status'=>'bind'));
            if( !( $node_type = $shop_info[0]['node_type'] ) ){
                kernel::log('no data in b2c_shop! from_node_id: ' . $api_info['from_node_id']);
                trigger_error('server reject!', E_USER_ERROR);
            }

            base_kvstore::instance('b2c_apiv')->fetch('apiv.mapper', $apiv_mapper);
            if( !$apiv_mapper ){
                kernel::log('no apiv_mapper!');
                trigger_error('server reject!', E_USER_ERROR);
            }

            $local_apiv = $apiv_mapper[ $node_type . '_' . $api_info['from_api_v'] ];
            if( !$local_apiv ){
                kernel::log('no data in apiv_mapper! node_type: ' . $node_type . ', node_apiv: ' . $api_info['from_api_v']);
                trigger_error('server reject!', E_USER_ERROR);
            }

        }
        else{
            $local_apiv = '2.0';
        }

        $this->apiv = $local_apiv;
    }

    public function __call($method, $params)
    {
        //api 版本历史
        $apiv_history = array(
            '2.0',
            '1.0'
            );

        $api_info = base_rpc_service::$api_info;
        $api_obj = NULL;

        $flag = false;
        foreach( $apiv_history as $v )
        {
            if( $this->apiv == $v )
                $flag = true;

            if( $flag )
            {
                $service = 'apiv_' . $v . '_' . $api_info['api_name'];
                $api_obj = kernel::service($service);

                if( method_exists( $api_obj, $method ) )
                    break;
                else
                    kernel::log('apiv service:' . $service . ', method:' . $method . '  not found!');
            }
        }

        if( !$api_obj || !method_exists( $api_obj, $method ) )
        {
            trigger_error('server reject!', E_USER_ERROR);
        }

        //return call_user_func_array(array( &$api_obj, $method ), $params);
        return $api_obj->$method($params[0], $params[1]);
    }
}
