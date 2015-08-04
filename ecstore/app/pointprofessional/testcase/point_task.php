<?php

 
class order extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->model = app::get('b2c')->model('orders');
        $this->obj_payment = app::get('ectools')->model('payments');
        $this->obj_refund = app::get('ectools')->model('refunds');
        $this->obj_delivery = app::get('b2c')->model('delivery');
        $this->obj_reship = app::get('b2c')->model('reship');
    }

    public function testInsert(){

        $orderArr = array(
                'member_id'=>'1',
                'currency'=>'CNY',
                'cur_rate'=>'0.2',
                'title'=>'订单明细介绍',
                'createtime'=>'1234567891',
                'last_modified'=>'1234577891',
                'confirm'=>'N',   //Y/N
                'status'=>'active',     //active/dead/cancel/finish
                'pay_status'=>'0',      //0/1/2/3/4/5
                'ship_status'=>'0',     //0/1/2/3/4
                'is_delivery'=>'Y',     //Y/N
                'weight'=>189.00,
                'itemnum'=>2,
                'ip'=>'127.0.0.1',
                'is_tax'=>'false',
                'tax_title'=>'开票title',
                'cost_tax'=>0,
                'cost_item'=>220,
                'discount'=>2,
                'pmt_goods'=>10,
                'pmt_order'=>20,
                'total_amount'=>'190',
                'cur_amount'=>'190',
                'payed'=>'190',
                'score_u'=>'0',
                'score_g'=>'190',
                'memo'=>'订单留言',
                'shipping'=>array(
                    'shipping_id'=>'1',
                    'shipping_name'=>'配送方式名称',
                    'cost_shipping'=>10,
                    'is_protect'=>'false',
                    'cost_protect'=>0,
                ),
                'consignee'=>array(
                    'name'=>'收货人姓名',
                    'addr'=>'收货地址',
                    'zip'=>'200030',
                    'telephone'=>'021-56868589',
                    'mobile'=>'13978945612',
                    'email'=>'ever@shopex.cn',
                    'area'=>'mainland:上海/上海市/徐汇区:25',   //或者array('id'=>25, 'value'=>'上海/上海市/徐汇区');
                    'r_time'=>'要求时间',
                    'meta'=>array()
                    ),
                'payinfo'=>array(
                    'pay_app_id'=>'alipay',
                    'pay_name'=>'支付方式名称',
                    'cost_payment'=>2,
                ),
                'order_objects'=>array(
                    array(
                        'obj_type'=> 'goods',  //goods,gift,taobao, api...
                        'obj_alias'=> '商品区块',  //goods,gift,taobao, api...
                        'goods_id'=>1,
                        'bn'=>'对象编号',
                        'name'=>'对象名称',
                        'price'=>58.50,
                        'quantity'=>1,
                        'amount'=>58.50,
                        'weight'=>58.50,
                        'score'=>58,
                        'order_items'=>array(
                            array(
                                'product_id'=>1,
                                'goods_id'=>1,
                                'item_type'=>'product',
                                'bn'=>'对象编号',
                                'name'=>'对象名称',
                                'type_id'=>1,
                                'cost'=>5,
                                'quantity'=>5,
                                'sendnum'=>0,
                                'amount'=>58.50,
                                'price'=>58.50,
                                'weight'=>58.50,
                                'addon'=>58.50,
                                'score'=>58,
                            ),
                            array(
                                'product_id'=>1,
                                'goods_id'=>1,
                                'bn'=>'对象编号',
                                'name'=>'对象名称',
                                'type_id'=>1,
                                'cost'=>5,
                                'quantity'=>5,
                                'sendnum'=>0,
                                'price'=>58.50,
                                'amount'=>58.50,
                                'weight'=>58.50,
                                'sendnum'=>58.50,
                                'addon'=>58.50,
                                'item_type'=>'gift',
                                'score'=>58,
                            ),
                            array(
                                'product_id'=>1,
                                'goods_id'=>1,
                                'bn'=>'对象编号',
                                'name'=>'对象名称',
                                'type_id'=>1,
                                'cost'=>5,
                                'quantity'=>5,
                                'sendnum'=>0,
                                'price'=>58.50,
                                'amount'=>58.50,
                                'weight'=>58.50,
                                'sendnum'=>58.50,
                                'addon'=>58.50,
                                'is_type'=>'adjunct',
                                'score'=>58,
                            )
                        ),
                    ),
                ),
                'meta'=>array()
            );
        $orderArr['order_id'] = $this->model->gen_id();

        $payment['order_id'] = $orderArr['order_id'];
        $payment['payment_id'] = $this->obj_payment->gen_id();
        $payment['member_id'] = '1';
        $payment['account'] = '收款帐户1';
        $payment['bank'] = '支付宝';
        $payment['pay_account'] = '付款帐号';
        $payment['currency'] = 'CNY';
        $payment['money'] = '100';
        $payment['paycost'] = '1';
        $payment['cur_money'] = '50';
        $payment['pay_type'] = 'online';
        $payment['pay_app_id'] = 'alipay';
        $payment['pay_name'] = '支付宝';
        $payment['pay_ver'] = '1.0';
        $payment['op_id'] = '1';
        $payment['ip'] = '127.0.0.1';
        $payment['t_begin'] = '1234567899';
        $payment['t_payed'] = '1234567899';
        $payment['t_confirm'] = '1234567899';
        $payment['status'] = 'ready';
        $payment['trade_no'] = '支付宝交易号:78912';
        $payment['memo'] = '说明';
        $payment['orders'] = array(
                array(
                    'order_id' => $payment['order_id'],
                    'money' => 100,
                )
        );
        
        $orderArr['payments'][] = $payment;
        $orderArr['refunds'][] = $payment;
        unset($orderArr['refunds'][0]['payment_id']);
        $orderArr['refunds'][0]['refund_id'] = $this->obj_refund->gen_id();

        $delivery['order_id'] = $orderArr['order_id'];
        $delivery['delivery_id'] = $this->obj_delivery->gen_id();
        $delivery['member_id'] = '1';
        $delivery['is_protect'] = 'false';
        $delivery['ship_name'] = '收货人';
        $delivery['ship_area'] = '收货地区';
        $delivery['ship_addr'] = '收货地址';
        $delivery['ship_zip'] = '收货邮编';
        $delivery['ship_tel'] = '收货人电话';
        $delivery['ship_mobile'] = '收货人手机';
        $delivery['ship_email'] = '收货人Email';
        $delivery['money'] = '100';
        $delivery['logi_id'] = '1';
        $delivery['logi_name'] = 'online';
        $delivery['delivery'] = '支付宝';
        $delivery['op_name'] = '发货人';
        $delivery['t_begin'] = '1234567899';
        $delivery['t_send'] = '1234567899';
        $delivery['t_confirm'] = '1234567899';
        $delivery['status'] = 'ready';
        $delivery['logi_no'] = '配送单号:78912';
        $delivery['memo'] = '说明';
        $delivery['delivery_items'] = array(
                array(
                    'delivery_id' => $delivery['delivery_id'],
                    'product_id' => '1',
                    'product_bn' => 'EWWETRT',
                    'product_name' => '商品名称1',
                    'number' => '1',
                ),
                array(
                    'delivery_id' => $delivery['delivery_id'],
                    'product_id' => '2',
                    'product_bn' => 'FGHJKIMLK',
                    'product_name' => '商品名称2',
                    'number' => '1',
                ),
        );
        
        //items 不能读取item_id，只能放delivery_items
        $delivery['orders'] = array(
                array(
                    'order_id' => $delivery['order_id'],
                    'items' => array(
                                    array('item_id' => '1','number' => '1'),
                                    array('item_id' => '2','number' => '1'))
                )
        );

        $orderArr['delivery'][] = $delivery;
        $orderArr['reship'][] = $delivery;
        unset($orderArr['reship'][0]['delivery_id']);
        $orderArr['reship'][0]['reship_id'] = $this->obj_reship->gen_id();
        $orderArr['reship'][0]['reship_items'] = $orderArr['reship'][0]['delivery_items'];
        unset($orderArr['reship'][0]['delivery_items']);
        $this->model->save($orderArr);
        $row = $this->model->db->selectrow('select * from sdb_b2c_orders where order_id='.$orderArr['order_id']);
        $this->assertEquals($row['order_id'],$orderArr['order_id']);
    }
    
    public function atestPay()
    {
        
        
        $payment['order_id'] = '20100113199221';
        $payment['member_id'] = '1';
        $payment['bill_type'] = 'pay';
        $payment['account'] = '收款帐户1';
        $payment['bank'] = '支付宝';
        $payment['pay_account'] = '付款帐号';
        $payment['currency'] = 'CNY';
        $payment['money'] = '100';
        $payment['paycost'] = '1';
        $payment['cur_money'] = '50';
        $payment['pay_type'] = 'online';
        $payment['pay_key'] = 'alipay';
        $payment['pay_name'] = '支付宝';
        $payment['pay_ver'] = '1.0';
        $payment['op_id'] = '1';
        $payment['ip'] = '127.0.0.1';
        $payment['t_begin'] = '1234567899';
        $payment['t_end'] = '1234567899';
        $payment['status'] = 'ready';
        $payment['trade_no'] = '支付宝交易号:78912';
        $payment['memo'] = '说明';
        $payment['pay_status'] = '1';
        
        $orderArr['payments'][] = $payment;
        $orderArr['payments'][] = $payment;
        $orderArr['payments'][] = $payment;
        
        $this->model->save($orderArr);
        //$row = $this->model->db->selectrow('select * from sdb_b2c_orders where order_id='.$orderArr['order_id']);
        //$this->assertEquals($row['pay_status'],$orderArr['pay_status']);
    }
    
    public function atestConsign()
    {
        $orderArr['order_id'] = '20100113199229';
        $orderArr['ship_status'] = '1';
        $this->model->pay($orderArr);
        $row = $this->model->db->selectrow('select * from sdb_b2c_orders where order_id='.$orderArr['order_id']);
        $this->assertEquals($row['ship_status'],$orderArr['ship_status']);
    }
    
}
