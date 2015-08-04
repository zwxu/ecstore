<?php



class base_vcode {

    var $use_gd = true;

    function __construct(){
        if($this->use_gd){
            $this->obj = kernel::single('base_vcode_gd');
        }else{
            $this->obj = kernel::single('base_vcode_gif');
        }
        kernel::single('base_session')->start();
    }

    function length($len) {
        $this->obj->length($len);
        return true;
    }

    function verify_key($key){
        $sess_id = kernel::single('base_session')->sess_id();
        $key = $key.$sess_id;
        if(defined('WITHOUT_CACHE') && !constant('WITHOUT_CACHE')){
            cacheobject::set($key,$this->obj->get_code());
        }else{
            base_kvstore::instance('vcode')->store($key,$this->obj->get_code());
        }
    }

    static function verify($key,$value){
        $sess_id = kernel::single('base_session')->sess_id();
        $key = $key.$sess_id;
        if(defined('WITHOUT_CACHE') && !constant('WITHOUT_CACHE')){
            cacheobject::get($key,$vcode);
        }else{
            base_kvstore::instance('vcode')->fetch($key,$vcode);
        }
        if( $vcode == $value ){
            return true;
        }

        return false;
    }

    function display(){
        $this->obj->display();
    }
}
