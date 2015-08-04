<?php

$setting['author']='ql';
$setting['name']='会员注册/登录';
$setting['version']='1.0.0';
$setting['stime']='2013-11-18';
$setting['catalog']='首页挂件';
$setting['usual'] = '0';
$setting['vary'] = '*';
$setting['description']='本挂件无需参数设置，添加本挂件到模板页面对应插槽上即可使用。';
$setting['userinfo']='您只需配置欢迎词即可。';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
?>
