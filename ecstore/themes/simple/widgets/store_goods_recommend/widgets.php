<?php

$setting['author']='ccy';
$setting['version']='1.0';
$setting['orderby']='1';
$setting['name'] = app::get('business')->_('宝贝自定义展示挂件');
$setting['catalog'] = app::get('business')->_('宝贝挂件');

$setting['usual'] = '1';
$setting['description'] = app::get('business')->_('宝贝推荐');
$setting['vary'] = '*';
$setting['stime']='2013-5-22';
$setting['template'] = array(
    'default.html'=>app::get('business')->_('默认'),
    'electron.html'=>'电子类模板',
    'electron_2.html'=>'电子类模板2',
    'electron_3.html'=>'电子类模板3',
    'electron_ad.html'=>'电子类模板带广告',
);