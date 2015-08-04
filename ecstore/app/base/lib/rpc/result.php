<?php

 
class base_rpc_result{

    function __construct($response,$app_id){
        $sign = $response['sign'];
        unset($response['sign']);
        $this->response = $response;
		if (!$app_id || !base_shopnode::token($app_id))
			$sign_check = base_certificate::gen_sign($response);
		else
			$sign_check = base_shopnode::gen_sign($response,$app_id);
        if($sign != $sign_check){
            //trigger_error('sign error!',E_USER_ERROR);
            echo json_encode(array(
                    'rsp' => 'fail',
                    'res' => 4003,
                    'data' => 'sign error'
                ));
            exit;
        }
    }

    function set_callback_params($params){
        $this->callback_params = $params;
    }

    function get_callback_params(){
        return $this->callback_params;
    }

    function get_pid(){
        return $this->response['msg_id'];
    }

    function get_status(){
        return $this->response['rsp'];
    }

    function get_data(){
        return json_decode($this->response['data'],1);
    }

    function get_result(){
        return $this->response['res'];
    }

}
