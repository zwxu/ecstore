<?php

 
class gift_order_service_gift implements b2c_order_service_interface
{
    /**
     * 构造方法
     * @param object application object
     * @return null
     */
    public function __construct(&$app)
    {
        $this->app = $app;
    }
    /**
     * 获取货品类型
     *
     * @return string 'gift
     */ 
    public function get_goods_type()
    {
        return 'gift';
    }
    /**
     * 冻结商品
     *
     * @param array $arrParams 商品信息数据
     * @return boolean 冻结成功返回true,失败返回false
     */
    public function freezeGoods($arrParams=array())
    {
        if (!$arrParams)
        {
            return false;
        }
            
        $is_freeze = false;
        $objGoods = $this->app->model('goods');
        if (isset($arrParams['goods_id']) && $arrParams['goods_id'])
            $is_freeze = $objGoods->freez($arrParams['goods_id'], $arrParams['product_id'], $arrParams['quantity']);
        else
        {
            $products = $this->app->model('products');
            $tmp = $products->getList('goods_id', array('product_id', $arrParams['product_id']));
            $arr_product = $tmp[0];
            
            $is_freeze = $objGoods->freez($arr_product['goods_id'], $arrParams['product_id'], $arrParams['quantity']);
        }
        
        return $is_freeze;
    }
    /**
     * 商品解冻
     *
     * @param array $arrParams 商品信息数组
     * @return boolean 解冻成功返回true,解冻失败返回false
     */
    public function unfreezeGoods($arrParams=array())
    {
        if (!$arrParams)
        {
            return false;
        }
            
        $is_unfreeze = false;
        $objGoods = $this->app->model('goods');
        if (isset($arrParams['goods_id']) && $arrParams['goods_id'])
            $is_unfreeze = $objGoods->unfreez($arrParams['goods_id'], $arrParams['product_id'], $arrParams['quantity']);
        else
        {
            $products = $this->app->model('products');
            $tmp = $products->getList('goods_id', array('product_id', $arrParams['product_id']));
            $arr_product = $tmp[0];
            
            $is_unfreeze = $objGoods->unfreez($arr_product['goods_id'], $arrParams['product_id'], $arrParams['quantity']);
        }
        
        return $is_unfreeze;
    }
    /**
     * 库存减去当前购买商品数量
     *
     * @param array 商品信息数组
     * @return boolean 成功返回true,失败返回false
     */
    public function minus_store($arrParams=array())
    {
        if (!$arrParams)
        {
            return false;
        }
            
        $objGoods = $this->app->model('goods');
        $products = $this->app->model('products');
        $tmp = $products->getList('goods_id,store', array('product_id'=>$arrParams['product_id']));
        $arr_product = $tmp[0];
        $tmp = $objGoods->getList('store,nostore_sell', array('goods_id'=>$arr_product['goods_id']));
        $arr_goods = $tmp[0];
        
        $objMath = kernel::single('ectools_math');
            
        if (!is_null($arr_product['store']) || $arr_product['store'] === '')
        {
            if ($objMath->number_minus(array($arr_product['store'], $arrParams['number'])) < 0)
                return false;
                
            $update_data = array(
                'store' => $objMath->number_minus(array($arr_product['store'], $arrParams['number'])),
            );
            
            $is_updated = $products->update($update_data, array('product_id'=>$arrParams['product_id']));
        }
        else
        {
            $is_updated = true;
        }
        
        if ($is_updated) 
        {
            if (!is_null($arr_goods['store']) || $arr_goods['store'] === '')
            {
                $update_data = array(
                    'store' => $objMath->number_minus(array($arr_goods['store'], $arrParams['number'])),
                );
                $objGoods->unuse_filter_default();
                if ($objGoods->update($update_data, array('goods_id'=>$arr_product['goods_id'])))
                    return true;
                else
                    return false;
            }
        }
        else
        {
            return false;
        }
    }
    /**
     * 库存加上给定商品信息数组数量
     *
     * @param array 商品信息数组
     * @return boolean 成功返回true,失败返回false
     */
    public function recover_store($arrParams=array())
    {
        if (!$arrParams)
        {
            return false;
        }
            
        $objGoods = $this->app->model('goods');
        $products = $this->app->model('products');
        $tmp = $products->getList('goods_id,store', array('product_id'=>$arrParams['product_id']));
        $arr_product = $tmp[0];
        $tmp = $objGoods->getList('store,nostore_sell', array('goods_id'=>$arr_product['goods_id']));
        $arr_goods = $tmp[0];
        if ($arr_goods['nostore_sell'])
            return true;
        $objMath = kernel::single('ectools_math');
            
        if (!is_null($arr_product['store']) || $arr_product['store'] === '')
        {
            $update_data = array(
                'store' => $objMath->number_plus(array($arr_product['store'], $arrParams['number'])),
            );
            
            $is_updated = $products->update($update_data, array('product_id'=>$arrParams['product_id']));
        }
        else
        {
            $is_updated = true;
        }
        
        if ($is_updated) 
        {
            if (!is_null($arr_goods['store']) || $arr_goods['store'] === '')
            {
                $update_data = array(
                    'store' => $objMath->number_plus(array($arr_goods['store'], $arrParams['number'])),
                );
                
                if ($objGoods->update($update_data, array('goods_id'=>$arr_product['goods_id'])))
                    return true;
                else
                    return false;
            }
        }
        else
        {
            return false;
        }
    }
    /**
     * 生成订单数据
     *
     * @param array $arrParams 赠品信息
     * @param array &$order_data 订单信息，此处处理后引用返回
     * @param string &$msg 提示信息
     * @return boolean 
     */
    public function gen_order($arrParams=array(), &$order_data, &$msg='')
    {        
        // 赠品...        
        $index = count($order_data['order_objects']);
        $obj_spec_values = app::get('b2c')->model('spec_values');
        $obj_specification = app::get('b2c')->model('specification');
        $objMath = kernel::single('ectools_math');
        $store_mark = app::get('b2c')->getConf('system.goods.freez.time');
		$is_freez = true;
        
        // 订单赠送的赠品...
        if (isset($arrParams['order']) && $arrParams['order'])
        {
            foreach ($arrParams['order'] as $arr_gift_info)
            {
                $strAddon = "";
                $arrAddon = array();
                if (isset($arr_gift_info['spec_desc']) && $arr_gift_info['spec_desc'] && is_array($arr_gift_info['spec_desc']))
                {
                    foreach ($arr_gift_info['spec_desc']['spec_value_id'] as $spec_key=>$str_spec_value_id)
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
                
                $order_data['order_objects'][$index++] = array(
                    'order_id' => $order_data['order_id'],
                    'obj_type' => 'gift',
                    'obj_alias' => app::get('b2c')->_('赠品区块'),
                    'goods_id' => $arr_gift_info['goods_id'],
                    'bn' => $arr_gift_info['bn'],
                    'name' => $arr_gift_info['name'],
                    'price' => $arr_gift_info['price']['price'],
                    'quantity'=> $arr_gift_info['quantity'],
                    'amount'=> $objMath->number_multiple(array($arr_gift_info['price']['buy_price'], $arr_gift_info['quantity'])),
                    'weight'=> $arr_gift_info['weight'],
                    'score'=> $objMath->number_multiple(array($arr_gift_info['consume_score'], $arr_gift_info['quantity'])),
                    'order_items' => array(
                        array(
                            'products' => array('product_id'=>$arr_gift_info['product_id']),
                            'goods_id'=> $arr_gift_info['goods_id'],
                            'order_id' => $order_data['order_id'],
                            'item_type'=>'gift',
                            'bn'=> $arr_gift_info['bn'],
                            'name'=> $arr_gift_info['name'],
                            'type_id'=> ($arr_gift_info['type_id'] ? $arr_gift_info['type_id'] : 0),
                            'cost'=> $arr_gift_info['price']['cost'],
                            'quantity'=> $arr_gift_info['quantity'],
                            'sendnum'=>0,
                            'amount'=> $objMath->number_multiple(array($arr_gift_info['price']['buy_price'], $arr_gift_info['quantity'])),
                            'score' => $arr_gift_info['consume_score'],
                            'price'=> $arr_gift_info['price']['buy_price'],
							'g_price'=>$arr_gift_info['price']['buy_price'],
                            'weight'=> $arr_gift_info['weight'],
                            'addon'=> $strAddon,
                        ),
                    ),
                );
                
                // 冻结库存...
                $arr_params = array(
                    'goods_id' => $arr_gift_info['goods_id'],
                    'product_id' => $arr_gift_info['product_id'],
                    'quantity' => $arr_gift_info['quantity'],
                );
                if ($store_mark == '1')
				{
                    $is_freez = $this->freezeGoods($arr_params);
					if (!$is_freez)
					{
						$msg = app::get('b2c')->_('库存冻结失败！');
						return false;
					}
				}
            }
        }
        
        // 积分兑换的赠品...
        if (isset($arrParams['cart']) && $arrParams['cart'])
        {
            foreach ($arrParams['cart'] as $arr_gift_info)
            {
                $strAddon = "";
                $arrAddon = array();
                if (isset($arr_gift_info['spec_desc']) && $arr_gift_info['spec_desc'] && is_array($arr_gift_info['spec_desc']))
                {
                    foreach ($arr_gift_info['spec_desc']['spec_value_id'] as $spec_key=>$str_spec_value_id)
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
                
                $order_data['order_objects'][$index++] = array(
                    'order_id' => $order_data['order_id'],
                    'obj_type' => 'gift',
                    'obj_alias' => app::get('b2c')->_('商品区块'),
                    'goods_id' => $arr_gift_info['goods_id'],
                    'bn' => $arr_gift_info['bn'],
                    'name' => $arr_gift_info['name'],
                    'price' => $arr_gift_info['price']['buy_price'],
                    'quantity'=> $arr_gift_info['quantity'],
                    'amount'=> $objMath->number_multiple(array($arr_gift_info['price']['buy_price'], $arr_gift_info['quantity'])),
                    'weight'=> $arr_gift_info['weight'],
                    'score'=> $objMath->number_multiple(array($arr_gift_info['consume_score'], $arr_gift_info['quantity'])),
                    'order_items' => array(
                        array(
                            'products' => array('product_id'=>$arr_gift_info['product_id']),
                            'goods_id'=> $arr_gift_info['goods_id'],
                            'order_id' => $order_data['order_id'],
                            'item_type'=>'gift',
                            'bn'=> $arr_gift_info['bn'],
                            'name'=> $arr_gift_info['name'],
                            'type_id'=> ($arr_gift_info['type_id'] ? $arr_gift_info['type_id'] : 0),
                            'cost'=> $arr_gift_info['price']['cost'],
                            'quantity'=> $arr_gift_info['quantity'],
                            'sendnum'=>0,
                            'amount'=>$objMath->number_multiple(array($arr_gift_info['price']['buy_price'], $arr_gift_info['quantity'])),
                            'score' => $arr_gift_info['consume_score'],
                            'price'=> $arr_gift_info['price']['buy_price'],
							'g_price'=>$arr_gift_info['price']['buy_price'],
                            'weight'=> $arr_gift_info['weight'],
                            'addon'=> $strAddon,
                        ),
                    ),
                );
                
                // 冻结库存...
                $arr_params = array(
                    'goods_id' => $arr_gift_info['goods_id'],
                    'product_id' => $arr_gift_info['product_id'],
                    'quantity' => $arr_gift_info['quantity'],
                );
                if ($store_mark == '1')
				{
                    $is_freez = $this->freezeGoods($arr_params);
					if (!$is_freez)
					{
						$msg = app::get('b2c')->_('库存冻结失败！');
						return false;
					}
				}
            }
        }
		return true;
    }
    /**
     * 根据订单信息取出相关商品信息
     *
     * @param array $arr_object 订单信息，主要是goods_id及product_id
     * @param array &$arrGoods 商品信息
     * @param string $tml 暂未使用
     * @return boolean
     */
    public function get_order_object($arr_object=array(), &$arrGoods, $tml='member_order_detail')
    {
        $objGoods = $this->app->model('goods');
        $arrGoods = $objGoods->dump($arr_object['goods_id'], '*', 'default');
        if ($arrGoods)
            $arrGoods['link_url'] = $this->app->controller('site_gift')->gen_url(array('app'=>'gift','ctl'=>'site_gift','act'=>'index','arg0'=>$arr_object['goods_id']));
        
        $objProducts = $this->app->model('products');
        $arrProducts = $objProducts->dump($arr_object['product_id']);
        $arrGoods['products'] = $arrProducts;
        
        if ($arrGoods)
            return true;
        else
            return false;
    }
    /**
     * 组合定单相关信息
     *
     * @param array $val_list 商品信息数组
     * @param array &$data 订单信息数组
     * @return void
     */
    public function get_default_dly_order_info($val_list=array(), &$data)
    {
        //订单商品名称
        $data['order_name'] .= app::get('b2c')->_('名称：%s',$val_list['name']);
        //订单商品名称+数量                            
        $data['order_name_a'] .= app::get('b2c')->_('名称：%s&nbsp;&nbsp;数量：%s',$val_list['name'],$val_list['quantity'])."\n";
        //订单商品名称+规格+数量
        $data['order_name_as'].= app::get('b2c')->_('名称：%s&nbsp;&nbsp;规格：%s&nbsp;&nbsp;数量：%s',$val_list['name'],$val_list['products']['spec_info'],$val_list['quantity'])."\n";
        //订单商品名称+货号+数量
        $data['order_name_ab'].= app::get('b2c')->_("名称：%s&nbsp;&nbsp;货号：%s&nbsp;&nbsp;数量：%s",$val_list['name'],$val_list['products']['bn'],$val_list['quantity'])."\n";
    }
    /**
     * 检查商品是否冻结
     *
     * @param array $arrParams 商品信息数组
     * @return boolean
     */
	public function check_freez($arrParams)
	{
		if (!$arrParams)
        {
            return true;
        }
            
        $is_freeze = true;
        $objGoods = $this->app->model('goods');
        if (isset($arrParams['goods_id']) && $arrParams['goods_id'])
            $is_freeze = $objGoods->check_freez($arrParams['goods_id'], $arrParams['product_id'], $arrParams['quantity']);
        else
        {
            $products = $this->app->model('products');
            $tmp = $products->getList('goods_id', array('product_id', $arrParams['product_id']));
            $arr_product = $tmp[0];
            
            $is_freeze = $objGoods->check_freez($arr_product['goods_id'], $arrParams['product_id'], $arrParams['quantity']);
        }
        
        return $is_freeze;
	}
    /**
     * 获取售后信息
     *
     * @param array $arr_data 商品信息数组
     * @return array
     */
	public function get_aftersales_order_info($arr_data)
	{
		/** 赠品不参加售后 **/
		return array();
	}
	
	public function is_decomposition($goods_type)
	{
		return true;
	}
	
	public function is_item_edit($item_type)
	{
		return false;
	}
}
