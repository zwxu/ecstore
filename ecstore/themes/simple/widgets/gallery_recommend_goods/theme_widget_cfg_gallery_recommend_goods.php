<?php

 
function theme_widget_cfg_gallery_recommend_goods($app){
	$modTag = app::get('desktop')->model('tag');
    $return['tags'] = $modTag->getList('*',array('tag_type'=>'goods'),0,-1);
    
    return $return;
}