<?php
$setting['author']='Jxwinter';
$setting['name']='★新轮播广告★（前台店铺专用）';
$setting['version']='v1.0.0';
$setting['order']=20;
$setting['stime']='2012-04-10';
$setting['catalog']='广告挂件';
$setting['description'] = 'JS版图片轮播效果';
$setting['userinfo']='你可轻松设置图片轮播';
$setting['usual']    = '0';
$setting['vary'] = '*';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                            'default_LR.html'=>'左右箭头式模板',
                            'default_square.html'=>'轮播小方框列表式模板',
                        );
?>
