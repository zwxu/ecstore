<?php
function theme_widget_gallery_goodscat(&$setting, &$system) {
    foreach($setting['cat'] as $k=>&$v){
        foreach($setting['top_link_title'] as $k1=>$v1){
            if($v['cat_id'] == $k1){
                $v['top_link_title'] = $setting['top_link_title'][$k1];
            }
        }
    }

    return $setting;

}

?>
