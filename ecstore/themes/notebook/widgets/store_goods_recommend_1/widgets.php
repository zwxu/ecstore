<?php

$setting['author']='yzb';
$setting['version']='1.0';
$setting['orderby']='1';
$setting['name'] = app::get('business')->_('店长推荐挂件');
$setting['catalog'] = app::get('business')->_('详情页挂件');

$setting['usual'] = '1';
$setting['description'] = app::get('business')->_('店长推荐');
$setting['vary'] = '*';
$setting['stime']='2013-5-22';
$setting['template'] = array(
    'default.html'=>app::get('business')->_('默认'),
);