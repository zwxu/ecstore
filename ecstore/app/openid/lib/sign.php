<?php
class openid_sign {

   function get_ce_sign($params,$api_key=''){
        $arg="";
        ksort($params);
        foreach($params as $key=>$value){
            $arg .= $key.$value;
        }
        $sign = md5($arg.$api_key);
        return strtoupper($sign);
    }
}
