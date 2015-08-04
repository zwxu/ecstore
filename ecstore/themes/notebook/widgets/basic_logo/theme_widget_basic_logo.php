<?php

 
function theme_widget_basic_logo($setting,&$smarty){
    $logo_id = app::get('b2c')->getConf('site.logo');
    $result['logo_image'] = base_storager::image_path($logo_id);
    return $result;
}
?>
