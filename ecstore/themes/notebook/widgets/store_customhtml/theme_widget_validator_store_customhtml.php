<?php
function theme_widget_validator_store_customhtml(&$data,$action) {
    
    $customhtml=$data['params']['usercustom'];
    error_log($customhtml,3,'d:/them.txt');
    $url=kernel::single('business_url');
    $valite=$url->is_valid_html($customhtml);
    $img_url=kernel::single('business_img_url');
    $img_valite=$url->is_valid_html($customhtml);
    $style=kernel::single('business_theme_widget_style');
    $customhtml=$style->prefix($customhtml,substr(md5($customhtml),0,6));//css过滤
    
    //error_log($customhtml,3,'d:/them.txt');
    return ($valite&&img_valite)? true :'{error:"图片或者超链接存在非站内地址"}';
    //return '{error:"'.$customhtml.'"}';
}