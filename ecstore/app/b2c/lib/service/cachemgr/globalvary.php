<?php


/**
 * 缓存数据键值
 */
class b2c_service_cachemgr_globalvary{
    
    function get_varys(){
        $GLOBALS['runtime']['member_lv'] = $_COOKIE['MLV'];
        $GLOBALS['runtime']['money'] = $_COOKIE['CUR'];
        $aTmp = array(
                        'MLV' => $_COOKIE['MLV'],
                        'CUR' => $_COOKIE['CUR'],
                    );
       return $aTmp;
    }
}
