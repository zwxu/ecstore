<?php



/**
 * b2c order interactor with center
 */
class b2c_api_basic_update_store
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
        $this->objMath = kernel::single("ectools_math"); 

        //店铺及货品校验 
        foreach(kernel::servicelist('business.api_verify_store') as $object)
        {
             if(is_object($object))
             {
                 if(method_exists($object,'checkStore'))
                 {
                    $result = $object->checkStore($_POST,$ergmsg);
                    if( $result==false){
                        echo $ergmsg;
                        exit;
                    }
                 }
             }
        }
       

    }


    /**
     * 库存修改
     * @param array sdf
     * @return boolean success of failure
     */
    public function updateStore(&$sdf, $thisObj)
    { 
       
        if (!isset($sdf['list_quantity']) || !$sdf['list_quantity'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('需要更新的货品的库存不存在！'), array());
        }
        else
        {
            $has_error = false;
            $arr_store = json_decode($sdf['list_quantity'], true);

            $product = $this->app->model('products');
            $obj_goods = $this->app->model('goods');
            $fail_products = array();
            if (isset($arr_store) && $arr_store)
            {
                foreach ($arr_store as $arr_product_info)
                {
                    if ($arr_product_info['bn'] && is_numeric($arr_product_info['quantity']))
                    {
                        $arr_product = $product->dump(array('bn' => $arr_product_info['bn']));
                        if ($arr_product)
                        {
                            $store_increased = $this->objMath->number_minus(array(floatval($arr_product_info['quantity']), floatval($arr_product['store'])));
                            $arr_goods = $obj_goods->dump($arr_product['goods_id'], 'goods_id,store');
                            $arr_goods['store'] = ($this->objMath->number_plus(array($arr_goods['store'], $store_increased)) == '0') ? 0 : $this->objMath->number_plus(array($arr_goods['store'], $store_increased));
                            $arr_product['store'] = $arr_product_info['quantity'];
                            $storage_enable = $this->app->getConf('site.storage.enabled');
                            if (!is_null($arr_product['store']) && $storage_enable != 'true')
                            {
                                $is_save = $product->save($arr_product);
                                $obj_goods->update($arr_goods, array('goods_id' => $arr_goods['goods_id']));
                            }
                            else
                            {
                                $is_save = true;
                            }

                            if (!$is_save)
                            {
                                $msg = $this->app->_('商品库存更新失败！');
                                $has_error = true;

                                $fail_products[] = $arr_product_info['bn'];

                                continue;
                            }
                        }
                        else
                        {
                            $has_error = true;

                            $fail_products[] = $arr_product_info['bn'];

                            continue;
                        }
                    }
                    else
                    {
                        $has_error = true;
                        continue;
                    }
                }

                if (!$has_error)
                    return true;
                else
                {
                    // 更新部分失败.
                    $fail_products = array('error_response' => $fail_products);
                    $thisObj->send_user_error(app::get('b2c')->_('更新库存部分失败！'), $fail_products);
                }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('更新的商品的库存信息不存在！'), array());
            }
        }
    }

    /**
     * 冻结库存请0
     * @param array sdf
     * @return boolean success of failure
    */
    public function updateFreezStore(&$sdf, $thisObj)
    {
        if (!isset($sdf['order_bn']) || !$sdf['order_bn'])
        {
            trigger_error(app::get('b2c')->_('订单标号不存在！'), E_USER_ERROR);
        }
        else
        {
            $obj_orders = $this->app->model('orders');
            $goods = $this->app->model('goods');
            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $sdf_order = $obj_orders->dump($sdf['order_bn'], '*', $subsdf);
            $stock_freez_time = $this->app->getConf('system.goods.freez.time');

            if ($stock_freez_time == '1')
            {
                // 清除预占库存
                if ($sdf_order['order_objects'])
                {
                    foreach ($sdf_order['order_objects'] as $arr_sdf_objs)
                    {
                        if ($arr_sdf_objs['order_items'])
                        {
                            foreach ($arr_sdf_objs['order_items'] as $arr_sdf_items)
                            {
                                $goods->unfreez($arr_sdf_items['products']['goods_id'], $arr_sdf_items['products']['product_id'], $arr_sdf_items['quantity']);
                            }
                        }
                    }
                }
            }

            return array('tid'=>$sdf['order_bn']);
        }
    }
}