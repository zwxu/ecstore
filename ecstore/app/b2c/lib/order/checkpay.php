<?php

 
class b2c_order_checkpay{
    
    function check_goods(&$sdf, &$message)
    {
        //商品库存验证
    
        //==============check_order_pay===============*/
                //商品库存不足
        
        return true;
    }
    function check_order_status($sdf,&$message){
        $order = app::get('b2c')->model('orders');
        $sdf_order = $order->dump($sdf['order_id'],'*');
        if($sdf_order['status'] != 'active' 
            || $sdf_order['pay_status'] == '1' 
            || $sdf_order['pay_status'] == '2' 
            || $sdf_order['pay_status'] == '4' 
            || $sdf_order['pay_status'] == '5'){
            $message = app::get('b2c')->_('订单状态锁定');
            return false;
            exit;
        }
        return true;
    }
    function check_order(&$sdf, &$message)
    {
        $order = app::get('b2c')->model('orders');
        $sdf_order = $order->dump($sdf['order_id'],'*');

        $nonPay = $sdf_order['total_amount'] - $sdf_order['payed'];
        if(isset($sdf['money'])){//如果输入了收款金额
            if(floatval($sdf['money'])>$nonPay || floatval($sdf['money']) <= 0){
                $message = app::get('b2c')->_('支付失败：支付总金额不在订单金额范围');
                return false;
            }
            $payMoney = floatval($sdf['money']);
        }else{//如果没有输入付款金额
            $payMoney = $nonPay;                           
        }

        switch($sdf['pay_type']){
            case 'deposit':
                $oAdvance = &app::get('b2c')->model("members");
                $row = $oAdvance->dump($sdf_order['member_id'],'*');
                if(empty($row)){
                    $message = app::get('b2c')->_('查询预存款帐户失败');
                    return false;
                }else{
                    if($payMoney>$rows['advance']){
                        $message = app::get('b2c')->_('预存款帐户余额不足');
                        return false;
                    }
                }
                $sdf['pay_type'] = 'deposit';
            break;
            case 'offline':
                $sdf['pay_type'] = 'offline';
            break;
            default:
                $sdf['pay_type'] = 'online';
            break;
        }
    
        
        return true;
    }
 
}
