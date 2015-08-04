<?php

class complain_buyer_menuextends {
    public function get_extends_menu(&$arr_menus, $args = array()) {
        $arr_menus[]=array('label'=>app::get('b2c')->_('维权管理'),
            'mid'=>0,
            'items'=>array(
                array('label'=>app::get('b2c')->_('投诉管理'),'app'=>'complain','ctl'=>'site_buyer_complain','link'=>'main'),
                array('label'=>app::get('b2c')->_('举报管理'),'app'=>'complain','ctl'=>'site_buyer_reports','link'=>'reports_main')
            )
        );
    } 
} 
