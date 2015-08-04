<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_cellphone extends cellphone_base_interface_cellphone{

    public function __construct(){
        parent::__construct();
    }

    function api(){
        $params = $this->params;
        $method = $params['method'];
        $arymethod = explode('.',$method);
        $class= str_replace('.'.end($arymethod),'',$method);
        $service_name = 'openapi.cellphone.'.$class;
        $method = end($arymethod);
        $obj = kernel::service($service_name);
        if( $obj && method_exists($obj,$method) ){
            $obj->$method();
        }else{
            $error['code'] = null;
            $error['msg']  =  $method."方法不可用";
            $this->send(false,null,$error['msg']);
        }
    }


}

