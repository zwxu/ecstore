<?php

 
$setting['author']='jxwinter';
$setting['name']='入驻商家轮播展示';
$setting['version']='v1.0.0';
$setting['vary'] = '*';
$setting['catalog']='招商首页挂件';
$setting['usual']    = '1';
$setting['description'] = '本版块（widget）是展示已入驻商家。没有特殊参数需要设置，添加本版块到模板页面上的相应插槽里即可。';
$setting['userinfo'] ='*'; 
$setting['stime']='2013-11-14';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
?>
