<?php
class business_finder_storemember{
   
    function __construct($app){
        $this->app = $app;

        $this ->storemanger =&$this->app->model('storemanger');
        $this ->member = &app::get('b2c')->model('members');
        $this ->account = &app::get('pam')->model('account');
        $this ->storeroles =&$this->app->model('storeroles');

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

            //店铺名 store_id
           $storename=$this ->storemanger ->getList('store_name,shop_name',array('store_id'=>$row['store_id']));
        
        return $storename['0']['store_name'];
    }

     var $column_storeid = '店铺ID';
    function column_storeid($row){
        return $row['store_id'];
    }


    var $column_shopname = '店主名';
    function column_shopname($row){

            //店铺名 store_id
        $storename=$this ->storemanger ->getList('store_name,shop_name',array('store_id'=>$row['store_id']));
        
        return $storename['0']['shop_name'];
    }

    var $column_membername = '店员名';
    function column_membername($row){
           //用户名 member_id
      
        $membername=$this->account->getList('login_name',array('account_id'=>$row['member_id']));
        return $membername['0']['login_name'];
    }

     var $column_memberid = '店员ID';
    function column_memberid($row){
           //用户名 member_id
        
        return $row['member_id'];
    }

     var $column_roles = '角色';
    function column_roles($row){

           //角色名 
        $membername = $this ->storeroles ->getList('role_name',array('role_id'=>$row['roles_id']));

        return $membername['0']['role_name'];
        
    }
    
    
	
}