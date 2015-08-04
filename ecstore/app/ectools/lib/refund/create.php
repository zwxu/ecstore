<?php

 
/**
 * 退款单添加相关操作
 * @version 0.1
 * @package ectools.lib.refund
 */
class ectools_refund_create
{
    /**
     * 共有构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }
    
    /**
     * 退款单标准数据生成
     * @params array - 订单数据
     * @params string - 唯一标识
     * @return boolean - 成功与否
     */
    public function generate(&$sdf)
    {
        // 退款单创建是和中心的交互
        $obj_refund = $this->app->model('refunds');        
        $payment_cfgs = $this->app->model('payment_cfgs');
        $arrPyMethod = $payment_cfgs->getPaymentInfo($sdf['pay_app_id']);
        
        $sdf['pay_account'] = $account = $sdf['pay_account'] ? $sdf['pay_account'] : $arrPyMethod['app_display_name'];
        $sdf['trade_no'] = $trade_no = substr(uniqid(rand(), true), 0, 30);

        if($sdf['refund_type'] == '1'){
            $refund_type = 'refunds';
        }else{
            $refund_type = 'blances';
        }
        
        $arr_data = array(
            'refund_id' => $sdf['refund_id'],
            'member_id' => $sdf['member_id'],
            'account' => $sdf['account'] ? $sdf['account'] : '',
            'bank' => $sdf['bank'],
            'pay_account' => $account,
            'currency' => $sdf['currency'],
            'money' => $sdf['money'],
            'paycost' => $sdf['paycost'],
            'cur_money' => $sdf['cur_money'],
            'pay_type' => $sdf['pay_type'],
            'pay_app_id' => $sdf['payment'],
            'pay_name' => $sdf['app_name'] ? $sdf['app_name'] : $sdf['payment'],
            'pay_ver' => $sdf['app_version'],
            'op_id' => $sdf['op_id'],
            't_begin' => $sdf['t_begin'],
            't_payed' => $sdf['t_payed'],
            't_confirm' => $sdf['t_confirm'],
            'status' => 'ready',
            'memo' => '',
            'trade_no' => $trade_no,
            'profit' => $sdf['profit'],
            'refund_type' => $sdf['refund_type'],
            'is_safeguard' => $sdf['is_safeguard'],
            'score_cost' => $sdf['score_cost'],
            'orders' => array(
                    array(
                        'rel_id' => $sdf['order_id'],
                        'bill_type' => $refund_type,
                        'pay_object' => 'order',
                        'bill_id' => $sdf['refund_id'],
                        'money' => $sdf['money'],
                    )
                )
        );
        $is_save = $obj_refund->save($arr_data);
        if (!$is_save)
        {
            return false;
        }
        
        return true;
    }
}