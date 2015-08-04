<?php

 
$setting['author']='jxwinter';
$setting['name']='商家热卖';
$setting['version']='v1.0.0';
$setting['vary'] = '*';
$setting['catalog']='商品列表页挂件';
$setting['usual']    = '1';
$setting['description'] = '本版块是在商品列表页底部显示商家热卖商品，添加本版块到模板页面上的相应插槽里即可。';
$setting['userinfo'] ='*'; 
$setting['stime']='2012-04-12';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
?>
