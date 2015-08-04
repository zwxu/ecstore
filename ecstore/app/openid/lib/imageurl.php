<?php
class openid_imageurl{

    function get_image_url(){
        $res = app::get('pam')->getConf('passport.openid_passport_trust');
        $appid = app::get('openid')->getConf('appid');
        if($res['site_passport_status']['value'] === 'true') {
            return "<script type='text/javascript' charset='utf-8' src = 'http://www.ecopen.cn/connect/logincode?appid=".$appid."&v=1.0' ></script>";
        }else{
            return false;
        }
    }

}
?>
