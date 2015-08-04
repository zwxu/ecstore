<?php



/**
 * b2c order interactor with center
 */
class b2c_api_ocs_1_0_order implements b2c_api_interface_order
{
    /**
     * app object
     */
    public $app;

    /**
     * ectools_math object
     */
    public $objMath;

    /**
     * 订单状态转换值
     */
    private $arr_status = array(
        'TRADE_ACTIVE'=>'active',
        'TRADE_CLOSED'=>'dead',
        'TRADE_FINISHED'=>'finish',
    );

    /**
     * 订单支付状态转换值
     */
    private $arr_pay_status = array(
        'PAY_NO'=>'0',
        'PAY_FINISH'=>'1',
        'PAY_TO_MEDIUM'=>'2',
        'PAY_PART'=>'3',
        'REFUND_PART'=>'5',
        'REFUND_ALL'=>'4',
    );

    /**
     * 订单发货状态转化
     */
    private $arr_ship_status = array(
        'SHIP_NO'=>'0',
        'SHIP_PREPARE'=>'0',
        'SHIP_PART'=>'2',
        'SHIP_FINISH'=>'1',
        'RESHIP_PART'=>'3',
        'RESHIP_ALL'=>'4',

    );

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->objMath = kernel::single('ectools_math');

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
                        if($result==false){
                            echo app::get('b2c')->_('店铺校验失败！');
                            exit;
                        }else {

                            //取得Store_id
                            $arycert=app::get('business')->model('storemanger')->getList('store_id',array('store_cert'=>trim($data['store_cert'])));

                            if($arycert){
                              $this->store_id=$arycert[0]['store_id'];
                            }

                        }
                     }
                 }
            }
         }

    }

    /**
     * 订单创建
     * @param array sdf
     * @param string member indent
     * @param string message
     * @return boolean success or failure
     */
    public function create(&$sdf, &$thisObj)
    {
        // 创建订单是和中心的交互
        $order = &$this->app->model('orders');

        //增加店铺
        if($this->store_id){
           $sdf['store_id'] = $this->store_id;
        }


        $result = $order->save($sdf);//todo order_items表product_id字段未插入

        if (!$result)
        {
            trigger_error(app::get('b2c')->_('订单生成失败！'), E_USER_ERROR);
        }
        else
        {
            return true;
        }
    }

    /**
     * 订单修改
     * @param array sdf
     * @return boolean sucess of failure
     */
    public function update(&$sdf, &$thisObj)
    {
        // 修改订单是和中心的交互

        if (!isset($sdf['order_bn']) || !$sdf['order_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('需要更新的库存不存在！'), array('tid'=>''));
        }
        else
        {
            $objOrder = $this->app->model('orders');
            $arr_order = $objOrder->dump($sdf['order_bn']);

            if ($arr_order)
            {
                //增加店铺 
                if($this->store_id){
                  if($this->store_id != $arr_order['store_id']){
                    $thisObj->send_user_error(app::get('b2c')->_('此订单ID不属于本店铺。'), array('tid' => $sdf['order_bn']));
                  }
                }


                $arr_data_receive = json_decode($sdf['consignee'], true);

                if (!$arr_data_receive)
                {
                    $thisObj->send_user_error(app::get('b2c')->_('订单收货地址为空！'), array('tid'=>$sdf['order_bn']));
                }
                else
                {
                    $obj_regions = app::get('ectools')->model('regions');
                    $arr_regions = $obj_regions->dump(array('local_name' => $arr_data_receive['distinct']));

                    $arr_data['order_id'] = $sdf['order_bn'];
                    if (isset($sdf['last_modified']) && $sdf['last_modified'])
                        $arr_data['last_modified'] = $sdf['last_modified'];
                    if (isset($sdf['is_tax']) && $sdf['is_tax'])
                    {
                        $arr_data['is_tax'] = $sdf['is_tax'];
                        $arr_data['tax_title'] = $sdf['tax_title'];
                        $arr_data['cost_tax'] = $sdf['cost_tax'];
                    }
                    if (isset($sdf['cost_item']) && $sdf['cost_item'])
                        $arr_data['cost_item'] = $sdf['cost_item'];
                    if (isset($sdf['total_amount']) && $sdf['total_amount'])
                        $arr_data['total_amount'] = $sdf['total_amount'];
                    if (isset($sdf['discount']) && $sdf['discount'])
                        $arr_data['discount'] = $sdf['discount'];
                    //if (isset($sdf['payed']) && $sdf['payed'])
                    //    $arr_data['payed'] = $sdf['payed'];
                    if (isset($sdf['currency']) && $sdf['currency'])
                        $arr_data['currency'] = $sdf['currency'];
                    if (isset($sdf['cur_rate']) && $sdf['cur_rate'])
                        $arr_data['cur_rate'] = $sdf['cur_rate'];
                    if (isset($sdf['cur_amount']) && $sdf['cur_amount'])
                        $arr_data['cur_amount'] = $sdf['cur_amount'];
                    if (isset($sdf['score_u']) && $sdf['score_u'])
                        $arr_data['score_u'] = $sdf['score_u'];
                    if (isset($sdf['score_g']) && $sdf['score_g'])
                        $arr_data['score_g'] = $sdf['score_g'];
                    if (isset($sdf['shipping']) && $sdf['shipping'])
                    {
                        $arr_data['shipping'] = json_decode($sdf['shipping'], true);
                    }
                    if (isset($sdf['payinfo']) && $sdf['payinfo'])
                    {
                        $arr_data['payinfo'] =json_decode($sdf['payinfo'], true);
                    }
                    if ($arr_regions)
                        $arr_data['consignee'] = array(
                            'name' => $arr_data_receive['name'],
                            'addr' => $arr_data_receive['addr'],
                            'zip' => $arr_data_receive['zip'],
                            'telephone' => $arr_data_receive['telephone'],
                            'mobile' => $arr_data_receive['mobile'],
                            'email' => $arr_data_receive['email'],
                            'area' => $arr_regions['package'] . ":" . $arr_data_receive['states'] . "/" . $arr_data_receive['city'] . "/" . $arr_data_receive['distinct'] . ":" . $arr_regions['region_id'],
                        );
                    else
                        $arr_data['consignee'] = array(
                            'name' => $arr_data_receive['name'],
                            'addr' => $arr_data_receive['addr'],
                            'zip' => $arr_data_receive['zip'],
                            'telephone' => $arr_data_receive['telephone'],
                            'mobile' => $arr_data_receive['mobile'],
                            'email' => $arr_data_receive['email'],
                            'area' => "",
                        );

                    $result = $objOrder->save($arr_data);//订单基本信息更改

                    if (!$result)
                    {
                        $thisObj->send_user_error(app::get('b2c')->_('订单基本信息修改失败！'), array());
                    }

                    // 记录订单日志
                    $objorder_log = $this->app->model('order_log');
                    $log_text = app::get('b2c')->_("订单收货人信息修改！");
                    $sdf_order_log = array(
                        'rel_id' => $sdf['order_bn'],
                        'op_id' => '1',
                        'op_name' => 'admin',
                        'alttime' => time(),
                        'bill_type' => 'order',
                        'behavior' => 'updates',
                        'result' => 'SUCCESS',
                        'log_text' => $log_text,
                    );
                    $log_id = $objorder_log->save($sdf_order_log);

                    return array('tid'=>$sdf['order_bn']);
                }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
            }
        }
    }

    /**
     * 修改订单明细 - order items
     * @param array sdf
     * @return boolean sucess of failure
     */
    public function update_items(&$sdf, &$thisObj)
    {

        if (!isset($sdf['order_bn']) && !$sdf['order_bn'])
        {
            return $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
        else
        {
            $objOrder = $this->app->model('orders');
            $obj_order_item = $this->app->model('order_items');
            $obj_order_object = $this->app->model('order_objects');
            $tmp = $objOrder->getList('*',array('order_id'=>$sdf['order_bn']));
            if (!$tmp)
            {
                return $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
            }
            else
            {
                $db = kernel::database();
                $transaction_status = $db->beginTransaction();

                $sdf_order = $tmp[0];
            if ($sdf_order['status'] == 'dead' || $sdf_order['status'] == 'finish')
            {
             return $thisObj->send_user_error(app::get('b2c')->_('订单已经作废或完成，不能再编辑！'), array('tid'=>$sdf['order_bn']));
            }
                $obj_regions = app::get('ectools')->model('regions');
                $arr_regions = $obj_regions->dump(array('local_name' => $sdf['receiver_district']));
                $sdf_order = array(
                    'last_modified'=>$sdf['modified'],
                    'is_tax'=>$sdf['has_invoice'],
                    'tax_company'=>$sdf['invoice_title'],
                    'cost_tax'=>$sdf['invoice_fee'],
                    'cost_item'=>$sdf['total_goods_fee'],
                    'total_amount'=>$sdf['total_trade_fee'],
                    'discount'=>$sdf['discount_fee'],
                    'payed'=>$sdf['payed_fee'],
                    'currency'=>$sdf['currency'],
                    'cur_rate'=>$sdf['currency_rate'],
                    'final_amount'=>$sdf['total_currency_fee'],
                    'score_g'=>$sdf['buyer_obtain_point_fee'],
                    'score_u'=>$sdf['point_fee'],
                    'weight'=>$sdf['total_weight'],
                    'ship_time'=>$sdf['receiver_time'],
                    'shipping'=>$sdf['shipping_type'],
                    'cost_freight'=>$sdf['shipping_fee'],
                    'is_protect'=>$sdf['is_protect'],
                    'cost_protect'=>$sdf['protect_fee'],
                    'ship_name'=>$sdf['receiver_name'],
                    'ship_email'=>$sdf['receiver_email'],
                    'ship_mobile'=>$sdf['receiver_mobile'],
                    //'ship_area'=>$arr_regions['package'] . ":" . $arr_data_receive['states'] . "/" . $arr_data_receive['city'] . "/" . $arr_data_receive['distinct'] . ":" . $arr_regions['region_id'],
                    'ship_addr'=>$sdf['receiver_address'],
                    'ship_zip'=>$sdf['receiver_zip'],
                    'ship_tel'=>$sdf['receiver_phone'],
                    'cost_payment'=>$sdf['commission_fee'],
                    'memo'=>$sdf['trade_memo'],
                );

            if ($this->arr_status[$sdf['status']])
             $sdf_order['status'] = $this->arr_status[$sdf['status']];
            if ($this->arr_pay_status[$sdf['pay_status']])
                            $sdf_order['pay_status'] = $this->arr_pay_status[$sdf['pay_status']];
            if ($this->arr_ship_status[$sdf['ship_status']])
                            $sdf_order['ship_status'] = $this->arr_ship_status[$sdf['ship_status']];

            // 判断下目前订单所处的状态
            if ($sdf_order['payed'] >= $sdf_order['final_amount'])
            {
             // 全额支付
             $sdf_order['pay_status'] = '1';
            }
            else
            {
                 $obj_order_bills = app::get('ectools')->model('order_bills');
                 $tmp = $obj_order_bills->count(array('rel_id'=>$sdf['order_bn'], 'bill_type'=>'refunds'));
                 if ($tmp > 0)
                 {
                      if ($sdf_order['payed'] == 0)
                       $sdf_order['pay_status'] = '5';
                      else
                       $sdf_order['pay_status'] = '4';
                 }
                 else
                 {
                      if ($sdf_order['payed'] == 0)
                       $sdf_order['pay_status'] = 0;
                      else
                       $sdf_order['pay_status'] = '3';
                 }
            }

                if ($objOrder->update($sdf_order, array('order_id'=>$sdf['order_bn'])))
                {
                    $is_save = true;
                    $arr_order_object = json_decode($sdf['orders'], 1);

                    if ($arr_order_object['order'])
                    {
                        $obj_spec_values = $this->app->model('spec_values');
                        $obj_products = $this->app->model('products');
                        $obj_goods = $this->app->model('goods');
                        $obj_specification = $this->app->model('specification');
                        $arr_exception_style = array('pkg');
echo '<pre>';
//print_r($arr_order_object['order'] );
                        foreach ($arr_order_object['order'] as $arr_obj)
                        {

                            if ($arr_obj['order_items'])
                            {
                                $obj_bn = '';
                                $obj_id = 0;
                                $sdf_arr_item_bns = '';
                                $arr_item_bns = '';
                                $goods_id = 0;
                                $goods_price = 0;
                                $sdf_arr_items = array();
                                $sdf_item_total_score = 0;
                                $obj_bn = $arr_obj['orders_bn'];

                                /** 找到相应的obj_id **/
            if (!$arr_obj['iid'])
            {

             foreach ($arr_obj['order_items']['item'] as $arr_item)
             {

              // 目前不认识的类型
              if (in_array($arr_item['item_type'], $arr_exception_style))
              {
               $db->rollback();
               $thisObj->send_user_error(app::get('b2c')->_('编辑的订单中含有不认识的商品类型！'), array('tid'=>$sdf['order_bn']));
              }

              $sdf_arr_item_bns[] = $arr_item['bn'];
              $sdf_arr_items[] = $arr_item;
             }

             asort($sdf_arr_item_bns);
             $row = $obj_order_object->getList('*', array('order_id'=>$sdf['order_bn'],'bn'=>$obj_bn));
             if (count($row) > 1)
             {
              foreach ($row as $arr_objs)
              {
               $row_item = $obj_order_item->getList('*', array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_objs['obj_id']));
               if ($row_item)
               {
                foreach ($row_item as $arr_item)
                {
                 $arr_item_bns[$arr_objs['obj_id']][] = $arr_item['bn'];
                }
                asort($arr_item_bns[$arr_objs['obj_id']]);
               }
              }
              if ($arr_item_bns)
              {
               foreach ($arr_item_bns as $key=>$arr_item_bn)
               {
                if ($sdf_arr_item_bns == $arr_item_bn)
                {
                 $arr_obj['iid'] = $key;
                }
               }
              }
             }
             elseif (count($row) > 0)
             {
              //$arr_obj['iid'] = $row[0]['obj_id'];
              $row_item = $obj_order_item->getList('*', array('order_id'=>$sdf['order_bn'],'obj_id'=>$row[0]['obj_id']));
              if ($row_item)
              {
               foreach ($row_item as $arr_item)
               {
                $arr_item_bns[$row[0]['obj_id']][] = $arr_item['bn'];
               }
               asort($arr_item_bns);
              }
              if ($arr_item_bns)
              {
               foreach ($arr_item_bns as $key=>$arr_item_bn)
               {
                if ($sdf_arr_item_bns == $arr_item_bn)
                {
                 $arr_obj['iid'] = $key;
                }
               }
              }
             }
            }
            /** end **/

        if (!$arr_obj['iid'])
        {
         $is_all_normal = false;
         $is_has_product = false;
         $is_product_true = false;
         foreach ($arr_obj['order_items']['item'] as $arr_item)
         {
          if ($arr_item['item_type'] == 'product')
          {
           $is_has_product = true;
           if ($arr_item['item_status'] == 'normal')
            $is_product_true = true;
          }
          else
          {
           if ($arr_item['item_status'] == 'normal')
            $is_all_normal = true;
          }
         }
         if ($is_has_product && !$is_product_true)
         {
          $is_all_normal = false;
         }
         else
         {
          $is_all_normal = true;
         }

         $order_items = array();
        $obj_price= 0;
         foreach ($arr_obj['order_items']['item'] as $arr_item)
         {
          // 目前不认识的类型
          if (in_array($arr_item['item_type'], $arr_exception_style))
          {
           $db->rollback();
           $thisObj->send_user_error(app::get('b2c')->_('编辑的订单中含有不认识的商品类型！'), array('tid'=>$sdf['order_bn']));
          }

          // 此区块为新增的区块
          switch ($arr_obj['type'])
          {
           case 'goods':
            $obj_alias = app::get('b2c')->_('商品区块');
            break;
           case 'gift':
            $obj_alias = app::get('b2c')->_('赠品区块');
            break;
           default:
            $obj_alias = app::get('b2c')->_('捆绑销售');
            break;
          }

          $tmp = $obj_products->getList('goods_id,price', array('bn'=>$obj_bn));
          if ($tmp)
          {
           $goods_id = $tmp[0]['goods_id'];
           $goods_price = $tmp[0]['price'];
          }
          else
          {
           $goods_id = 0;
           $goods_price = 0;
          }
          $strAddon = '';
          $arrAddon = array();
          $tmp = $obj_products->getList('goods_id,product_id,spec_desc', array('bn'=>$arr_item['bn']));

          $tmp_goods = $obj_goods->getList('type_id', array('goods_id'=>$tmp[0]['goods_id']));
          if ($tmp[0]['spec_desc'])
          {
           $tmp[0]['spec_desc'] = unserialize($tmp[0]['spec_desc']);
           if (isset($tmp[0]['spec_desc']) && $tmp[0]['spec_desc'] && is_array($tmp[0]['spec_desc']))
           {
            foreach ($tmp[0]['spec_desc'] as $spec_key=>$str_spec_value_id)
            {
             $arr_spec_value = $obj_spec_values->dump($str_spec_value_id);
             $arr_specification = $obj_specification->dump($arr_spec_value['spec_id']);
             $arrAddon['product_attr'][$spec_key] = array(
              'label' => $arr_specification['spec_name'],
              'value' => $arr_spec_value['spec_value'],
             );
            }

            $strAddon = serialize($arrAddon);
           }
          }
          if ($arr_item['item_status'] == 'normal')
                $price = $arr_item['sale_price']/$arr_item['num'];
                if($arr_item['item_type']!='adjunct')
                {
                    $obj_price+= $price;
                 }
           $order_items[] = array(
            'products'=>array('product_id'=>$tmp[0]['product_id']),
            'goods_id'=>$tmp[0]['product_id']['goods_id'],
            'order_id' => $sdf['order_bn'],
            'item_type'=>$arr_item['item_type'],
            'bn'=>$arr_item['bn'],
            'name'=>$arr_item['name'],
            'type_id'=>$tmp_goods[0]['type_id'],
            'g_price'=>$arr_item['price'],
            'quantity'=>$arr_item['num'],
            'sendnum'=>$arr_item['sendnum'],
            'amount'=>$arr_item['sale_price'],
            'score' => $arr_item['score'],
            'price'=>$price,
            'weight'=>$arr_item['weight'],
            'addon'=>$strAddon,
           );

         }
         $sdf_order_object = array(
          'order_id' => $sdf['order_bn'],
          'obj_type' => $arr_obj['type'],
          'obj_alias' => $obj_alias,
          'goods_id' => $goods_id,
          'bn' => $obj_bn,
          'name' => $arr_obj['title'],
          'price' => $obj_price,
          'quantity'=> $arr_obj['items_num'],
          'amount'=> $arr_obj['total_order_fee'],
          'weight'=> $arr_obj['weight'],
          'score'=> $sdf_item_total_score,
          'order_items' => $order_items,
         );

         if ($is_all_normal)
          if (!$obj_order_object->save($sdf_order_object))
           $is_save = false;
        }
        else
        {
         // 区块不是新增

                                    $is_all_item_cancel = true;
                                    $obj_price = 0;
                                    foreach ($arr_obj['order_items']['item'] as $arr_item)
                                    {
                                        if ($arr_item['item_status'] == 'normal')
                                        {
                                            $price = $arr_item['sale_price']/$arr_item['num'];
                                            if($arr_item['item_type']!='adjunct')
                                            {
                                                $obj_price+= $price;
                                             }
                                            $is_all_item_cancel = false;
                                            $sdf_order_item = array(
                                                'g_price'=>$arr_item['price'],
                                                'nums'=>$arr_item['num'],
                                                'sendnum'=>$arr_item['sendnum'],
                                                'amount'=>$arr_item['sale_price'],
                                                'score' => $arr_item['score'],
                                                'price'=>$price,
                                                'weight'=>$arr_item['weight'],
                                            );
                                            $item_exist = $obj_order_item->dump(array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid'],'bn'=>$arr_item['bn']),'*');
                                            if($item_exist){
                                            $is_save = $obj_order_item->update($sdf_order_item, array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid'],'bn'=>$arr_item['bn']));
                                            }else{
                                               $sdf_order_item['order_id'] = $sdf['order_bn'];
                                               $sdf_order_item['obj_id'] = $arr_obj['iid'];
                                                $sdf_order_item['bn'] = $arr_item['bn'];
                                                $sdf_order_item['name'] = $arr_item['name'];
                                                $is_save = $obj_order_item->insert($sdf_order_item);
                                            }

                                        }
                                        else
                                        {
                                            $is_save = $obj_order_item->delete(array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid'],'bn'=>$arr_item['bn']));
                                        }
                                    }
                                    if ($is_all_item_cancel)
                                    {
                                        $is_save = $obj_order_object->delete(array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid']));
                                    }
        }
                                /** end **/
                            }
                        }

                        if ($is_save)
                        {
       $db->commit($transaction_status);
      }
                        else
                        {
                            $db->rollback();
                            $thisObj->send_user_error(app::get('b2c')->_('订单修改失败！'), array('tid'=>$sdf['order_bn']));
                        }

      $db = kernel::database();
      $transaction_status = $db->beginTransaction();

      $arr_order_items = $obj_order_item->getList('nums,sendnum', array('order_id'=>$sdf['order_bn']));
      $order_ship_status = '1';
      $is_finish_ship = false;
      $is_part_ship = false;
      if ($arr_order_items)
      {
       foreach ($arr_order_items as $arr_item)
       {
        if ($arr_item['nums'] > $arr_item['sendnum'])
        {
         if ($arr_item['sendnum'] > 0)
         {
          $is_part_ship = true;
          break;
         }
         else
         {
          $is_part_ship = false;
         }
         $is_finish_ship = false;
        }
        else
        {
         $is_finish_ship = true;
        }
       }
      }

      if ($is_finish_ship)
       $order_ship_status = '1';
      else
      {
       if ($is_part_ship)
        $order_ship_status = '2';
       else
        $order_ship_status = '0';
      }
      $is_save = $objOrder->update(array('ship_status'=>$order_ship_status),array('order_id'=>$sdf['order_bn']));

      if ($is_save)
      {
       $db->commit($transaction_status);
      }
      else
      {
       $db->rollback();
       $thisObj->send_user_error(app::get('b2c')->_('订单发货状态修改失败！'), array('tid'=>$sdf['order_bn']));
      }

      return array('tid'=>$sdf['order_bn']);

                    }
                    else
                    {
                        $db->rollback();
                        $thisObj->send_user_error(app::get('b2c')->_('修改订单的明细数据有误！'), array('tid'=>$sdf['order_bn']));
                    }
                }
                else
                {
                    $db->rollback();
                    $thisObj->send_user_error(app::get('b2c')->_('订单信息更新失败！'), array('tid'=>$sdf['order_bn']));
                }
            }
        }
    }

    /**
     * 订单备注
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function remark(&$sdf, &$thisObj)
    {
        // 备注订单是和中心的交互
        $order = $this->app->model('orders');
        $arr_order = $order->dump($sdf['order_bn']);

        if ($arr_order)
        {
            if ($arr_order['mark_text'])
                $arr_order['mark_text'] = unserialize($arr_order['mark_text']);
            $mem_info = json_decode($sdf['memo'], true);
            $data['order_id'] = $sdf['order_bn'];
            $arr_order['mark_text'][] = array(
                'mark_text' => $mem_info['op_content'],
                'add_time' => $mem_info['op_time'],
                'op_name' => $mem_info['op_name'],
            );
            $data['mark_text'] = serialize($arr_order['mark_text']);
            $data['mark_type'] = $sdf['mark_type'];

            $is_success = $order->save($data);
            if ($is_success)
            {
                return array('tid'=>$sdf['order_bn']);
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单备注保存失败！'), array('tid'=>$sdf['order_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('此订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
    }

    /**
     * 订单留言
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function leave_message(&$sdf, &$thisObj)
    {
        // 订单留言是和中心的交互
        if (isset($sdf['order_bn']) && $sdf['order_bn'])
        {
            $order = &$this->app->model('orders');
            $arrOrder = $order->dump($sdf['order_bn'], 'member_id');
            $arr_memo = json_decode($sdf['message'], true);
   if (!$arr_memo)
   {
    $thisObj->send_user_error(app::get('b2c')->_('留言内容格式不正确！'), array('tid'=>$sdf['order_bn']));
   }

            if ($arrOrder)
            {
                $objMember = $this->app->model('members');
                $arrMember = $objMember->dump($arrOrder['member_id'], 'name');
                $oMsg = kernel::single("b2c_message_order");

                $order_id = $sdf['order_bn'];
                $arrData['title'] = app::get('b2c')->_('订单 ') . $sdf['order_bn'] . app::get('b2c')->_('管理员留言');
                $arrData['comment'] = htmlspecialchars($arr_memo['op_content']);
                $arrData['to_id'] = $arrOrder['member_id'];
                $arrData['to_uname'] = $arrMember['contact']['name'] ? $arrMember['contact']['name'] : app::get('b2c')->_('顾客');
                $arrData['for_comment_id'] = 0;
                $arrData['author_id'] = 0;
                $arrData['order_id'] = $order_id;
                $arrData['object_type'] = 'order';
                $arrData['author'] = $arr_memo['op_name'];
                $arrData['time'] = $arr_memo['op_time'] ? strtotime($arr_memo['op_time']) : 0;
                $arrData['ip'] = $_SERVER['REMOTE_ADDR'];

                if (!$oMsg->save($arrData))
                {
                    $thisObj->send_user_error(app::get('b2c')->_('订单留言保存失败！'), array('tid'=>$sdf['order_bn']));
                }
                else
                {
                    return array('tid'=>$sdf['order_bn'], 'comment_id'=>$arrData['comment_id']);
                }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('订单号未发送！'), array('tid'=>$sdf['order_bn']));
        }
    }

    /**
     * 订单状态更新
     * @param array sdf
     * @return boolean true or false.
     */
    public function status_update(&$sdf, &$thisObj)
    {
        // 取消订单是和中心的交互


        $order = $this->app->model('orders');
        $arr_data['status'] = $sdf['status'];
        $arr_data['order_id'] = $sdf['order_bn'];

        $arr_order = $order->dump($sdf['order_bn']);
        $db = kernel::database();
        if ($arr_order){

            //事务处理

            $transaction_status = $db->beginTransaction();

            //订单作废，释放冻结库存
            if($sdf['status'] == 'dead') {
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                $arrStatus = $obj_checkorder->checkOrderFreez('cancel', $arr_order['order_id']);
                if($arrStatus['unfreez']) {
                    $is_unfreeze = true;
                    $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
                    $sdf_order = $order->dump($arr_order['order_id'], 'order_id,status,pay_status,ship_status', $subsdf);

                    // 所有的goods type 处理的服务的初始化.
                    $arr_service_goods_type_obj = array();
                    $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
                    foreach ($arr_service_goods_type as $obj_service_goods_type){
                        $goods_types = $obj_service_goods_type->get_goods_type();
                        $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
                    }

                   $objGoods = $this->app->model('goods');
                   foreach($sdf_order['order_objects'] as $k => $v){
                        if ($v['obj_type'] != 'goods' && $v['obj_type'] != 'gift'){
                            foreach( kernel::servicelist('b2c.order_store_extends') as $object ) {
                                if( $object->get_goods_type()!=$v['obj_type'] ) continue;
                                $obj_extends_store = $object;
                                if ($obj_extends_store){
                                    $obj_extends_store->store_change($v, 'cancel');
                                }
                            }
                            continue;
                        }

                        foreach ($v['order_items'] as $arrItem){
                            if ($arrItem['item_type'] == 'product')  $arrItem['item_type'] = 'goods';
                            $arr_params = array(
                                'goods_id' => $arrItem['products']['goods_id'],
                                'product_id' => $arrItem['products']['product_id'],
                                'quantity' => $arrItem['quantity'],
                            );
                            $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                            $is_unfreeze = $str_service_goods_type_obj->unfreezeGoods($arr_params);
                        }
                    }
                }
            }


            $is_save = $order->save($arr_data);

            //订单作废，释放冻结积分
            if($sdf['status'] == 'dead') {
                $obj_order_operations = kernel::servicelist('b2c.order_point_operaction');
                if ($obj_order_operations){
                    $arr_data = array(
                        'member_id'  => $arr_order['member_id'],
                        'score_g'       => $arr_order['score_g'],
                        'score_u'       => $arr_order['score_u'],
                    );
                    foreach ($obj_order_operations as $obj_operation){
                        $obj_operation->gen_member_point($arr_data, $reason);
                    }
                }
            }

            if ($is_save){
                //事务提交
                $db->commit($transaction_status);

                //触发邮件短信事件
                if ($sdf['status'] == 'dead'){
                    $aUpdate['order_id'] = $sdf['order_bn'];
                    //$sdf_order = $order->dump($sdf['order_bn']);
                    $sdf_order = $arr_order;
                    if ($sdf_order['member_id']){
                        $member = $this->app->model('members');
                        $arr_member = $member->dump($sdf_order['member_id'], '*', array(':account@pam'=>'*'));
                    }
                    $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member['contact']['email'];
                    $order->fireEvent("cancel", $aUpdate, $sdf_order['member_id']);

                    foreach( kernel::servicelist("b2c_order_cancel_finish") as $object ) {
                        if( !is_object($object) ) continue;
                        if( !method_exists($object,'order_notify') ) continue;
                        $object->order_notify($arr_order);
                    }
                }

                // 记录订单日志
                $objorder_log = $this->app->model('order_log');
                $log_text = app::get('b2c')->_("订单状态修改！");
                $sdf_order_log = array(
                    'rel_id' => $sdf['order_bn'],
                    'op_id' => '1',
                    'op_name' => 'admin',
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'updates',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $log_id = $objorder_log->save($sdf_order_log);

                return array('tid'=>$sdf['order_bn']);

            }else{
                //事件回滚
                $db->rollback();
                $thisObj->send_user_error(app::get('b2c')->_('订单状态修改失败！'), array('tid'=>$sdf['order_bn']));
            }

        }else{
            //事件回滚
            $db->rollback();
            $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
    }

    /**
     * 订单支付状态更新接口
     * @param array sdf
     * @return boolean true or false
     */
    public function pay_status_update(&$sdf, &$thisObj)
    {
        $thisObj->send_user_error(app::get('b2c')->_('此接口已经弃用'), array('tid'=>$sdf['order_bn']));exit;

        $order = $this->app->model('orders');
        $arr_data['pay_status'] = $sdf['pay_status'];
        $arr_data['order_id'] = $sdf['order_bn'];

        $arr_order = $order->dump($sdf['order_bn']);

        if ($arr_order)
        {
            $is_save = $order->save($arr_data);

            if ($is_save)
            {
                // 记录订单日志
                $objorder_log = $this->app->model('order_log');
                $log_text = app::get('b2c')->_("订单支付状态修改！");
                $sdf_order_log = array(
                    'rel_id' => $sdf['order_bn'],
                    'op_id' => '1',
                    'op_name' => 'admin',
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'updates',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $log_id = $objorder_log->save($sdf_order_log);

                return array('tid'=>$sdf['order_bn']);
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单支付状态修改失败！'), array('tid'=>$sdf['order_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
    }

    /**
     * 订单发货状态更新接口
     * @param array sdf
     * @return boolean true or false
     */
    public function ship_status_update(&$sdf, &$thisObj)
    {


        $order = $this->app->model('orders');
        $arr_data['ship_status'] = $sdf['ship_status'];
        $arr_data['order_id'] = $sdf['order_bn'];

        $arr_order = $order->dump($sdf['order_bn']);
      if ($arr_order['ship_status'] == '1')
      {
       return array('tid'=>$sdf['order_bn']);
      }

        if ($arr_order)
        {
            $is_save = $order->save($arr_data);

            if ($is_save)
            {
                // 记录订单日志
                $objorder_log = $this->app->model('order_log');
                $log_text = app::get('b2c')->_("订单发货状态修改！");
                $sdf_order_log = array(
                    'rel_id' => $sdf['order_bn'],
                    'op_id' => '1',
                    'op_name' => 'admin',
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'updates',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $log_id = $objorder_log->save($sdf_order_log);

                return array('tid'=>$sdf['order_bn']);
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单发货状态修改失败！'), array('tid'=>$sdf['order_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
    }

    public function get_detail(&$data, &$obj)
    {
        /**
         * 订单的三个状态的修改
         * order status - 订单状态
         * pay status - 订单支付状态
         * shipping status - 订单发货状态
         */


        $order_status = array('active'=>'TRADE_ACTIVE','dead'=>'TRADE_CLOSED','finish'=>'TRADE_FINISHED');
        $pay_status = array(0=>'PAY_NO',1=>'PAY_FINISH',2=>'PAY_TO_MEDIUM',3=>'PAY_PART',4=>'REFUND_PART',5=>'REFUND_ALL');
        $ship_status = array(0=>'SHIP_NO',1=>'SHIP_FINISH',2=>'SHIP_PART',3=>'RESHIP_PART',4=>'RESHIP_ALL');

        $is_listitem = isset($data['listItem']) ? intval($data['listItem']) : 1;
        if(!(isset($data['tid']) && $data['tid']))
        {
            $obj->send_user_error('订单号不能为空',array('tid'=>$data['order_bn']));
        }

        if(is_array($data['tid']))
        {
            $data['tid'] = $data['tid'][0];
        }

        $filter = array(
            'order_id'=>$data['tid'],
        );
        $obj_order = $this->app->model('orders');
        $db = &kernel::database();
        $tmp = $obj_order->getList('*', $filter);

        if ($tmp)
        {
            $orders = $tmp[0];
            if($orders['ship_area'])
            {
                $tmp1 = explode(':',$orders['ship_area']);
                $area = explode('/',$tmp1[1]);
            }

            $returndata = array(
                'tid'=>$orders['order_id'],
                'created'=>date('Y-m-d H:i:s', $orders['createtime']),
                'modified'=>date('Y-m-d H:i:s', $orders['last_modified']),
                'status'=>$order_status[$orders['status']],
                'pay_status'=>$pay_status[$orders['pay_status']],
                'ship_status'=>$ship_status[$orders['ship_status']],
                'has_invoice'=>$orders['is_tax'],
                'invoice_title'=>$orders['tax_company'],
                'invoice_fee'=>$orders['cost_tax'],
                'total_goods_fee'=>(is_null($orders['cost_item'])?0:$orders['cost_item']),
                'total_trade_fee'=>(is_null($orders['final_amount'])?0:$orders['final_amount']),
                'discount_fee'=>(is_null($orders['discount'])?0:$orders['discount']),
                'payed_fee'=>(is_null($orders['payed'])?0:$orders['payed']),
                'currency'=>$orders['currency'],
                'currency_rate'=>$orders['cur_rate'],
                'total_currency_fee'=>(is_null($orders['total_amount'])?0:$orders['total_amount']),
                'buyer_obtain_point_fee'=>$orders['score_g'],
                'point_fee'=>$orders['score_u'],
                'total_weight'=>(is_null($orders['weight'])?0:$orders['weight']),
                'shiptype_id'=>$orders['shipping_id'],
                'shiptype_name'=>$orders['shipping'],
                'shipping_fee'=>(is_null($orders['cost_freight'])?0:$orders['cost_freight']),
                'is_delivery '=>($orders['is_delivery ']=='Y')?"true":"false",
                'is_protect'=>$orders['is_protect'],
                'protect_fee'=>$orders['cost_protect'],
                'receiver_name'=>$orders['ship_name'],
                'receiver_email'=>$orders['ship_email'],
                'receiver_state'=>$area[0],
                'receiver_city'=>$area[1],
                'receiver_district'=>$area[2],
                'receiver_address'=>$orders['ship_addr'],
                'receiver_zip'=>$orders['ship_zip'],
                'receiver_mobile'=>$orders['ship_mobile'],
                'receiver_phone'=>$orders['ship_tel'],
                'payment_type'=>$orders['payment'],
                'trade_memo'=>$orders['memo'],
            );
            /** extends order parameters **/
            if (isset($orders['extends']) && $orders['extends'])
            {
                foreach ($orders['extends'] as $key=>$items)
                {
                    $returndata[$key] = $items;
                }
            }
            /** end **/

            $obj_order_items = $this->app->model('order_items');
            $tmp = $obj_order_items->getList('*', $filter);
            if ($tmp)
                $order_items = $tmp[0];
            else
                $order_items = array();

            if($order_items)
            {
                foreach($order_items as $k=>$v){
                    $returndata['orders']['order'][] = $v;
                }
            }
        }
        else{
            $obj->send_user_error('订单号不存在',array('tid'=>$data['order_bn']));
        }

        return (array)$returndata;
    }

        // csone 下载订单 
     public function csone_order_info(&$data, &$obj){
        $filter = array();

        if(!(isset($data['start_time']) && $data['start_time'])){
            $obj->send_user_error('请设置开始时间', array());
        }
        $filter['createtime|bthan'] = $data['start_time'];

        if(!isset($data['end_time']) || !$data['end_time']){
            $data['end_time'] = time();//如果没有设置结束时间或结束时间为空，那么将结束时间置为当前时间
        }
        $filter['createtime|sthan'] = $data['end_time'];

        //订单状态
        if(isset($data['order_status']) && $data['order_status']){
              $filter['status|in'] = explode(',',$data['order_status']);
        }

        //发货状态
        if(isset($data['ship_status']) && $data['ship_status'] !== ''){
            $arr_ship_status = explode(',',$data['ship_status']);
//            foreach($arr_ship_status as $sk => $sv){
//                $arr_ship_status[$sk] = '\'' . $sv . '\'';
//            }
            $filter['ship_status|in'] = $arr_ship_status;
        }

        //支付状态
        if(isset($data['pay_status']) && $data['pay_status'] !== ''){
            $arr_pay_status = explode(',',$data['pay_status']);
//            foreach($arr_pay_status as $pk => $pv){
//                $arr_pay_status[$pk] = '\'' . $pv . '\'';
//            }
            $filter['pay_status|in'] = $arr_pay_status;
        }

        $page_size = $data['page_size'] ? (int)$data['page_size'] : 20;
        $page_current = $data['current_page'] ? (int)$data['current_page'] : 1;

        if(!$order_mdl = $this->app->model('orders')){
            $obj->send_user_info('系统错误', array());
        }

        $rows = $order_mdl->getList('*', $filter, ($page_current - 1) * $page_size, $page_size);
        if(!$rows){
            return array();
        }

        //订单商品obj
        if(!$order_item_mdl = $this->app->model('order_items')){
            $obj->send_user_info('系统错误', array());
        }

        //订单发货obj
        if(!$delivery_mdl = $this->app->model('delivery')){
            $obj->send_user_info('系统错误', array());
        }

        //订单发货明细obj
        if(!$dly_items_mdl = $this->app->model('delivery_items')){
            $obj->send_user_info('系统错误', array());
        }

        //订单退货obj
        if(!$reship_mdl = $this->app->model('reship')){
            $obj->send_user_info('系统错误', array());
        }

        //订单退货明细obj
        if(!$rsp_items_mdl = $this->app->model('reship_items')){
            $obj->send_user_info('系统错误', array());
        }

//        //订单交易obj
//        if(!$order_delivery_mdl = $this->app->model('order_delivery')){
//            $obj->send_user_info('系统错误', array());
//        }

        //订单支付obj
        if(!$order_payment_mdl = app::get('ectools')->model('payments')){
            $obj->send_user_info('系统错误', array());
        }

        //订单退款obj
        if(!$order_refunds_mdl = app::get('ectools')->model('refunds')){
            $obj->send_user_info('系统错误', array());
        }

        //订单促销obj
        if(!$order_pmt_mdl = $this->app->model('order_pmt')){
            $obj->send_user_info('系统错误', array());
        }

        foreach($rows as $key => $value){

            //订单信息
            $order_info=$order_mdl->dump(array('order_id'=>$value['order_id']));
            $rows[$key]=$order_info;

            //转成json 配送信息
            if (isset($rows[$key]['shipping']) && $rows[$key]['shipping'])
            {
                $rows[$key]['shipping'] = json_encode($rows[$key]['shipping']);
            }

            //支付信息
            $pay_arr=array('online'=>'在线支付','offline'=>'线下支付','deposit'=>'预存款支付');
            if (isset($rows[$key]['payinfo']) && $rows[$key]['payinfo'])
            {
                $rows[$key]['payinfo']['pay_name'] =$pay_arr[$rows[$key]['payinfo']['pay_app_id']];
               unset($rows[$key]['payinfo']['pay_app_id']);
                $rows[$key]['payinfo']=json_encode($rows[$key]['payinfo']);
            }


            $item_rows = $order_item_mdl->getList('*', array('order_id' => $value['order_id']));
            $rows[$key]['order_goods'] = $item_rows;    //订单商品信息

            $delivery_rows = $delivery_mdl->getList('*', array('order_id' => $value['order_id']));
            if(!empty($delivery_rows)){
                foreach($delivery_rows as $dkey => $dvalue){
                    $dly_item_rows = $dly_items_mdl->getList('*', array('delivery_id' => $dvalue['delivery_id']));
                    unset($dly_item_rows['item_id']);
                    $delivery_rows[$dkey]['delivery_items'] = $dly_item_rows;
                }
            }
            $rows[$key]['consigner'] = json_encode($delivery_rows); //订单发货信息(发货单信息、发货明细信息)

            $reship_rows = $reship_mdl->getList('*', array('order_id' => $value['order_id']));
            if(!empty($reship_rows)){
                foreach($reship_rows as $rkey => $rvalue){
                    $rsp_item_rows = $rsp_items_mdl->getList('*', array('reship_id' => $rvalue['reship_id']));
                    $reship_rows[$rkey]['items'] = $rsp_item_rows;
                }
            }

            $rows[$key]['order_reship'] = $reship_rows; //订单退货信息(退货单信息、退货明细信息)

            $payment_rows = $order_payment_mdl->get_all_payments_by_order_id($value['order_id']);
            $rows[$key]['order_payment'] = $payment_rows[0]; //订单收款单信息

            $refunds_rows = $order_refunds_mdl->get_all_refunds_by_order_id($value['order_id']);
            $rows[$key]['order_refund'] = $refunds_rows[0]; //订单退款单信息

            $pmt_rows = $order_pmt_mdl->getList('*', array('order_id' => $value['order_id']));
            $rows[$key]['pmt_detail'] = json_encode($pmt_rows);   //订单关联的促销信息

            // 增加会员信息
            $member_mdl= $this->app->model('members');
            $member_rows=$member_mdl->dump(array('member_id'=>$value['member_id']),'*');//会员信息
            /*
            $member_addr_mdl=$this->app->model('member_addrs');
            $member_addr_rows=$member_addr_mdl->dump(array('member_id'=>$value['member_id']),'*');//会员地址
            */
            $currency_mdl=app::get('ectools')->model('currency');
            $currency_rows=$currency_mdl->dump(array('cur_default'=>'true'),'*');//会员货币

            $pam_mdl=app::get('pam')->model('account');
            $pam_rows=$pam_mdl->dump(array('account_id'=>$value['member_id']),'*');

            $order_num=$order_mdl->count(array('member_id'=>$value['member_id']));//订单数
            $member_comments_mdl=$this->app->model('member_comments');
            $member_comments=$member_comments_mdl->getList('*',array('object_type'=>'order','order_id'=>$value['order_id'],'author_id'=>$value['member_id']));
            $rows[$key]['custom_mark']=$member_comments;
            $consignee=array();//收货人信息
            foreach($value as $k=>$v){

                if(strpos($k,'ship_')===0){
                    if($k=='ship_tel'){
                        $consignee['telephone']=$v;
                        $receivers['tel']=$v;
                    }else if($k=='ship_time'){
                        $consignee['r_time']=$v;
                    }elseif($k=='ship_name'){
                        $consignee['name']=$v;
                        $receivers['user_name']=$v;
                    }else if($k=='ship_area'){
                        if($v){
                            $ship_area=explode(':',$v);
                            $ship_area=explode('/',$ship_area[1]);
                            $consignee['area_state']=$ship_area[0];
                            $consignee['area_city']=$ship_area[1];
                            $consignee['area_district']=$ship_area[2];
                        }
                        $receivers['area']=$v;
                    }else{
                        $k=substr($k,strpos($k,'ship_')+5,strlen($k));
                        $consignee[$k]=$v;
                        $receivers[$k]=$v;
                    }

                }else{

                }
            }
            $rows[$key]['consignee']=json_encode($consignee);


            //订单详细
            $order_items=array();
            $order_items_mdl=$this->app->model('order_items');
            $order_items=$order_items_mdl->getList('*',array('order_id'=>$value['order_id']));

            //订单对象
            $order_objs=array();
            $order_obj_mdl=$this->app->model('order_objects');
            $order_objs=$order_obj_mdl->getList('*',array('order_id'=>$value['order_id']));
            $order_objects=array();
            if(!empty($order_objs)){
                foreach($order_items as $itemkey=>$itemv){

                    foreach($order_objs as $objkey=>$objv){
                        if($objv['obj_id']==$itemv['obj_id']){
                            $order_objs[$objkey]['order_items'][]=array(
                                    'products'=>array(
                                        'product_id'=>$itemv['product_id']
                                    ),
                                    'goods_id'=>$itemv['goods_id'],
                                    'order_id'=>$itemv['order_id'],
                                    'item_type'=>$itemv['item_type'],
                                    'bn'=>$itemv['bn'],
                                    'name' => $itemv['name'],
                                    'type_id' => $itemv['type_id'],
                                    'cost' => $itemv['cost'],
                                    'quantity' =>$itemv['nums'],
                                    'sendnum' => $itemv['sendnum'],
                                    'amount' =>$itemv['amount'],
                                    'score' => $itemv['score'],
                                    'price' => $itemv['price'],
                                    'g_price' => $itemv['g_price'],
                                    'weight' => $itemv['weight'],
                                    'addon' => $itemv['addon']
                                );
                        }
                    }
                }
            }


            $rows[$key]['order_objects']=json_encode($order_objs);
            $member_info=array();
            $member_info['shop_info']=$shop_info;
            $member_info=$member_rows;
            $member_info['name']=$member_rows['contact']['name'];
            $member_info['sex']=$member_rows['profile']['gender'];
            $member_info['area']=$member_rows['contact']['area'];
            $member_info['uname']=$pam_rows['login_name'];
            $member_info['regtime']=$pam_rows['createtime'];
            if($member_rows['area']){
                $area=implode(':',$member_rows['area']);
                $area=implode('/',$area[1]);
                $member_info['area_state']=$area[0];
                $member_info['area_city']=$area[1];
                $member_info['area_district']=$area[2];
            }

            $member_info['addr']=$member_rows['addr'];

            $member_info['tel']=$member_rows['tel'];
            $member_info['mobile']=$member_rows['mobile'];
            $member_info['email']=$member_rows['email'];
            $member_info['zip']=$member_rows['zip'];
            $member_info['order_num']=$order_num;
            $member_info['b_year']=$member_rows['b_year'];
            $member_info['b_month']=$member_rows['b_month'];
            $member_info['b_day']=$member_rows['b_day'];
            $member_info['profile']['gender']=$member_info['sex'];
            $member_info['channel_id']='b2b2c'; //3为B2C
            $member_info['currency']=$currency_rows['cur_code'];
            $member_info['disabled']='true';
            $member_info['is_customer']='true';
            $member_info['receivers']=$receivers;
            $rows[$key]['member_info']=json_encode($member_info);


            //end
        }

        $rows['total_count'] = $order_mdl->count($filter);    //筛选出来的订单总数
        return (array)$rows;
    }

     public function csone_member_info(&$data, &$obj){
        $filter = array();

        if(!(isset($data['start_time']) && $data['start_time'])){
            $obj->send_user_error('请设置开始时间', array());
        }
        $filter['regtime|bthan'] = $data['start_time'];

        if(!isset($data['end_time']) || !$data['end_time']){
            $data['end_time'] = time();//如果没有设置结束时间或结束时间为空，那么将结束时间置为当前时间
        }
        $filter['regtime|sthan'] = $data['end_time'];


        $page_size = $data['page_size'] ? (int)$data['page_size'] : 20;
        $page_current = $data['current_page'] ? (int)$data['current_page'] : 1;

        if(!$member_mdl = $this->app->model('members')){
            $obj->send_user_info('系统错误', array());
        }

        $rows = $member_mdl->getList('*', $filter, ($page_current - 1) * $page_size, $page_size);
        if(!$rows){
            return array();
        }
        foreach($rows as $key=>$value){

            //用户名
            $pam_mdl=app::get('pam')->model('account');
            $pam_rows=$pam_mdl->dump(array('account_id'=>$value['member_id']),'*');
            $rows[$key]['uname']=$pam_rows['login_name'];
            $rows[$key]['regtime']=$pam_rows['createtime'];

            //地址
            $mem_addr_mdl=app::get('b2c')->model('member_addrs');
            $mem_addr=$mem_addr_mdl->dump(array('member_id'=>$value['member_id']),'*');
            $rows[$key]['contact']=$mem_addr;

            $rows[$key]['profile']['gender']=$value['sex'];
            $rows[$key]['channel_id']='b2b2c'; //3为B2C
            $rows[$key]['currency']=$value['cur'];
            /*
            //收货人信息
            $receivers=array();
            $receivers=$mem_addr;
            $receivers['user_name']=$mem_addr['name'];
            $rows[$key]['receivers']=$receivers;
            */
            //订单数
            $order_mdl=app::get('b2c')->model('orders');
            $order_num=$order_mdl->count(array('member_id'=>$value['member_id']));//订单数
            $rows[$key]['order_num']=$order_num;
            if($order_num>0)
                $rows[$key]['is_customer']='true';
            else
                $rows[$key]['is_customer']='false';


            unset($rows[$key]['member_id']);

        }

        return (array)$rows;
    }

}