<?php



class groupbuy_order_cancel extends b2c_api_rpc_request
{
    /**
     * 私有构造方法，不能直接实例化，只能通过调用getInstance静态方法被构造
     * @params null
     * @return null
     */
    public function __construct($app)
    {
        $app = app::get('b2c');
        parent::__construct($app);
    }

    /**
     * 最终的克隆方法，禁止克隆本类实例，克隆是抛出异常。
     * @params null
     * @return null
     */
    final public function __clone()
    {
        trigger_error(app::get('b2c')->_("此类对象不能被克隆！"), E_USER_ERROR);
    }

    /**
     * 订单取消
     * @params array - 订单数据
     * @params object - 控制器
     * @params string - 支付单生成的记录
     * @return boolean - 成功与否
     */
    public function generate($sdf, &$msg='')
    {
        $is_save = false;
        $is_unfreeze = true;

        $order = app::get('b2c')->model('orders');
        $sdf_order = $order->dump($sdf['order_id'], '*');

        //更新库存
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        $arrStatus = $obj_checkorder->checkOrderFreez('cancel', $sdf['order_id']);

        if ($arrStatus['unfreez'])
        {
            $is_unfreeze = $this->unfreezeGoods($sdf['order_id']);
        }

        //更新活动库存
        switch($sdf_order['order_type']){
            case 'group':
                $applyObj = app::get('groupbuy')->model('groupapply');
                $orderItemObj = app::get('b2c')->model('order_items');
                $apply = $applyObj->dump(array('id'=>$sdf_order['act_id']),'gid,nums,remainnums');
                if($apply['nums'] != ''){
                    $goodsnum = $orderItemObj->dump(array('goods_id'=>$apply['gid']),'(nums-sendnum) as gnum');
                    $sql = "update sdb_groupbuy_groupapply set remainnums=".($goodsnum['gnum']+$apply['remainnums']).' where id='.$sdf_order['act_id'];
                    $applyObj->db->exec($sql);
                    
                    $gsql = "update sdb_b2c_goods set store_freeze=".($goodsnum['gnum']+$apply['remainnums']).' where goods_id='.$apply['gid'];
                    $applyObj->db->exec($gsql);
                }
                break;
        }

        //$obj_api_order = kernel::service("api.b2c.order");
        $sdf_order['status'] = 'dead';
        $is_save = $order->save($sdf_order);
        $this->request($sdf_order['order_id']);

        $obj_order_operations = kernel::servicelist('b2c.order_point_operaction');
        if ($obj_order_operations)
        {
            $arr_data = array(
                'member_id' => $sdf_order['member_id'],
                'score_g' => $sdf_order['score_g'],
                'score_u' => $sdf_order['score_u'],
            );
            foreach ($obj_order_operations as $obj_operation)
            {
                $obj_operation->gen_member_point($arr_data, 'cancel');
            }
        }

        // 更新退款日志结果
        if ($is_save && $is_unfreeze)
        {
            $objorder_log = app::get('b2c')->model('order_log');

            $log_text[] = array(
                    'txt_key'=>'订单取消',
                    'data'=>array(
                    ),
                );
            $log_text = serialize($log_text);

            $sdf_order_log = array(
                'rel_id' => $sdf['order_id'],
                'op_id' => $sdf['op_id'],
                'op_name' => $sdf['opname'],
                'alttime' => time(),
                'bill_type' => 'order',
                'behavior' => 'cancel',
                'result' => 'SUCCESS',
                'log_text' => $log_text,
            );
            $log_id = $objorder_log->save($sdf_order_log);
        }

        $aUpdate['order_id'] = $sdf['order_id'];
        if ($sdf_order['member_id'])
        {
            $member = app::get('b2c')->model('members');
            $arr_member = $member->dump($sdf_order['member_id'], '*', array(':account@pam'=>'*'));
        }
        $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member['contact']['email'];
        $order->fireEvent("cancel", $aUpdate, $sdf_order['member_id']);

        if( $is_save && $is_unfreeze ) {
            foreach( kernel::servicelist("b2c_order_cancel_finish") as $object ) {
                if( !is_object($object) ) continue;
                if( !method_exists($object,'order_notify') ) continue;
                $object->order_notify($sdf_order);
            }
        }

        return ($is_save && $is_unfreeze);
    }

    private function unfreezeGoods($order_id)
    {
        $is_unfreeze = true;
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = app::get('b2c')->model('orders')->dump($order_id, 'order_id,status,pay_status,ship_status', $subsdf);

        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $objGoods = &app::get('b2c')->model('goods');
        foreach($sdf_order['order_objects'] as $k => $v)
        {
            if ($v['obj_type'] != 'goods' && $v['obj_type'] != 'gift')
            {
                foreach( kernel::servicelist('b2c.order_store_extends') as $object ) {
                    if( $object->get_goods_type()!=$v['obj_type'] ) continue;
                    $obj_extends_store = $object;
                    if ($obj_extends_store)
                    {
                        $obj_extends_store->store_change($v, 'cancel');
                    }
                }
                continue;
            }

            foreach ($v['order_items'] as $arrItem)
            {
                if ($arrItem['item_type'] == 'product')
                    $arrItem['item_type'] = 'goods';
                $arr_params = array(
                    'goods_id' => $arrItem['products']['goods_id'],
                    'product_id' => $arrItem['products']['product_id'],
                    'quantity' => $arrItem['quantity'],
                );
                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                $is_unfreeze = $str_service_goods_type_obj->unfreezeGoods($arr_params);
            }
        }

        return $is_unfreeze;
    }

    /**
     * 订单取消事件埋点
     * @param array sdf
     * @return boolean success or failure
     */
    protected function request(&$sdf)
    {
        // 回朔待续...
        $arr_data['tid'] = $sdf;
        $arr_data['status'] = 'TRADE_CLOSED';

        $arr_callback = array(
            'class' => 'b2c_api_callback_app',
            'method' => 'callback',
            'params' => array(
                'method' => 'store.trade.status.update',
                'tid' => $sdf,
            ),
        );

        //$rst = $this->app->matrix()->call('store.trade.status.update', $arr_data);
        parent::request('store.trade.status.update', $arr_data, $arr_callback, 'Order Cancel', 1);

        return true;
    }
}
