<?php

 
function theme_widget_basic_member($setting,&$smarty){
    #return; //todo 修正widget
    if(app::get('site')->getConf('account.type')){
        $member_id = $_SESSION['account'][app::get('site')->getConf('account.type')];
    }else {
        $member_id = $_SESSION['account']['member'];

    }

    if(!$member_id){
        $member_id = $_COOKIE['S']['MEMBER'];
    }
   
    $member = app::get('b2c')->model('members');
    $member_data = $member->dump($member_id,'*',array(':account@pam'=>array('login_name')));

    $member_data['valideCode'] = app::get('b2c')->getConf('site.login_valide');
         if(app::get('openid')->is_actived())
        {    
            $member_data['open_id_open'] = 'true';
            $member_data['res_url'] = app::get('openid')->res_url;
        }
        else
        {
            $member_data['open_id_open'] = 'false';
        }

        $member_data['seller'] = $_COOKIE['SELLER'];

    return $member_data;
}
function instance_loginplug($data){
    //var_dump($data);
    //if(!class_exists('app')) require('app.php');
    $path = APP_DIR.'/'.$data['app_id'].'/passport.'.$data['app_id'].'.php';
    //echo $path;
    if(file_exists($path)){
        require_once($path);
        $classname = 'passport_'.$data['plugin_ident'];
        $object = new $classname;
        return $object;
    }else{
        return false;
    }
}
?>
