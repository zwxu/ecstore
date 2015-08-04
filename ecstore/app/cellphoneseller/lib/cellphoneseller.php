<?php

class cellphoneseller_cellphoneseller extends cellphoneseller_base_interface_cellphone{

    public function __construct(){
        parent::__construct();
    }

    function api(){
        $params = $this->params;
        $method = $params['method'];
        if($method == 'services.version.get'){
            $this->send(true,$this->api_version,'版本号');
        }
        if($method != 'user.business.login'){
            $member=$this->get_current_member();
            if($member['member_id'] == ''){
                $this->send(false,-999,'session过期');
            }
        }
        $arymethod = explode('.',$method);
        $class= $arymethod[0];
        unset($arymethod[0]);
        $service_name = 'openapi.cellphoneseller.'.$class;
        $method = implode('_',$arymethod);
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

