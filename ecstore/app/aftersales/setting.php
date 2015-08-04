<?php
 
 
$setting = array(
'site.is_open_return_product'=>array('type'=>SET_T_ENUM,'default'=>0,'desc'=>app::get('aftersales')->_('售后服务状态'),'options'=>array(0=>app::get('aftersales')->_('关闭'),1=>app::get('aftersales')->_('开启'))),//WZP
'site.return_product_comment'=>array('type'=>SET_T_TXT,'default'=>'','desc'=>app::get('aftersales')->_('服务须知')),//WZP
);
