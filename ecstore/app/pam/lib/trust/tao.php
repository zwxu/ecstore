<?php

 
class pam_trust_tao {

    public function login($params=null){
        if(!$params) return false;
        foreach(kernel::servicelist('api_login') as $k=>$passport){
            return $passport->login($params);
        }
    }

}
