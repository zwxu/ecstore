<?php

function theme_widget_store_customhtml(&$setting,&$system){  
   //过滤掉javascript脚本。
  $usercustom=$setting['usercustom'];
  $usercustom=preg_replace("/<script[^>]*>([\s\S]*?)<\/script>/i","",$usercustom);
  $url=kernel::single('business_url');
  $usercustom=$url->replace_html($usercustom);//非本地地址过滤
  $img_url=kernel::single('business_img_url');
  $usercustom=$img_url->replace_html($usercustom);//图片地址限制
  $style=kernel::single('business_theme_widget_style');
  $usercustom=$style->prefix($usercustom,substr(md5($usercustom),0,6));//css过滤
 //error_log($usercustom,3,'d:/them1.txt');
 $setting['usercustom']=$usercustom;
  return $setting;
}