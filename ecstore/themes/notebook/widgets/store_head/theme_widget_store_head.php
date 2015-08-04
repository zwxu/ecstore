<?php
function theme_widget_store_head(&$setting,&$smarty) {    
    $setting['store']=$smarty->pagedata['store'];
    //会员登录
    $setting['isLogin']=isset($smarty->pagedata['member_info']['member_id']);
    //客服
    $setting['im_webcall']=$smarty->pagedata['im_webcall'];
    return $setting;
}