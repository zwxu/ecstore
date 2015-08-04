<?php
class openid_render_href{

    function pre_display(&$html){
        $str = 'index.php?app=desktop&ctl=pam&act=setting&finder_id=a8d83c&p[0]=openid_passport_trust';
        $url = 'http://www.ecopen.cn';
        $res = app::get('pam')->getConf('passport.openid_passport_trust');
        if(strpos($html,$str) && !strpos($html,$url) && $res['site_passport_status']['value'] === 'true'){
            $html .= '| <a href='.$url.' target="_blank">信任登陆管理</a>';
        }
        return ;
    }

}
