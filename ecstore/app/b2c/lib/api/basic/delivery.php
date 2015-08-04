<?php



/**
 * b2c delivery interactor with center
 */
class b2c_api_basic_delivery
{
    /**
     * app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;

         //店铺校验 
         $data = $_POST ? $_POST: $_GET;
       if($data['method'] &&  trim($data['source_type']) !='system'){
            foreach(kernel::servicelist('business.api_verify_store') as $object)
            {
                 if(is_object($object))
                 {
                     if(method_exists($object,'verifyStore'))
                     {
                        $result = $object->verifyStore(trim($data['store_cert']));
                        if( $result==false){
                            echo app::get('b2c')->_('店铺校验失败！');
                            exit;
                        }
                     }
                 }
            }
       }
    }

    /**
     * 发货单创建
     * @param array sdf
     * @return boolean success or failure
     */
    public function create(&$sdf, $thisObj)
    {
        // 发货单创建是和中心的交互
        $odelivery = $this->app->model('delivery');

        if (!$sdf['delivery_bn'] || !$sdf['order_bn'] || !isset($sdf['delivery_bn']) || !isset($sdf['order_bn']))
        {
            $thisObj->send_user_error(app::get('b2c')->_('发货单tid没有收到！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
        }
        else
        {
            $cnt = $odelivery->count(array('delivery_bn' => $sdf['delivery_bn']));
            if (!$cnt)
            {
                // save the delivery and order items
                $arr_items = json_decode($sdf['items'], 1);
                $order_item = $this->app->model('order_items');
                $o = $this->app->model('delivery_items');
                $objMath = kernel::single('ectools_math');

                // 开始数据库事务
                $db = kernel::database();
                $transaction_status = $db->beginTransaction();

                $obj_products = $this->app->model('products');
                $obj_dlytype = $this->app->model('dlytype');
                $arr_dlytype = $obj_dlytype->dump(array('dt_name' => $sdf['delivery']));
                $obj_dlycorp = $this->app->model('dlycorp');
                $arr_dlycorp = $obj_dlycorp->dump(array('corp_code' => $sdf['logistics_code']));
                $obj_regions = app::get('ectools')->model('regions');
                $arr_regions = $obj_regions->dump(array('local_name' => $sdf['ship_distinct']));
                $order_delivery = $this->app->model('order_delivery');
                $delivery_id = $odelivery->gen_id();
                $arr_data = array(
                    'money' => $sdf['money'] ? $sdf['money'] : 0,
                    'order_id' => $sdf['order_bn'],
                    'is_protect' => ($sdf['is_protect']) ? 'true' : 'false',
                    'delivery' => $arr_dlytype['dt_id'] ? $arr_dlytype['dt_id'] : 0,
                    'delivery_id' => $delivery_id,
                    'delivery_bn' => $sdf['delivery_bn'],
                    'logi_id' => $arr_dlycorp['corp_id'] ? $arr_dlycorp['corp_id'] : 0,
                    'logi_no' => $sdf['logi_no'],
                    'logi_name' => $sdf['logi_name'],
                    'ship_name' => $sdf['ship_name'],
                    'ship_area' => $arr_regions['package'] . ":" . $sdf['ship_states'] . "/" . $sdf['ship_city'] . "/" . $sdf['ship_distinct'] . ":" . $arr_regions['region_id'],
                    'ship_addr' => $sdf['ship_addr'],
                    'ship_zip' => $sdf['ship_zip'],
                    'ship_tel' => $sdf['ship_tel'],
                    'ship_mobile' => $sdf['ship_mobile'],
                    'ship_email' => $sdf['ship_email'],
                    'memo' => $sdf['memo'],
                );

                $arr_data['member_id'] = $sdf['member_id'] ? $sdf['member_id'] : 0;
                $arr_data['t_begin'] = $sdf['t_begin'];
                $arr_data['op_name'] = $sdf['buyer_uname'] ? $sdf['buyer_uname'] : '';
                //$arr_data['type'] = 'delivery';

                $arr_data['status'] = $sdf['status'];

                $is_save = $odelivery->save($arr_data);
    if (!$is_save)
    {
     $db->rollback();
     $thisObj->send_user_error(app::get('b2c')->_('发货单生成失败！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
    }

                $items = array();
                $has_error = false;
                $nonGoods = 0;    //是否完全发货商品标识
                $fail_items = array();

                if ($arr_items)
                {
                    foreach ($arr_items as $arr_item_info)
                    {
                        $arr_item_info = (array)$arr_item_info;
                        $spe_odr_item_filter = array(
                            'order_id'=>$sdf['order_bn'],
                            'bn'=>$arr_item_info['product_bn'],
                            'filter_sql'=>"nums > sendnum",
                        );

                        $arr_order_items = $order_item->getList('*',$spe_odr_item_filter);
                        if (!$arr_order_items)
                        {
       $db->rollback();
                            $thisObj->send_user_error(app::get('b2c')->_('订单相应的明细不存在！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
                        }

                        $need_send = 0;
                        foreach ($arr_order_items as $arr_odr_item)
                        {
                            $need_send = $objMath->number_minus(array($arr_odr_item['nums'], $arr_odr_item['sendnum']));
                            if ($arr_item_info['number'] <= $need_send)
                            {
                                $items = array(
                                    'delivery_id' => $delivery_id,
                                    'order_item_id' => $arr_odr_item['item_id'],
                                    'item_type' => $arr_odr_item['item_type'] == 'product' ? 'goods' : $arr_odr_item['item_type'],
                                    'product_id' => $arr_odr_item['product_id'],
                                    'product_bn' => $arr_item_info['product_bn'],
                                    'product_name' => $arr_item_info['product_name'],
                                    'number' => $arr_item_info['number'],
                                );
                                $arr_item_info['number'] = 0;
                            }
                            else
                            {
                                $items = array(
                                    'delivery_id' => $delivery_id,
                                    'order_item_id' => $arr_odr_item['item_id'],
                                    'item_type' => $arr_odr_item['item_type'] == 'product' ? 'goods' : $arr_odr_item['item_type'],
                                    'product_id' => $arr_odr_item['product_id'],
                                    'product_bn' => $arr_item_info['product_bn'],
                                    'product_name' => $arr_item_info['product_name'],
                                    'number' => $need_send,
                                );
                                $arr_item_info['number'] = $objMath->number_minus(array($arr_item_info['number'], $need_send));
                            }

                            $is_save = $o->save($items);
       if (!$is_save)
       {
        $db->rollback();
        $thisObj->send_user_error(app::get('b2c')->_('发货明细单保存有误！'), $items);
       }
                            if ($arr_item_info['number'] == 0)
                                break;
                        }

                        if ($arr_item_info['number'] > 0)
                        {
                            $has_error = true;
                            $fail_items[] = array(
                                'delivery_id' => $delivery_id,
                                'item_type' => $arr_odr_item['item_type'] == 'product' ? 'goods' : $arr_odr_item['item_type'],
                                'product_bn' => $arr_item_info['product_bn'],
                                'product_name' => $arr_item_info['product_name'],
                                'number' => $arr_item_info['number'],
                            );
                            break;
                        }
                    }

                    $order_delivery_data = array('order_id'=>$sdf['order_bn'],'dly_id'=>$delivery_id,'dlytype'=>'delivery','items'=>($items));
                    if ($order_delivery_data)
                        $order_delivery->save($order_delivery_data);

                    if (!$has_error)
                    {
                        $db->commit($transaction_status);
                        return array('tid'=>$sdf['order_bn'],'delivery_id'=>$sdf['delivery_bn']);
                    }
                    else
                    {
                        $db->rollback();
                        $thisObj->send_user_error(app::get('b2c')->_('发货明细单保存有误！'), $fail_items);
                    }
                }
    else
    {
     $db->rollback();
     $thisObj->send_user_error(app::get('b2c')->_('发货明细没有给出！'), $fail_items);
    }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('发货单已经存在了！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
            }
        }
    }

    /**
     * 发货单修改
     * @param array sdf
     * @return boolean sucess of failure
     */
    public function update(&$sdf, $thisObj)
    {
        // 发货单修改是和中心的交互
error_log(var_export($sdf,1),3,__FILE__.'.2.log');
        $odelivery = $this->app->model('delivery');
        $arr_data = $odelivery->dump(array('delivery_bn' => $sdf['delivery_bn'], 'order_id' => $sdf['order_bn']));

        if (isset($arr_data) && $arr_data)
        {
            /*$obj_dlytype = $this->app->model('dlytype');
            $arr_dlytype = $obj_dlytype->dump(array('dt_name' => $sdf['delivery']));*/
            $obj_dlycorp = $this->app->model('dlycorp');
            $obj_regions = app::get('ectools')->model('regions');
            $arr_regions = $obj_regions->dump(array('local_name' => $sdf['ship_distinct']));
            $order = $this->app->model('orders');
            $order_item = $this->app->model('order_items');
            $objMath = kernel::single('ectools_math');
            $arr_dlycorp = $obj_dlycorp->dump(array('corp_code' => $sdf['logi_code']),'name,corp_id');

            if($arr_dlycorp){
                $arr_data['logi_id'] = $arr_dlycorp['corp_id'];
                $arr_data['logi_name'] = $arr_dlycorp['name'];

            }else{
                $arr_dlycorp['name'] = $arr_data['logi_name'];
            }

            if (isset($arr_regions) && $arr_regions)
            {
                $arr_data['is_protect'] = ($sdf['is_protect']) ? 'true' : 'false';
                $arr_data['ship_name'] = $sdf['ship_name'];
                $arr_data['ship_area'] = $arr_regions['package'] . ":" . $sdf['ship_states'] . "/" . $sdf['ship_city'] . "/" . $sdf['ship_distinct'] . ":" . $arr_regions['region_id'];
                $arr_data['ship_addr'] = $sdf['ship_addr'];
                $arr_data['ship_zip'] = $sdf['ship_zip'];
                $arr_data['ship_tel'] = $sdf['ship_tel'];
                $arr_data['ship_mobile'] = $sdf['ship_mobile'];
                $arr_data['ship_email'] = $sdf['ship_email'];
            }

            /*if ($arr_dlytype)
                $arr_data['delivery'] = $arr_dlytype['dt_id'];
            else
                $arr_data['delivery'] = 0;*/
            if ($sdf['memo'])
                $arr_data['memo'] = $sdf['memo'];
           else
            unset($arr_data['memo']);
            if ($sdf['money'])
                        $arr_data['money'] = $sdf['money'];
           else
            unset($arr_data['money']);
            if ($sdf['logi_no'])
                    $arr_data['logi_no'] = $sdf['logi_no'];
           else
            unset($arr_data['logi_no']);
            if($sdf['logi_name'])
                $arr_data['logi_name'] = $sdf['logi_name'];

            else
                unset($arr_data['logi_name']);


            $status = $arr_data['status'];
            if ($sdf['status'])
            {
                $arr_data['status'] = $sdf['status'];
                /** 发货单增加了打回状态 **/
                if ($arr_data['status'] == 'cancel')
                {
                    $arr_data['status'] = 'cancel';
                    $arr_data['disabled'] = 'true';
                }
            }

            $is_updated = true;
            $db = kernel::database();
            $transaction_status = $db->beginTransaction();

            if ($status != 'succ')
            {
                if ($arr_data['status'] == 'succ')
                {
                    $obj_delivery_item = $this->app->model('delivery_items');
                    $obj_odr_object = $this->app->model('order_objects');
                    $arr_delivery_items = $obj_delivery_item->getList('*',array('delivery_id'=>$arr_data['delivery_id']));
                    if ($arr_delivery_items)
                    {
                        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                        $arrStatus = $obj_checkorder->checkOrderFreez('delivery', $sdf['order_bn']);

                        // 裁剪库存
                        $products = $this->app->model('products');
                        $obj_goods = $this->app->model('goods');
                        $fail_items = array();
                        $has_error = false;
                        $arr_extends_objs = array();

                        foreach ($arr_delivery_items as $arr_item)
                        {
                            //更新发货量
                            $is_update_store = false;
                            $tmp = $order_item->getList('*', array('order_id'=>$sdf['order_bn'], 'product_id'=>$arr_item['product_id'],'item_id'=>$arr_item['order_item_id']));
                            if (!$tmp)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('需要发货的商品不在订单的明细中'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
                            }

                            $update_data['sendnum'] = $objMath->number_plus(array($tmp[0]['sendnum'], $arr_item['number']));
                            $tmp_odr_obj = $obj_odr_object->getList('*',array('obj_id'=>$tmp[0]['obj_id']));
                            if (!$tmp_odr_obj)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('需要发货的商品所在的子订单不存在！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
                            }
                            // 处理其他类型子订单的发货
                            if ($tmp_odr_obj[0]['obj_type'] != 'goods' && $tmp_odr_obj[0]['obj_type'] != 'gift')
                            {
                                if (!$arr_extends_objs[$tmp[0]['obj_id']])
                                    $arr_extends_objs[$tmp[0]['obj_id']] = array(
                                        'obj_type'=>$tmp_odr_obj[0]['obj_type'],
                                    );
                                if ($update_data['sendnum'] == $tmp[0]['nums'])
                                {
                                    $arr_extends_objs[$tmp[0]['obj_id']]['is_finish'] = true;
                                }
                                else
                                {
                                    $arr_extends_objs[$tmp[0]['obj_id']]['is_finish'] = false;
                                }
                            }

                            if ($tmp[0]['nums'] >= $update_data['sendnum'])
                                $is_update_store = true;
                            else
                                $is_update_store = false;

                            $update_data['item_id'] = $tmp[0]['item_id'];
                            if (!$is_update_store) continue;
                            if (!$order_item->save($update_data))
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('订单明细发货数量保存失败！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
                            }

                            $update_data_p = array();
                            $tmp_p = $products->dump($arr_item['product_id'],'*');

                            // 是否要裁剪库存
                            $tmp_g = $obj_goods->getList('*',array('goods_id'=>$tmp_p['goods_id']));
                            if ($tmp_g && $tmp_g[0]['nostore_sell']) continue;

                            $arr_goods = $tmp_g[0];
                            if (is_null($tmp_p['store']) && $tmp_p['store'] !== '' && $tmp_p['store'] == 0)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('需要发货的货品库存不足！'), array(
                                        'delivery_id' => $sdf['delivery_bn'],
                                        'order_item_id' => $tmp[0]['item_id'],
                                        'item_type' => $tmp[0]['item_type'] == 'product' ? 'goods' : 'gift',
                                        'product_id' => $arr_item['product_id'],
                                        'product_bn' => $arr_item['product_bn'],
                                        'product_name' => $arr_item['product_name'],
                                        'number' => $arr_item['number'],
                                    )
                                );
                            }

                            if ($arrStatus['store'])
                                $update_data_p['store'] = $objMath->number_minus(array($tmp_p['store'], $arr_item['number']));
                            if ($arrStatus['unfreez'])
                                $update_data_p['freez'] = $objMath->number_minus(array($tmp_p['freez'], $arr_item['number']));

                            $update_data_p['product_id'] = $tmp_p['product_id'];
                            $is_updated = $products->save($update_data_p);
                            if (!$is_updated)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('货品库存裁剪出错！'), array(
                                        'delivery_id' => $sdf['delivery_bn'],
                                        'order_item_id' => $tmp[0]['item_id'],
                                        'item_type' => $tmp[0]['item_type'] == 'product' ? 'goods' : 'gift',
                                        'product_id' => $arr_item['product_id'],
                                        'product_bn' => $arr_item['product_bn'],
                                        'product_name' => $arr_item['product_name'],
                                        'number' => $arr_item['number'],
                                    )
                                );
                            }

                            if (is_null($arr_goods['store']) && $arr_goods['store'] !== '' && $arr_goods['store'] == 0)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('需要发货的商品品库存不足！'), array(
                                        'delivery_id' => $sdf['delivery_bn'],
                                        'order_item_id' => $tmp[0]['item_id'],
                                        'item_type' => $tmp[0]['item_type'] == 'product' ? 'goods' : 'gift',
                                        'product_id' => $arr_item['product_id'],
                                        'product_bn' => $arr_item['product_bn'],
                                        'product_name' => $arr_item['product_name'],
                                        'number' => $arr_item['number'],
                                    )
                                );
                            }

                            $update_data_g = array(
                                'store' => $objMath->number_minus(array($arr_goods['store'], $arr_item['number'])),
                            );
                            $is_updated = $obj_goods->update($update_data_g, array('goods_id'=>$tmp_p['goods_id']));
                            if (!$is_updated)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('商品库存裁剪出错！'), array(
                                        'delivery_id' => $sdf['delivery_bn'],
                                        'order_item_id' => $tmp[0]['item_id'],
                                        'item_type' => $tmp[0]['item_type'] == 'product' ? 'goods' : 'gift',
                                        'product_id' => $arr_item['product_id'],
                                        'product_bn' => $arr_item['product_bn'],
                                        'product_name' => $arr_item['product_name'],
                                        'number' => $arr_item['number'],
                                    )
                                );
                            }
                        }

                        // 处理其他对象类别的发货处理
                        if ($arr_extends_objs)
                        {
                            $arr_extends_objects = array();
                            foreach( kernel::servicelist('b2c.order_store_extends') as $object ) {
                                if (!$object->get_goods_type()) continue;
                                $arr_extends_objects[$object->get_goods_type()] = array(
                                    'obj_type'=>$object->get_goods_type(),
                                    'obj'=>$object,
                                );
                            }

                            foreach ($arr_extends_objs as $key=>$arr_extends_item)
                            {
                                if ($arr_extends_item['is_finish'] && $arr_extends_objects[$arr_extends_item['obj_type']] && $arr_extends_objects[$arr_extends_item['obj_type']]['obj'])
                                {
                                    $subsdf = array('order_items'=>array('*',array(':products'=>'*')));
                                    $v = $obj_odr_object->dump($key,'*',$subsdf);
                                    $arr_extends_objects[$arr_extends_item['obj_type']]['obj']->store_change($v, 'delivery', 'delivery_finish');
                                }
                            }
                        }
                    }
                    else
                    {
                        $db->rollback();
                        $thisObj->send_user_error(app::get('b2c')->_('发货单明细不存在！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
                    }
                }
            }

            if ($status == 'succ' && $arr_data['status'] != 'cancel')
            {
                unset($arr_data['status']);

            }


            $is_updated = $odelivery->save($arr_data);
            if (!$is_updated)
            {
                $db->rollback();
                $thisObj->send_user_error(app::get('b2c')->_('修改发货单失败！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
            }

   // 更新物流单号日志.
   if ($sdf['logi_no'] && (!isset($sdf['status']) || $sdf['status'] != 'succ'))
   {
    /** 生成订单日志 **/
                $objorder_log = $this->app->model('order_log');
                $tmp = $objorder_log->getList('*',array('rel_id'=>$sdf['order_bn'],'behavior'=>'delivery'));
                $arr_order_log = array();

    if ($tmp)
    {
     if ($tmp[0]['log_text'])
     {
      if (strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')) !== false)
       $log_text = substr($tmp[0]['log_text'], 0, strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')));
      else
       $log_text = $tmp[0]['log_text'] . "，".(($sdf['logi_no']) ? "".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
     }
     else
      $log_text = "".(($sdf['logi_no']) ? "".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");

     $arr_order_log = array(
      'log_text'=>$log_text,
     );
     $objorder_log->update($arr_order_log, array('log_id'=>$tmp[0]['log_id']));
    }
    else
    {
     $log_text = "".(($sdf['logi_no']) ? "".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
     $arr_order_log = array(
      'rel_id' => $sdf['order_bn'],
      'op_id' => '1',
      'op_name' => 'admin',
      'alttime' => time(),
      'bill_type' => 'order',
      'behavior' => 'delivery',
      'result' => 'SUCCESS',
      'log_text' => $log_text,
     );
     $objorder_log->insert($arr_order_log);
    }
   }

            $db->commit($transaction_status);

   /** 更新订单发货状态和修改日志 **/
            if ($sdf['status'] == 'succ')
            {
    $db = kernel::database();
    $transaction_status = $db->beginTransaction();

                // 判断此订单是否完全发货
                $is_part_delivery = false;
                $is_none_delivery = true;
                $o = $this->app->model('order_items');
                $arr_order_items = $o->getList('*', array('order_id' => $sdf['order_bn']));
                if ($arr_order_items)
                {
                    foreach ($arr_order_items as $arr_item)
                    {
                        if ($arr_item['sendnum'] != $arr_item['nums'])
                        {
                            if ($arr_item['sendnum'] > 0)
                                $is_none_delivery = false;

                            $is_part_delivery = true;
                            if ($arr_item['sendnum'] > 0)
                                $arr_delivery_items_send[] = array(
                                    'number' => $arr_item['sendnum'],
                                    'name' => $arr_item['name'],
                                );
                        }
                        else
                        {
       if ($arr_item['sendnum'] > 0)
                                $is_none_delivery = false;

                            $arr_delivery_items_send[] = array(
                                'number' => $arr_item['nums'],
                                'name' => $arr_item['name'],
                            );
                        }
                    }
                }

                if (!$is_none_delivery)
                {
                    if ($is_part_delivery)
                    {
                        $ship_status = '2';
                    }
                    else
                    {
                        $ship_status = '1';
                    }
                }
                else
                {
                    $ship_status = '0';
                }


                $aUpdate = array();
                $aUpdate['order_id'] = $sdf['order_bn'];
                $aUpdate['ship_status'] = $ship_status;

                /** 更新订单发货状态 **/
                $is_updated = $order->save($aUpdate);
                if (!$is_updated)
                {
                    $db->rollback();
                    $thisObj->send_user_error(app::get('b2c')->_('订单发货状态修改失败！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
                }
                /** end **/

                /** 生成订单日志 **/
                $objorder_log = $this->app->model('order_log');
                $tmp = $objorder_log->getList('*',array('rel_id'=>$sdf['order_bn'],'behavior'=>'delivery'));
                $arr_order_log = array();
                // 生成物流日志

                 $Odlycorp = $obj_dlycorp->dump($arr_data['logi_id'],'request_url');

                 $arr_dlycorp['request_url'] = $Odlycorp['request_url'];
                switch ($ship_status)
                {
                    case '0':
                        if ($tmp)
                        {
                            if ($tmp[0]['log_text'])
                            {
                                if (strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')) !== false)
                                    $log_text = substr($tmp[0]['log_text'], 0, strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')));
                                else
                                    $log_text = $tmp[0]['log_text'] . (($sdf['logi_no']) ? ""."，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                            }
                            else
                                $log_text = "".(($sdf['logi_no']) ? "".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");

                            $arr_order_log = array(
                                'log_text'=>$log_text,
                            );
                            $objorder_log->update($arr_order_log, array('log_id'=>$tmp[0]['log_id']));
                        }
                        else
                        {
                            $log_text = "".(($sdf['logi_no']) ? "".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                            $arr_order_log = array(
                                'rel_id' => $sdf['order_bn'],
                                'op_id' => '1',
                                'op_name' => 'admin',
                                'alttime' => time(),
                                'bill_type' => 'order',
                                'behavior' => 'delivery',
                                'result' => 'SUCCESS',
                                'log_text' => $log_text,
                            );
                            $objorder_log->insert($arr_order_log);
                        }
                        break;
                    case '1':
                        if ($tmp)
                        {
                            if ($tmp[0]['log_text'])
                            {
                                if (strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')) === false)
                                    $log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                                else
                                {
                                    $log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . "，" . substr($tmp[0]['log_text'], strrpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')));
                                }
                            }
                            else
                            {
                                $log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                            }

                            $arr_order_log = array(
                                'log_text'=>$log_text,
                            );

                            $objorder_log->update($arr_order_log, array('log_id'=>$tmp[0]['log_id']));
                        }
                        else
                        {
                            $log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                            $arr_order_log = array(
                                'rel_id' => $sdf['order_bn'],
                                'op_id' => '1',
                                'op_name' => 'admin',
                                'alttime' => time(),
                                'bill_type' => 'order',
                                'behavior' => 'delivery',
                                'result' => 'SUCCESS',
                                'log_text' => $log_text,
                            );
                            $objorder_log->insert($arr_order_log);
                        }
                        break;
                    case '2':
                        if ($tmp)
                        {
                            if ($tmp[0]['log_text'])
                            {
                                if (strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')) === false)
                                    $log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                                else
                                {
                                    $log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . "，" . substr($tmp[0]['log_text'], strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')));
                                }
                            }
                            else
                            {
                                $log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                            }

                            $arr_order_log = array(
                                'log_text'=>$log_text,
                            );
                            $objorder_log->update($arr_order_log, array('log_id'=>$tmp[0]['log_id']));
                        }
                        else
                        {
                            $log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_delivery_items), ENT_QUOTES) . ")
    \" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('发货完成').(($arr_dlycorp) ? "，" . "".app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
                            $arr_order_log = array(
                                'rel_id' => $sdf['order_bn'],
                                'op_id' => '1',
                                'op_name' => 'admin',
                                'alttime' => time(),
                                'bill_type' => 'order',
                                'behavior' => 'delivery',
                                'result' => 'SUCCESS',
                                'log_text' => $log_text,
                            );
                            $objorder_log->insert($arr_order_log);
                        }
                        break;
                }
    $db->commit($transaction_status);
            }

            // 监控订单发货
            if (!$sdf['logi_no'] && $sdf['status'] == 'succ')
            {
                $sdf_order = $order->dump($sdf['order_bn'],'*');
                $aUpdate['order_id'] = $sdf['order_bn'];
                $aUpdate['ship_status'] = $ship_status;
                $aUpdate['total_amount'] = $sdf_order['total_amount'];
                $aUpdate['is_tax'] = $sdf_order['is_tax'];
                $aUpdate['member_id'] = $sdf_order['member_id'];
                $aUpdate['delivery'] = $arr_data;
                $aUpdate['ship_billno'] = $arr_data['logi_no'];
                $aUpdate['ship_corp'] = $arr_dlycorp['name'] ? $arr_dlycorp['name'] : '';
                // 配送方式名称
                $obj_dlytype = $this->app->model('dlytype');
                $arr_dlytype = $obj_dlytype->dump($arr_data['delivery'], 'dt_name');
                if ($arr_dlytype)
                    $aUpdate['delivery']['delivery'] = $arr_dlytype['dt_name'];
                else
                    $aUpdate['delivery']['delivery'] = "";
                if ($sdf_order['member_id'])
                {
                    $member = $this->app->model('members');
                    $arr_member = $member->dump($sdf_order['member_id'], '*', array(':account@pam'=>'*'));
                }
                $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member['contact']['email'];

                $order->fireEvent('shipping', $aUpdate, $sdf_order['member_id']);
            }

            return array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']);
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('需要修改的发货单不存在！'), array('delivery_id' => $sdf['delivery_bn'], 'tid' => $sdf['order_bn']));
        }
    }
}