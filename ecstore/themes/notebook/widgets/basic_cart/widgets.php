<?php

 

$setting['author']='ql';
$setting['name']='购物车';
$setting['version']='v1.0.0';
$setting['catalog']='首页挂件';
$setting['description']= '拉动本版块（widget）就可以在网店前台显示购物车，本版块不需要任何参数的设置，添加本版块到模板页面相应的插槽上即可。';
$setting['usual'] = '0';
$setting['userinfo']='购物车挂件，可显示商品数量';
$setting['stime']='2012-04-10';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                        );
$setting['cart_show_type'] = 1;
$setting['cart_show_total'] = '2';