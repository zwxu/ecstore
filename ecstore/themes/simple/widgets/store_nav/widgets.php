<?php

 

$setting['author']='Jxwinter';
$setting['version']='v1.0.0';
$setting['order']=18;
$setting['name']='店铺首页导航';
$setting['catalog'] = '导航挂件';
$setting['description'] = '可自行添加导航标题和连接';
$setting['usual'] = '0';
$setting['stime'] ='2012-04-10';
$setting['userinfo'] = '可自行添加导航标题和连接';
$setting['vary'] = '*';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                            'electron.html'=>'电子类首页模板',
                        );

?>
