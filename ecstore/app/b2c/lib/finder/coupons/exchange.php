<?php

 
class b2c_finder_coupons_exchange{
    var $column_edit = '操作';
    var $detail_basic = '查看';
    
    public function __construct($app) {
        $this->app = $app;
    }
    
    function column_edit($row){
        $html = '<a href="index.php?app=b2c&ctl=admin_sales_coupon_exchange&act=edit&id='.$row['cpns_id'].'" target="dialog::{title:\'添加兑换规则\',width:460,height:160}">编辑</a>';
        return $html;
    }
    
    
}
