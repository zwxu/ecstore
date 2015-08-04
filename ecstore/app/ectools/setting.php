<?php

 
$setting = array(
'site.decimal_digit.count'=>array('type'=>SET_T_ENUM,'default'=>2,'desc'=>app::get('ectools')->_('金额运算精度保留位数'),'options'=>array(0=>app::get('ectools')->_('整数取整'),1=>app::get('ectools')->_('取整到1位小数'),2=>app::get('ectools')->_('取整到2位小数'),3=>app::get('ectools')->_('取整到3位小数'))),//WZP
'site.decimal_type.count'=>array('type'=>SET_T_ENUM,'default'=>1,'desc'=>app::get('ectools')->_('金额运算精度取整方式'),'options'=>array('1'=>app::get('ectools')->_('四舍五入'),'2'=>app::get('ectools')->_('向上取整'),'3'=>app::get('ectools')->_('向下取整'))),//WZP
'site.decimal_digit.display'=>array('type'=>SET_T_ENUM,'default'=>2,'desc'=>app::get('ectools')->_('金额显示保留位数'),'options'=>array(0=>app::get('ectools')->_('整数取整'),1=>app::get('ectools')->_('取整到1位小数'),2=>app::get('ectools')->_('取整到2位小数'),3=>app::get('ectools')->_('取整到3位小数'))),//WZP
'site.decimal_type.display'=>array('type'=>SET_T_ENUM,'default'=>1,'desc'=>app::get('ectools')->_('金额显示取整方式'),'options'=>array('1'=>app::get('ectools')->_('四舍五入'),'2'=>app::get('ectools')->_('向上取整'),'3'=>app::get('ectools')->_('向下取整'))),
'system.area_depth'=>array('type'=>SET_T_INT,'default'=>'3','desc'=>app::get('ectools')->_('地区级数')),
'site.paycenter.pay_succ'=>array('type'=>SET_T_TXT,'default'=>'<a href="'.kernel::base_url(1).'/index.php" type="url" title="返回首页">返回首页</a><br/>（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）','desc'=>app::get('ectools')->_('支付成功提示自定义信息')),
'site.paycenter.pay_failure'=>array('type'=>SET_T_TXT,'default'=>'<a href="'.kernel::base_url(1).'/index.php" type="url" title="返回首页">返回首页</a><br/>
（此为默认内容，具体内容可以在后台“页面管理-提示信息管理”中修改）','desc'=>app::get('ectools')->_('支付失败提示自定义信息')),
);
