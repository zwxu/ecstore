<?php

 

$setting['author']='ql';
$setting['version']='v1.0.0';
$setting['order']=18;
$setting['name']='我的订单';
$setting['catalog']='系统基础挂件';
$setting['description'] = '罗列待付款，待收货，待评论订单数';
$setting['usual'] = '0';
$setting['vary'] = '*';
$setting['stime'] ='2013-05-06';
$setting['userinfo'] = '我的订单挂件，可显示待付款，待收货，待评论的订单数';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

?>
