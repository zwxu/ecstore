<?php

$setting['author']='wb';
$setting['name']=app::get('b2c')->_('宝贝分类');
$setting['version']='v1.0.1';
$setting['stime']='2012-3-24';
$setting['catalog']='详情页挂件';
$setting['description']= app::get('b2c')->_('商品详情页左边挂件宝贝分类');
$setting['order']='1';
$setting['userinfo']='店铺左侧';
$setting['usual']    = '1';
$setting['vary'] = '*';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认'),
                        );
?>