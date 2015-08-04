<?php

$setting['author']='ccy';
$setting['version']='1.0';
$setting['orderby']='1';
$setting['name'] = app::get('business')->_('宝贝搜索');
$setting['catalog'] = app::get('business')->_('搜索挂件');

$setting['usual'] = '1';
$setting['description'] = app::get('business')->_('宝贝搜索挂件，如果挂在类表页则选择默认，如果挂在店铺首页请使用“横向模板”');

$setting['stime']='2013-5-22';
$setting['vary'] = '*';
$setting['template'] = array(
    'default.html'=>app::get('b2c')->_('默认(左侧模板)'),
    'search_2.html'=>app::get('b2c')->_('横向模板'),
);

?>
