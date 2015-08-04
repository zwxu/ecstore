<?php

 
$setting['author']='dreamdream';
$setting['name']= '所有赠品列表';
$setting['version']='v1.0.0';
$setting['stime']='2008-08-08';
$setting['catalog']=app::get('gift')->_('商品挂件');
$setting['usual'] = '0';
$setting['description'] = '本版块(widget)的作用是显示所有赠品列表。可以控制赠品显示的数量。';
$setting['userinfo'] ='可以控制赠品显示的数量。';
$setting['template'] = array(
                            'default.html'=>app::get('gift')->_('默认')
                        );
?>