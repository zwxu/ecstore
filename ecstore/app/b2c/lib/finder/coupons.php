<?php

 
class b2c_finder_coupons{
    var $column_control = '操作';
    var $detail_basic = '查看';
    
    public function __construct($app) {
        $this->app = $app;
    }
    
    function column_control($row){
        if( !isset( $row['cpns_type'] ) ) {
            $row = $this->app->model('coupons')->getList( '*',array('cpns_id'=>$row['cpns_id']) );
            reset($row);
            $row = current( $row );
        }
        if ($row['cpns_type'] == 1) { // B类优惠劵
            $src = 'src="index.php?app=b2c&ctl=admin_sales_coupon&act=download&p[0]='.$row['cpns_id'].'"';
            $download_html = '&nbsp;&nbsp;<span class="lnk lnk-no" onclick="var h=prompt(\''.app::get('b2c')->_('请输入需要下载优惠券的数量：').'\',50);
                                                                        if(!h){return};
                                                                        $E(\'iframe[name=download]\').src=\'index.php?app=b2c&ctl=admin_sales_coupon&act=download&p[0]='.$row['cpns_id'].'&p[1]=\'+'.h.';
                                                                        ">'.app::get('b2c')->_('下载').'</span>';
        }
        return '<a href="index.php?app=b2c&ctl=admin_sales_coupon&act=edit&p[0]='.$row['cpns_id'].'" target="_blank">'.app::get('b2c')->_('编辑').'</a>'.$download_html;
    }
    
    
    
    
    
    function detail_basic($id){
        $arr = $this->app->model('coupons')->dump($id); 

        if( !isset($arr['rule']['rule_id']) || !$arr['rule']['rule_id'] ) return ;
        $arr = $this->app->model('sales_rule_order')->dump($arr['rule']['rule_id']); 
        $render = $this->app->render();
        if( !$arr ) exit('数据异常');
   
        
        //会员等级
        if($arr['member_lv_ids']) {
            $member_lv_id = explode(',', $arr['member_lv_ids']);
            $member = $this->app->model('member_lv')->getList('*', array('member_lv_id'=>$member_lv_id) );
            if(count($member_lv_id)>count($member)) {
                // $member[] = array('name'=>app::get('b2c')->_('非会员'));
            }
            $render->pagedata['member'] = $member;
        }
        
        //过滤条件
        if($arr['conditions']) {
            if($arr['c_template']) {
                $render->pagedata['conditions'] = kernel::single($arr['c_template'])->tpl_name;
            }
        }
        
        //优惠方案
        if($arr['action_solution']) {
            if($arr['s_template']) {
            	$o = kernel::single($arr['s_template']);
            	$o->setString($arr['action_solution'][$arr['s_template']]);
                $render->pagedata['action_solution'] = $o->getString();
                #$render->pagedata['action_solution'] = kernel::single($arr['s_template'])->name;
            }
        }
        $render->pagedata['rules'] = $arr;
        return $render->fetch('admin/sales/finder/order.html');
    }
}
