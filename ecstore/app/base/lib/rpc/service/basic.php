<?php

 
class base_rpc_service_basic{

    function ping($params, &$service){
        return func_get_args();
    }

    function time(){
        trigger_error('asdfasfsf',E_USER_ERROR);
        return date(DATE_RFC822);
    }

}
