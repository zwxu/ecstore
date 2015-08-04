<?php

$setting['author']='ccy';
$setting['version']='1.0';
$setting['orderby']='1';
$setting['name'] = app::get('business')->_('宝贝分类');
$setting['catalog'] = app::get('business')->_('左侧挂件');

$setting['usual'] = '1';
$setting['description'] = app::get('business')->_('宝贝分类');

$setting['vary'] = '*';
$setting['stime']='2013-5-22';
$setting['template'] = array(
    'default.html'=>app::get('b2c')->_('默认'),
);