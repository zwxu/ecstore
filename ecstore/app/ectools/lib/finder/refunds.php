<?php

 
/**
 * 退款finder下拉的操作列
 * 
 * @version 0.1
 * @package ectools.lib.finder
 */
class ectools_finder_refunds{
    
	/**
	 * 退款单明细数据说明
	 */
    var $detail_info = '退款单明细';
    
    /**
     * 构造方法
     * @param object 当前app对象
     * @return null
     */
    public function __construct($app){
        $this->app=$app;
    }
    
    /**
     * 退款单明细数据的实现
     * @return string 退款单序号
     * @return string 展示结果数据
     */
    public function detail_info($refund_id){
        
        $refund= $this->app->model('refunds');
        $sdf_refund = $refund->dump($refund_id, '*', array('orders' => '*'));
        if($sdf_refund){
            $render = $this->app->render();
            
            $render->pagedata['refunds'] = $sdf_refund;
            if (isset($render->pagedata['refunds']['member_id']) && $render->pagedata['refunds']['member_id'])
            {
                $obj_pam = app::get('pam')->model('account');
                $arr_pam = $obj_pam->dump(array('account_id' => $render->pagedata['refunds']['member_id'], 'account_type' => 'member'), 'login_name');
                $render->pagedata['refunds']['member_id'] = $arr_pam['login_name'];
            }
            if (isset($render->pagedata['refunds']['op_id']) && $render->pagedata['refunds']['op_id'])
            {
                $obj_pam = app::get('pam')->model('account');
                $arr_pam = $obj_pam->dump(array('account_id' => $render->pagedata['refunds']['op_id']), 'login_name');
                $render->pagedata['refunds']['op_id'] = $arr_pam['login_name'];
            }
            if (isset($render->pagedata['refunds']['orders']) && $render->pagedata['refunds']['orders'])
			{
				foreach ($render->pagedata['refunds']['orders'] as $key=>$arr_order_bills)
				{
					$render->pagedata['refunds']['order_id'] = $key;
				}
			}
			
            return $render->fetch('refund/refund.html',$this->app->app_id);
            /*$ui= new base_component_ui($this);
            $html .= $ui->form_start();
            foreach($sdf_refund as $k=>$val){
                $v['value'] = $val;
                $v['name'] = $k;
                $v['type'] = 'label';
                $v['title'] = $refund->schema['columns'][$k]['label'];
                $html .= $ui->form_input($v);
            }
            
            $html .= $ui->form_end(0);
            return $html;*/
        }else{
            return app::get('ectools')->_('无内容');
        }
    }
    
    /**
     * @var 退款对象列的说明
     */
    public $column_rel_id = '订单号';
    /**
     * 退款对象列的修改实现
     * @param array 特定行数据
     * @return string 修改后的值
     */
    public function column_rel_id($row)
    {
        $obj_refund = $this->app->model('refunds');
        
        $arr_refund = $obj_refund->dump($row['refund_id'], '*', array('orders' => '*'));
        if ($arr_refund)
		{
			if ($arr_refund['orders'])
				$order_bill = array_shift($arr_refund['orders']);
			else
				$order_bill = array('rel_id'=>0);
		}
        
        return $order_bill['rel_id'];
    }
}
