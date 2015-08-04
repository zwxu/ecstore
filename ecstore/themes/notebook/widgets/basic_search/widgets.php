<?php

 

$setting['author']='jxwinter';
$setting['name']='商品搜索';
$setting['version']='v1.0.0';
$setting['catalog']='首页挂件';
$setting['usual'] = '0';
$setting['description']= '商品搜索挂件,可方便用户搜索自己想要的商品。';
$setting['stime']='2012-04-10';
$setting['userinfo']='';
$setting['vary'] = '*';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                            'bottom.html'=>app::get('b2c')->_('底部位置'),
                        );
?>
