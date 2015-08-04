<?php
/*
 * Created on 2011-11-23
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$setting['author']='ql';
$setting['version']='v1.0';
$setting['name']='图片广告挂件';
$setting['stime']='2011-12-15';
$setting['catalog']='图片广告挂件模板';
$setting['description']='可以上传图片广告的挂件';
$setting['usual']='1';//是否出现在挂件中心首页.(1\0)
$setting['template']=array(
                        'default.html'=>'一张图片',
                        'index.html'=>app::get('b2c')->_('轮播>首页'),
                        'merchandise.html'=>app::get('b2c')->_('轮播>日用百货'),
                        'clothing.html'=>app::get('b2c')->_('轮播>鞋帽服饰(户外)'),
                        'clothing_packet.html'=>app::get('b2c')->_('多图片轮播>服装鞋包（频道页 ）'),
                        'clothing_shoes.html'=>app::get('b2c')->_('轮播>鞋帽服饰(鞋子)'),
                        'clothing_female.html'=>app::get('b2c')->_('轮播>鞋帽服饰(女装)'),
                        'clothing_male.html'=>app::get('b2c')->_('轮播>鞋帽服饰(男装)'),
                        'clothing_art.html'=>app::get('b2c')->_('轮播>鞋帽服饰晒单'),
                        'merchandise_art.html'=>app::get('b2c')->_('轮播>日用百货晒单'),
                        'mb_newpic.html'=>app::get('b2c')->_('轮播>母婴儿童新书'),
                        'cate_art.html'=>app::get('b2c')->_('轮播>美食特产晒单'),
                        'cate_ab.html'=>app::get('b2c')->_('轮播>美食特产广告')
 );//挂件包含的模板文件和名称
 

