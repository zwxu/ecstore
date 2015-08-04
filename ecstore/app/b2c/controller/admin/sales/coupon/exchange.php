<?php
 
 
class b2c_ctl_admin_sales_coupon_exchange extends desktop_controller{

   
    function index() {
        $this->finder('b2c_mdl_coupons', array(
                'title'=>app::get('b2c')->_('积分兑换优惠券'),
                'use_buildin_recycle' => false,
                'use_buildin_setcol' => false,
                'use_buildin_refresh' => false,
                'use_buildin_tagedit' => false,
                'actions'=>array(
                                array('label'=>app::get('b2c')->_('添加兑换规则'),'href'=>'index.php?app=b2c&ctl=admin_sales_coupon_exchange&act=add','target'=>'dialog::{title:\'添加兑换规则\',width:460,height:160}'),
                                array('label'=>app::get('b2c')->_('删除'), 'submit'=>'index.php?app=b2c&ctl=admin_sales_coupon_exchange&act=delete'),
                            ),'finder_cols'=>'column_edit,cpns_name,cpns_point','finder_aliasname'=>'exchange',
                'object_method'=>array('getlist'=>'getlist_exchange','count'=>'getlist_exchange_count'),
                ));
    }
    

    public function add() {
        $filter = array('cpns_status' => '1', 'cpns_type' => '1');
        $cpns = $this->app->model('coupons')->getList('*', $filter);
        foreach( $cpns as $key => $row ) {
            if( $row['cpns_point'] ) unset($cpns[$key]);
        }
        $this->pagedata['cpns'] = $cpns;
        if(count($this->pagedata['cpns'])<1) $this->pagedata['nodata'] = true;
        $this->page('admin/sales/coupon/exchange/add.html');
        
    }
    
    public function edit() {
        $filter['cpns_id'] = $_GET['id'];
        $cpns = $this->app->model('coupons')->getList('*', $filter);
        reset( $cpns );
        $cpns = current( $cpns );
        $this->pagedata['cpns'] = $cpns;
        $this->page('admin/sales/coupon/exchange/add.html');
    }
    
    
    public function save() {
        $this->begin('index.php?app=b2c&ctl=admin_sales_coupon_exchange&act=index');
        $arr['cpns_point'] = $_POST['cpns_point'];
        $arr['cpns_id'] = $_POST['cpns_id'];
        $flag = false;
        if($arr['cpns_id']) {
            $o = $this->app->model('coupons');
            if($o->dump($arr['cpns_id'])) {
                $flag = $o->save($arr);
            }
        } else {
            $this->end(false,  app::get('b2c')->_('请选择优惠券！'));
        }
        
        $show_title = '添加';
        if( $_POST['edit'] == 'true' ) {
            $show_title = '修改';
        }
        $this->end($flag, ($flag ? app::get('b2c')->_($show_title.'成功！') : app::get('b2c')->_($show_title.'失败！')));
    }
    
    public function delete() {
        $this->begin('index.php?app=b2c&ctl=admin_sales_coupon_exchange&act=index');
        foreach($_POST['cpns_id'] as $cpns_id) {
            $arr['cpns_point'] = null;
            $arr['cpns_id'] = $cpns_id;
            $this->app->model('coupons')->save($arr);
        }
        $this->end('操作成功！');
    }
    
    

}
