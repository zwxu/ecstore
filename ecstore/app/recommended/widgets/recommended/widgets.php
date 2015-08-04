<?php

 

$setting['author']      = 'shopex.cn';
$setting['version']     = '1.0';
$setting['name']        = app::get('b2c')->_('APP购买该商品的用户还购买了');
$setting['stime']       = '2010-11-03 14:33:18';
$setting['catalog']     = app::get('b2c')->_('商品挂件');
$setting['description'] = app::get('b2c')->_('推荐购买挂件，用于展示购买过某些商品的用户还同时购买哪些商品，吸引用户进行购买.');
$setting['usual']       = '1';
$setting['template']    = array(
                              'default.html'=>app::get('b2c')->_('竖排'),
                              'line.html'=>app::get('b2c')->_('横排'),
                          );
