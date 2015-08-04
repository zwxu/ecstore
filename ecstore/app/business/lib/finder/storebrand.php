<?php
class business_finder_storebrand{
   
    function __construct($app){
        $this->app = $app;

        $this ->storemanger =&$this->app->model('storemanger');
        $this ->brand = &app::get('b2c')->model('brand');

    }

   /*
	var $column_control = '操作';
    var $column_control_width = 100;

 	function column_control($row){
		
        return '<a href="index.php?app=business&ctl=admin_storegrade&act=edit&grade_id='.$row['grade_id'].'&finder_id='.$_GET['_finder']['finder_id'].'"  target="blank">'.app::get('business')->_('编辑').'</a>';
    }
    */

    var $column_storename = '店铺名';
    function column_storename($row){

            //店铺名 shop_id
           $storename=$this ->storemanger ->getList('store_name,shop_name',array('shop_id'=>$row['shop_id']));
        
        return $storename['0']['store_name'];
    }

     var $column_storeid = '店铺ID';
    function column_storeid($row){
        return $row['shop_id'];
    }


    var $column_shopname = '店主名';
    function column_shopname($row){

            //店铺名 shop_id
        $storename=$this ->storemanger ->getList('store_name,shop_name',array('shop_id'=>$row['shop_id']));
        
        return $storename['0']['shop_name'];
    }

    var $column_brandname = '品牌名';
    function column_brandname($row){
           //用户名 member_id
      
        $brandname=$this->brand ->getList('brand_name',array('brand_id'=>$row['brand_id']));
        
        return $brandname['0']['brand_name'];
    }

     var $column_memberid = '品牌ID';
    function column_memberid($row){
           //用户名 member_id
        
        return $row['brand_id'];
    }
    
    
	
}