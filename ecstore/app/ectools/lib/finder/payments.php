<?php

 
/**
 * 支付单finder下拉的操作列
 * 
 * @version 0.1
 * @package ectools.lib.finder
 */
class ectools_finder_payments{
	
	/**
	 * @var 下拉详细数据展示
	 */
    public $detail_payments = '参数设置';
    /**
     * 构造方法
     * @param object 本app对象
     * @return null
     */
    public function __construct($app){
        $this->app = $app;
    }
	
    /**
     * 下拉参数数据的展示的实现
     * @param string payment id支付单序号
     * @return string 详情内容
     */
    public function detail_payments($payment_id)
    {
        $payment = $this->app->model('payments');
        if($_POST['payment_id']){
            $sdf = $_POST;
            unset($_POST['_method']);
            if($payment->save($sdf)){
                echo 'ok';
            }
        }else{
            $sdf_payment = $payment->dump($payment_id, '*', array('orders' => '*'));
            if($sdf_payment)
            {
                $render = $this->app->render();
                
                $render->pagedata['payments'] = $sdf_payment;
                if (isset($render->pagedata['payments']['op_id']) && $render->pagedata['payments']['op_id'])
                {
                    $obj_pam = app::get('pam')->model('account');
                    $arr_pam = $obj_pam->dump(array('account_id' => $render->pagedata['payments']['op_id']), 'login_name');
                    $render->pagedata['payments']['op_id'] = $arr_pam['login_name'] ? $arr_pam['login_name'] : '-';
                }
				else
				{
					$render->pagedata['payments']['op_id'] = '-';
				}
                if (isset($render->pagedata['payments']['orders']) && $render->pagedata['payments']['orders'])
                {
                    foreach ($render->pagedata['payments']['orders'] as $key=>$arr_order_bills)
                    {
                        $render->pagedata['payments']['order_id'] = $key;
                    }
                }
                return $render->fetch('payments/payments.html',$this->app->app_id);
            }else{
                return app::get('ectools')->_('无内容');
            }
        }
    }
	
    /**
     * @var 支付对象的列的修改说明
     */
	public $column_order_id = '支付对象';
	/**
	 * 支付对象列的修改的实现
	 * @param array 特定行的数据
	 * @return string 修改后的内容
	 */
	public function column_order_id($row)
	{
		$obj_payment = $this->app->model('payments');
		
		$arr_payment = $obj_payment->dump($row['payment_id'], '*', array('orders' => '*'));
		if ($arr_payment)
			$order_bill = array_shift($arr_payment['orders']);
		
		return $order_bill['rel_id'];
	}
}
