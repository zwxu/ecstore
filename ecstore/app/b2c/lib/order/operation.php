<?php



/**
 * 定义order操作的抽象类
 * 主要实现接口ectools_interface_order_operaction的freezeGoods和unfreezeGoods的方法
 * ectools payment operation version 0.1
 */
abstract class b2c_order_operation implements b2c_interface_order_operation
{
    // 对应的应用对象
    protected $app;

    // 对象实体
    protected $model;

    public function freezeGoods($order_id)
    {
        $is_freeze = false;
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $this->app->model('orders')->dump($order_id, 'order_id,status,pay_status,ship_status', $subsdf);

        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        foreach($sdf_order['order_objects'] as $k => $v)
        {
            foreach ($v['order_items'] as $arrItem)
            {
                $arr_params = array(
								'goods_id' => $arrItem['products']['goods_id'],
								'product_id' => $arrItem['products']['product_id'],
								'quantity' => $arrItem['quantity'],
							);
				if ($arrItem['item_type'] == 'product')
					$arrItem['item_type'] = 'goods';
				$str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
				$is_freeze = $str_service_goods_type_obj->freezeGoods($arr_params);
            }
        }

        return $is_freeze;
    }

    public function unfreezeGoods($order_id)
    {
        $is_unfreeze = true;
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $this->app->model('orders')->dump($order_id, 'order_id,status,pay_status,ship_status', $subsdf);

        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        foreach($sdf_order['order_objects'] as $k => $v)
        {
            foreach ($v['order_items'] as $arrItem)
            {
                $arr_params = array(
								'goods_id' => $arrItem['products']['goods_id'],
								'product_id' => $arrItem['products']['product_id'],
								'quantity' => $arrItem['quantity'],
							);
				if ($arrItem['item_type'] == 'product')
					$arrItem['item_type'] = 'goods';
				$str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
				$is_freeze = $str_service_goods_type_obj->unfreezeGoods($arr_params);
            }
        }

        return $is_unfreeze;
    }

    public function minus_stock(&$arrData)
    {
        $storage_enable = $this->app->getConf('site.storage.enabled');
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        if ($storage_enable != 'true')
        {
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
            $arrStatus = $obj_checkorder->checkOrderFreez('delivery', $arrData['order_id']);

            // 裁剪库存
            $products = $this->app->model('products');
            $objMath = kernel::single('ectools_math');

            $update_data = array();
            if ($arrData['item_type'] != 'gift')
            {
                if ($arrData['item_type'] == 'product')
                    $arrData['item_type'] = 'goods';

                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrData['item_type']];
                if ($arrStatus['store'])
                {
                    $arr_params = array(
                        'product_id' => $arrData['product_id'],
                        'number' => $arrData['number'],
                    );

                    $str_service_goods_type_obj->minus_store($arr_params);
                }

                if ($arrStatus['unfreez'])
                {
                    $arr_params = array(
                        'product_id' => $arrData['product_id'],
                        'quantity' => $arrData['number'],
                    );
                    $str_service_goods_type_obj->unfreezeGoods($arr_params);
                }
            }
        }

        if ($arrData['item_type'] == 'gift')
        {
            if (isset($arr_service_goods_type_obj['gift']) && $arr_service_goods_type_obj['gift'])
            {
                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrData['item_type']];
                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arrData['goods_id']), $arr_gift_goods);

                if (!is_null($arr_gift_goods['store']) || $arr_gift_goods['store'] === '')
                {
                    if ($arrStatus['store'])
                    {
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrData['item_type']];
                        $arr_params = array(
                            'product_id' => $arrData['product_id'],
                            'number' => $arrData['number'],
                        );
                        $str_service_goods_type_obj->minus_store($arr_params);
                    }

                    if ($arrStatus['unfreez'])
                    {
                        $arr_params = array(
                            'product_id' => $arrData['product_id'],
                            'quantity' => $arrData['number'],
                        );
                        $str_service_goods_type_obj->unfreezeGoods($arr_params);
                    }
                }
            }
        }

        return true;
    }

    public function restore_stock(&$arrData)
    {
        $storage_enable = $this->app->getConf('site.storage.enabled');
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        if ($storage_enable != 'true')
        {
            //更新库存
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
            $arrStatus = $obj_checkorder->checkOrderFreez('reship', $arrData['order_id']);

            if ($arrStatus['unstore'])
            {
                if ($arrData['item_type'] == 'product')
                    $arrData['item_type'] = 'goods';

                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrData['item_type']];
                $arr_params = array(
                    'product_id' => $arrData['product_id'],
                    'number' => $arrData['number'],
                );
                return $str_service_goods_type_obj->recover_store($arr_params);
            }
        }
        else
        {
            return true;
        }

        return false;
    }
}
