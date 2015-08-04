<?php

 

$setting['author']='hxh';
$setting['version']='v1.0.0';
$setting['order']=18;
$setting['name']='热卖推荐';
$setting['catalog'] = '商品列表页挂件';
$setting['description'] = '自定义在列表页显示热卖商品，并可以设置显示商品数';
$setting['usual'] = '0';
$setting['stime'] ='2012-04-10';
$setting['userinfo'] = '*';
$setting['vary'] = '*';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

?>
