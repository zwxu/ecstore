<?php


class b2c_order_rpc_pay extends b2c_api_rpc_request implements b2c_api_rpc_request_interface{
    
    public function rpc_caller_request(&$sdf,$method='pay')
    {
        if ($sdf)
            $this->request($sdf);
            
        return true;
    }
    
    /**
     * ¶©µ¥Ö§¸¶
     * @param array sdf
     * @return boolean success or failure
     */
    protected function request(&$sdf)
    {
		$payments_status = array(
            'succ' => 'SUCC',
            'failed' => 'FAILED',
            'cancel' => 'CANCEL',
            'error' => 'ERROR',
            'invalid' => 'INVALID',
            'progress' => 'PROGRESS',
            'timeout' => 'TIMEOUT',
            'ready' => 'READY',
        );
        $arr_data = array();
        $arr_data['tid'] = $sdf['order_id'];
        $arr_data['payment_id'] = $sdf['payment_id'];
        $arr_data['seller_bank'] = $sdf['bank'];
        $arr_data['seller_account'] = $sdf['account'];
        $arr_data['buyer_account'] = $sdf['pay_account'];
        $arr_data['currency'] = $sdf['currency'];
        $arr_data['pay_fee'] = $sdf['money'];
        $arr_data['paycost'] = $sdf['paycost'];
        $arr_data['currency_fee'] = $sdf['cur_money'];
        $arr_data['pay_type'] = ($sdf['pay_app_id'] == 'deposit') ? 'deposit' : $sdf['pay_type'];
        $arr_data['payment_type'] = $sdf['pay_name'];
        $arr_data['payment_tid'] = $sdf['pay_app_id'];
        $arr_data['t_begin'] = date('Y-m-d H:i:s', $sdf['t_begin']);
        $arr_data['t_end'] = date('Y-m-d H:i:s', $sdf['t_payed']);
        $arr_data['status'] = $payments_status[$sdf['status']];
        $arr_data['memo'] = $sdf['memo'];
        $arr_data['outer_no'] = $sdf['trade_no'];        
        
        $arr_callback = array(
            'class' => 'b2c_api_callback_app', 
            'method' => 'callback',
            'params' => array(
                'method' => 'store.trade.payment.add',
                'tid' => $arr_data['tid'],
            ),
        );
        
        parent::request('store.trade.payment.add', $arr_data, $arr_callback, 'Payment Add', 1);
    }
}