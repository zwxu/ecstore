<?php



/**
 * b2c reship interactor with center
 */
class b2c_api_basic_reship
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
     * 退货单创建
     * @param array sdf
     * @return boolean success or failure
     */
    public function create(&$sdf, $thisObj)
    {
        $oreship = $this->app->model('reship');

        if (!$sdf['reship_bn'] || !$sdf['order_bn'] || !isset($sdf['reship_bn']) || !isset($sdf['order_bn']))
        {
            $thisObj->send_user_error(app::get('b2c')->_('退货单tid没有收到！'), array('tid'=>'','reship_id'=>''));
        }
        else
        {
            // 退货单创建是和中心的交互
            $cnt = $oreship->count(array('reship_bn' => $sdf['reship_bn']));

            if (!$cnt)
            {
                $arr_items = json_decode($sdf['items'], true);
                $order_item = $this->app->model('order_items');
                $o = $this->app->model('reship_items');
                $objMath = kernel::single('ectools_math');
                $order = $this->app->model('orders');

                // 开始数据库事务
                $db = kernel::database();
                $transaction_status = $db->beginTransaction();

                $obj_products = $this->app->model('products');
                $obj_dlytype = $this->app->model('dlytype');
                $arr_dlytype = $obj_dlytype->dump(array('dt_name' => $sdf['delivery']));
                $obj_dlycorp = $this->app->model('dlycorp');
                $arr_dlycorp = $obj_dlycorp->dump(array('name' => $sdf['logi_name']));
                $obj_regions = app::get('ectools')->model('regions');
                $arr_regions = $obj_regions->dump(array('local_name' => $sdf['ship_distinct']));
                $reship_id = $oreship->gen_id();

                $arr_data = array(
                    'money' => $sdf['money'],
                    'order_id' => $sdf['order_bn'],
                    'is_protect' => ($sdf['is_protect']) ? 'true' : 'false',
                    'delivery' => $arr_dlytype['dt_id'] ? $arr_dlytype['dt_id'] : 0,
                    'reship_id' => $reship_id,
                    'reship_bn' => $sdf['reship_bn'],
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
                $arr_data['t_begin'] = strtotime($sdf['timestamp']);
                $arr_data['op_name'] = $sdf['buyer_uname'] ? $sdf['buyer_uname'] : '';

                $arr_data['status'] = $sdf['status'];

                $oreship->save($arr_data);

                $items = array();
                $has_error = false;
                $nonGoods = 0;    //是否完全退货商品标识
                $failitems = array();
                if ($arr_items)
                {
                    foreach ($arr_items as $arr_item_info)
                    {
                        $spe_odr_item_filter = array(
							'order_id'=>$sdf['order_bn'],
                            'bn'=>$arr_item_info['product_bn'],
                            'filter_sql'=>'sendnum > 0',
                        );
                        $arr_order_items = $order_item->getList('*',$spe_odr_item_filter);
                        if (!$arr_order_items)
                        {
                            $thisObj->send_user_error(app::get('b2c')->_('订单相应的明细不存在！'), array('reship_id' => $sdf['reship_bn'], 'tid' => $sdf['order_bn']));
                        }

                        $need_reship = 0;
                        foreach ($arr_order_items as $arr_odr_item)
                        {
                            $need_reship = $arr_odr_item['sendnum'];
                            if ($arr_item_info['number'] < $need_reship)
                            {
                                $items = array(
                                    'reship_id' => $reship_id,
                                    'item_type' => $arr_item_info['item_type'] == 'goods' ? 'goods' : 'gift',
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
                                    'reship_id' => $reship_id,
                                    'item_type' => $arr_item_info['item_type'] == 'goods' ? 'goods' : 'gift',
                                    'product_id' => $arr_odr_item['product_id'],
                                    'product_bn' => $arr_item_info['product_bn'],
                                    'product_name' => $arr_item_info['product_name'],
                                    'number' => $need_reship,
                                );
                                $arr_item_info['number'] = $objMath->number_minus(array($arr_item_info['number'], $need_reship));
                            }

                            $o->save($items);
                            if ($arr_item_info['number'] == 0)
                                break;
                        }
                        if ($arr_item_info['number'] > 0)
                        {
                            $has_error = true;
                            $fail_items[] = array(
                                'delivery_id' => $reship_id,
                                'item_type' => $arr_item_info['item_type'] == 'product' ? 'goods' : 'gift',
                                'product_bn' => $arr_item_info['product_bn'],
                                'product_name' => $arr_item_info['product_name'],
                                'number' => $arr_item_info['number'],
                            );
                            break;
                        }
                    }

                    if (!$has_error)
                    {
                        $db->commit($transaction_status);
                        return array('tid'=>$sdf['order_bn'],'reship_id' =>$sdf['reship_bn']);
                    }
                    else
                    {
                        $db->rollback();
                        $thisObj->send_user_error(app::get('b2c')->_('退货单明细单保存有误！'), $failitems);
                    }
                }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('退货单已经存在了！'), array('tid'=>$sdf['order_bn'],'reship_id'=>$sdf['reship_bn']));
            }
        }
    }

    /**
     * 退货单修改
     * @param array sdf
     * @return boolean sucess of failure
     */
    public function update(&$sdf, $thisObj)
    {
        // 退货单修改是和中心的交互
        $oreship = $this->app->model('reship');
        $arr_data = $oreship->dump(array('reship_bn' => $sdf['reship_bn'], 'order_id' => $sdf['order_bn']));

        if ($arr_data)
        {
            $obj_dlytype = $this->app->model('dlytype');
            $arr_dlytype = $obj_dlytype->dump(array('dt_name' => $sdf['delivery']));
            $obj_dlycorp = $this->app->model('dlycorp');
            $arr_dlycorp = $obj_dlycorp->dump(array('name' => $sdf['logi_name']));
            $obj_regions = app::get('ectools')->model('regions');
            $arr_regions = $obj_regions->dump(array('local_name' => $sdf['ship_distinct']));
            $reship_id = $oreship->gen_id();
            $order = $this->app->model('orders');
            $objMath = kernel::single('ectools_math');

            if (isset($arr_regions) && $arr_regions)
            {
                $arr_data = array(
                    'is_protect' => ($sdf['is_protect']) ? 'true' : 'false',
                    'logi_id' => $arr_dlycorp['corp_id'] ? $arr_dlycorp['corp_id'] : 0,
                    'logi_name' => $sdf['logi_name'],
                    'ship_name' => $sdf['ship_name'],
                    'ship_area' => $arr_regions['package'] . ":" . $sdf['ship_states'] . "/" . $sdf['ship_city'] . "/" . $sdf['ship_distinct'] . ":" . $arr_regions['region_id'],
                    'ship_addr' => $sdf['ship_addr'],
                    'ship_zip' => $sdf['ship_zip'],
                    'ship_tel' => $sdf['ship_tel'],
                    'ship_mobile' => $sdf['ship_mobile'],
                    'ship_email' => $sdf['ship_email'],
                );
            }

            if ($arr_dlytype)
                $arr_data['delivery'] = $arr_dlytype['dt_id'];
            else
                $arr_data['delivery'] = 0;
            if ($sdf['memo'])
                $arr_data['memo'] = $sdf['memo'];
            if ($sdf['money'])
                $arr_data['money'] = $sdf['money'];
            if ($sdf['logi_no'])
                $arr_data['logi_no'] = $sdf['logi_no'];
            $status = $arr_data['status'];
            if ($sdf['status'])
                $arr_data['status'] = $sdf['status'];

            $is_updated = true;
            $db = kernel::database();
            $transaction_status = $db->beginTransaction();

            if ($status != 'succ')
            {
                if ($arr_data['status'] == 'succ')
                {
                    $obj_reship_item = $this->app->model('reship_items');
					$obj_odr_object = $this->app->model('order_object');
                    $arr_reship_items = $obj_reship_item->getList('*',array('delivery_id'=>$arr_data['delivery_id']));
                    if ($arr_reship_items)
                    {
                        foreach ($arr_reship_items as $arr_reship_item)
                        {
                            //更新发货量
                            $tmp = $order_item->dump(array('order_id' => $sdf['order_bn'], 'product_id' => $arr_reship_item['product_id'], 'item_id'=>$arr_reship_item['order_item_id']),'*');
                            if (!$tmp)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('需要退货的商品不存在于订单明细里！'), array('tid'=>$sdf['order_bn'],'reship_id'=>$sdf['reship_bn']));
                            }

                            $update_data['sendnum'] = $objMath->number_minus(array($tmp['sendnum'], $arr_reship_item['number']));
							$tmp_odr_obj = $obj_odr_object->getList('*',array('obj_id'=>$tmp['obj_id']));
							if (!$tmp_odr_obj)
							{
								$db->rollback();
								$thisObj->send_user_error(app::get('b2c')->_('需要退货的商品所在的子订单不存在！'), array('reship_id' => $sdf['reship_bn'], 'tid' => $sdf['order_bn']));
							}
							// 处理其他类型子订单的退货
							if ($tmp_odr_obj[0]['obj_type'] != 'goods' && $tmp_odr_obj[0]['obj_type'] != 'gift')
							{
								if (!$arr_extends_objs[$tmp['obj_id']])
									$arr_extends_objs[$tmp['obj_id']] = array(
										'obj_type'=>$tmp_odr_obj[0]['obj_type'],
									);
								if ($update_data['sendnum'] == $tmp['nums'])
								{
									$arr_extends_objs[$tmp['obj_id']]['is_finish'] = true;
								}
								else
								{
									$arr_extends_objs[$tmp['obj_id']]['is_finish'] = false;
								}
							}

                            $update_data['item_id'] = $tmp['item_id'];
                            $is_updated = $order_item->save($update_data);

                            if (!$is_updated)
                            {
                                $db->rollback();
                                $thisObj->send_user_error(app::get('b2c')->_('订单明细发货数量保存失败！'), array('tid'=>$sdf['order_bn'],'reship_id'=>$sdf['reship_bn']));
                            }
                        }

						// 处理其他对象类别的退货处理
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
									$subsdf = array('*',array('order_items'=>array('*',array(':products'=>'*'))));
									$v = $obj_odr_object->dump($key,$subsdf);
									$arr_extends_objects[$arr_extends_item['obj_type']]['obj']->store_change($v, 'reship', 'delivery_finish');
								}
							}
						}
                    }
                }
                $is_updated = $oreship->save($arr_data);
                if (!$is_updated)
                {
                    $db->rollback();
                    $thisObj->send_user_error(app::get('b2c')->_('退货单信息修改失败！'), array('tid'=>$sdf['order_bn'],'reship_id'=>$sdf['reship_bn']));
                }
            }

			// 更新物流单号日志.
			if ($sdf['logi_no'] && (!isset($sdf['status']) || $sdf['status'] != 'succ'))
			{
				/** 生成订单退货日志 **/
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

            if ($sdf['status'] == 'succ')
            {
				$db = kernel::database();
				$transaction_status = $db->beginTransaction();

				$is_part_reship = false;
				$o = $this->app->model('order_items');
				$arr_order_items = $o->getList('*', array('order_id' => $sdf['order_bn']));
				$arr_reship_items_send = array();
				if ($arr_order_items)
				{
					foreach ($arr_order_items as $arr_item)
					{
						if ($arr_item['sendnum'] == $arr_item['nums'])
						{
							$is_part_reship = true;
						}
						else
						{
							$arr_reship_items_send[] = array(
								'number' => $objMath->number_minus(array($arr_item['nums'], $arr_item['sendnum'])),
								'name' => $arr_item['name'],
							);
						}
					}
				}

				if ($is_part_reship)
				{
					$ship_status = '3';
				}
				else
				{
					$ship_status = '4';
				}

                $aUpdate = array();
                $aUpdate['order_id'] = $sdf['order_bn'];
                $aUpdate['ship_status'] = $ship_status;

                $is_updated = $order->save($aUpdate);
                if (!$is_updated)
                {
                    $db->rollback();
                    $thisObj->send_user_error(app::get('b2c')->_('订单发货状态修改失败！'), array('reship_id' => $sdf['reship_bn'], 'tid' => $sdf['order_bn']));
                }

				/** 生成订单退货日志 **/
				$objorder_log = $this->app->model('order_log');
				$tmp = $objorder_log->getList('*',array('rel_id'=>$sdf['order_bn'],'behavior'=>'delivery'));
				$arr_order_log = array();

				switch ($ship_status)
				{
					case '3':
						if ($tmp)
						{
							if ($tmp[0]['log_text'])
							{
								if (strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')) === false)
									$log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
	\" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('已退货').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
								else
								{
									$log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
	\" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('已退货').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . "，" . substr($tmp[0]['log_text'], strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')));
								}
							}
							else
							{
								$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;120100818000001&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
		\" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('已退货').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
							}

							$arr_order_log = array(
								'log_text'=>$log_text,
							);
							$objorder_log->update($arr_order_log, array('log_id'=>$tmp[0]['log_id']));
						}
						else
						{
							$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
	\" href=\"javascript:void(0)\">".app::get('b2c')->_('部分商品')."</a>".app::get('b2c')->_('已退货').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
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
					case '4':
						if ($tmp)
						{
							if ($tmp[0]['log_text'])
							{
								if (strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号')) === false)
									$log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
	\" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('退货完成').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
								else
								{
									$log_text = app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
	\" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('退货完成').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . "，" . substr($tmp[0]['log_text'], strpos($tmp[0]['log_text'], app::get('b2c')->_('物流单号'))+12);
								}
							}
							else
							{
								$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;120100818000001&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
		\" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('退货完成').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
							}

							$arr_order_log = array(
								'log_text'=>$log_text,
							);
							$objorder_log->update($arr_order_log, array('log_id'=>$tmp[0]['log_id']));
						}
						else
						{
							$log_text = "".app::get('b2c')->_('订单')."<a style=\"color: rgb(0, 51, 102); font-weight: bolder; text-decoration: underline;\" title=\"".app::get('b2c')->_('点击查看详细')."\" onclick=\"show_delivery_item(this,&quot;".$arr_data['delivery_id']."&quot;," . htmlentities(json_encode($arr_reship_items), ENT_QUOTES) . ")
	\" href=\"javascript:void(0)\">".app::get('b2c')->_('全部商品')."</a>".app::get('b2c')->_('退货完成').(($arr_dlycorp) ? "，" . app::get('b2c')->_('物流公司')."：<a href=\"" . $arr_dlycorp['request_url'] . "\" title=\"" . $arr_dlycorp['name'] . "\" _target=\"blank\" class=\"lnk\">" . $arr_dlycorp['name'] . "</a>（".app::get('b2c')->_('可点击进入物流公司网站跟踪配送')."）" : "") . (($sdf['logi_no']) ? "，".app::get('b2c')->_('物流单号')."：" . $sdf['logi_no'] : "");
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

            //退货监控
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
                // 取得物流公司的名称
                $obj_dlycorp = $this->app->model('dlycorp');
                $arr_dlycorp = $obj_dlycorp->dump($arr_data['delivery'], 'name');
                if ($arr_dlycorp)
                    $aUpdate['ship_corp'] = $arr_dlycorp['name'];
                else
                    $aUpdate['ship_corp'] = "";
                if ($sdf_order['member_id'])
                {
                    $member = $this->app->model('members');
                    $arr_member = $member->dump($sdf_order['member_id'], '*', array(':account@pam'=>'*'));
                }
                $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member['contact']['email'];

                $order->fireEvent('returned', $aUpdate, $sdf_order['member_id']);
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('退货单不存在！'), array('tid'=>$sdf['order_bn'],'reship_id'=>$sdf['reship_bn']));
        }

        return array('tid'=>$sdf['order_bn'],'reship_id' =>$sdf['reship_bn']);
    }

    /**
     * 得到退货单列表
     * @param mixed sdf
     * @param object service object
     * @return array
     */
    public function getList(&$data,&$obj)
    {
        if (isset($data['tid']) && count($data['tid'])>0)
        {
            $filter['order_id'] = (array)$data['tid'];
        }

        if (isset($data['page_no']) && intval($data['page_no'])>0)
        {
            $page_no = intval($data['page_no']);
        }
        else
        {
            $page_no = 1;
        }

        if (isset($data['page_size']) && intval($data['page_size'])>0)
        {
            $page_size = (intval($data['page_size'])>100)?100:intval($data['page_size']);
        }
        else
        {
            $page_size = 100;
        }

        if (isset($data['start_created']) && $data['start_created'])
        {
            $filter['t_begin|bthan'] = strtotime($data['start_created']);
        }

        if (isset($data['end_created']) && $data['end_created'])
        {
            $filter['t_begin|sthan'] = strtotime($data['end_created']);
        }

        $subsdf = array('reship_items'=>array('*'));
        $reships = $this->app->model('reship')->batch_dump($filter,'*',$subsdf,($page_no-1)*$page_size, $page_size);

        if ($reships && count($reships)>0)
        {
            $returndata = count($reships);
            foreach ($reships as $k=>$v)
            {
                if ($v['ship_area'])
                {
                    $tmp1 = explode(':',$v['ship_area']);
                    $area = explode('/',$tmp1[1]);
                }

                $reship['reship_id'] = $v['reship_id'];
                $reship['tid'] = $v['order_id'];
                $reship['is_protect'] = $v['is_protect'];
                $reship['buyer_id'] = $v['member_id'];
                $reship['reship_type'] = $v['delivery'];
                $reship['reship_fee'] = $v['money'];
                $reship['logistics_id'] = $v['logi_id'];

                $reship['logistics_company'] = $v['logi_name'];
                $reship['logistics_no'] = $v['logi_no'];
                $reship['receiver_name'] = $v['ship_name'];
                $reship['receiver_state'] = isset($area[0])?$area[0]:'';
                $reship['receiver_city'] = isset($area[1])?$area[1]:'';
                $reship['receiver_district'] = isset($area[2])?$area[2]:'';
                $reship['receiver_address'] = $v['ship_addr'];
                $reship['receiver_zip'] = $v['ship_zip'];
                $reship['receiver_mobile'] = $v['ship_mobile'];
                $reship['receiver_phone'] = $v['ship_tel'];
                $reship['receiver_email'] = $v['ship_email'];
                $reship['t_begin'] = date('Y-m-d H:i:s', $v['t_begin']);
                $reship['t_end'] = !is_null($v['t_end'])?date('Y-m-d H:i:s', $v['t_end']):'';
                $reship['status'] = strtoupper($v['status']);
                $reship['reship_operator'] = $v['op_name'];
                $reship['memo'] = $v['memo'];
                if($v['reship_items'] && count($v['reship_items'])>0){
                    foreach($v['reship_items'] as $k2=>$v2){
                        $reship_items[] = array(
                            'sku_type'=>$v2['item_type'],
                            'name'=>$v2['product_name'],
                            'bn'=>$v2['product_bn'],
                            'number'=>$v2['number']
                        );
                    }
                }
                $reship['reship_items']['reship_item'] = $reship_items;
                $returndata['reships']['reship'][] = $reship;
                unset($reship_items);
                unset($reship);
            }
        }

        return $returndata;
    }

    /**
     * 退货单详情
     * @param tid 订单号
     * @return array 退货单信息
     */
    public function get_detail(&$data, &$obj)
    {
        if(!(isset($data['tid']) && $data['tid']))
        {
            $obj->send_user_error("订单号不能为空", array('tid'=>''));
        }

        $order_id = $data['tid'];
        $obj_reship = $this->app->model('reship');
        $filter = array(
            'order_id'=>$order_id,
        );
        $tmp = $obj_reship->getList('*', $filter);
        if ($tmp)
            $reship = $tmp[0];
        else
            $reship = array();

        if ($reship)
        {
            if ($reship['ship_area'])
            {
                $tmp1 = explode(':',$reship['ship_area']);
                $area = explode('/',$tmp1[1]);
            }

            $returndata['reship_id'] = $reship['reship_id'];
            $returndata['tid'] = $reship['order_id'];
            $returndata['is_protect'] = $reship['is_protect'];
            $returndata['buyer_id'] = $reship['member_id'];
            $returndata['reship_type'] = $reship['delivery'];
            $returndata['reship_fee'] = $reship['money'];
            $returndata['logistics_id'] = $reship['logi_id'];
            $returndata['logistics_company'] = $reship['logi_name'];
            $returndata['logistics_no'] = $reship['logi_no'];
            $returndata['receiver_name'] = $reship['ship_name'];
            $returndata['receiver_state'] = isset($area[0])?$area[0]:'';
            $returndata['receiver_city'] = isset($area[1])?$area[1]:'';
            $returndata['receiver_district'] = isset($area[2])?$area[2]:'';
            $returndata['receiver_address'] = $reship['ship_addr'];
            $returndata['receiver_zip'] = $reship['ship_zip'];
            $returndata['receiver_mobile'] = $reship['ship_mobile'];
            $returndata['receiver_phone'] = $reship['ship_tel'];
            $returndata['receiver_email'] = $reship['ship_email'];
            $returndata['t_begin'] = date('Y-m-d H:i:s', $reship['t_begin']);
            $returndata['status'] = strtoupper($reship['status']);
            $returndata['reship_operator'] = $reship['op_name'];
            $returndata['memo'] = $reship['memo'];

            //获取退货单商品明细
            $obj_reship_item = $this->app->model('reship_items');
            $filter = array(
                'reship_id'=>$reship['reship_id'],
            );
            $tmp = $obj_reship_item->getList('*',$filter);
            if ($tmp)
                $reship_itemlist = $tmp[0];
            else
                $reship_itemlist = array();

            if($reship_itemlist && count($reship_itemlist)>0)
            {
                foreach ($reship_itemlist as $k=>$v)
                {
                    $product_ids[] = $v['product_id'];
                    $reship_items[$v['product_id']] = array(
                        'sku_id'=>$v['product_id'],
                        'sku_type'=>$v['item_type'],
                        'name'=>$v['product_name'],
                        'bn'=>$v['product_bn'],
                        'number'=>$v['number'],
                    );

                    /** 退货单额外信息 **/
                    if (isset($v['extends']) && $v['extends'])
                    {
                        foreach ($v['extends'] as $key=>$items)
                        {
                            $reship_items[$v['product_id']][$key] = $items;
                        }
                    }
                    /** end **/
                }
                unset($reship_itemlist);
            }

            sort($reship_items);
            $returndata['reship_items']['reship_item'] = $reship_items;
            unset($reship_items);
            unset($reship);
        }
        else{
            $obj->send_user_error("退货单不存在", array('tid'=>$data['tid']));
        }
        return $returndata;
    }
}