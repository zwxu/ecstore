<?php

 

$setting['author']='ql';
$setting['version']='v1.0.0';
$setting['order']=18;
$setting['name']='视频或者flash';
$setting['catalog'] = '广告挂件';
$setting['description'] = '展示一个flash或者视频';
$setting['usual'] = '0';
$setting['stime'] ='2013-11-4';
$setting['userinfo'] = '视频地址可使用上传图片，也可使用网络视频，更可使用%THEME%/flash/***.swf写法调用模板内部视频。';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

?>
