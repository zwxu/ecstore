<?php

class complain_seller_menuextends {
    public function __construct($app) {
        $this->app = $app;
    } 

    public function get_extends_menu(&$arr_menus, $args = array()) {

       foreach($arr_menus as $key=>&$group){
          if($group['label']=='客服中心'){
               array_unshift($arr_menus[$key]['items'],array('label' => app::get('b2c')->_('举报管理'), 'app' => 'complain', 'ctl' => 'site_seller_reports', 'link' => 'reports_main'));
          
               array_unshift($arr_menus[$key]['items'],array('label' => app::get('b2c')->_('投诉管理'), 'app' => 'complain', 'ctl' => 'site_seller_complain', 'link' => 'main'));
          }
       }
       return true;
    } 
} 
