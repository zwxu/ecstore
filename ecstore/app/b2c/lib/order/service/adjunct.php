<?php

 
class b2c_order_service_adjunct implements b2c_order_service_interface
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
    
    public function get_goods_type()
    {
        return 'adjunct';
    }
    
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
    
    public function gen_order($arrParams=array(), &$arr_data, &$msg='')
    {
        
    }
    
    public function get_order_object($arr_object=array(), &$arrGoods, $tml='member_order_detail')
    {
        $objGoods = $this->app->model('goods');
        $arrGoods = $objGoods->dump($arr_object['goods_id'], '*', 'default');
        if ($arrGoods)
            $arrGoods['link_url'] = $this->app->controller('site_product')->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_object['goods_id']));
        
        $objProducts = $this->app->model('products');
        $arrProducts = $objProducts->dump($arr_object['product_id']);
        $arrGoods['products'] = $arrProducts;
        
        if ($arrGoods)
            return true;
        else
            return false;
    }
    
    public function get_default_dly_order_info($val_list=array(), &$data)
    {
        //订单商品名称
        $data['order_name'] .= '名称：'.$val_list['name'];
        //订单商品名称+数量                            
        $data['order_name_a'] .= '名称：'.$val_list['name']."&nbsp;&nbsp;数量：".$val_list['quantity']."\n";
        //订单商品名称+规格+数量
        $data['order_name_as'].= '名称：'.$val_list['name']."&nbsp;&nbsp;规格：" . $val_list['products']['spec_info'] . "&nbsp;&nbsp;数量：".$val_list['quantity']."\n";
        //订单商品名称+货号+数量
        $data['order_name_ab'].= "名称：".$val_list['name']."&nbsp;&nbsp;货号：" . $val_list['products']['bn'] . "&nbsp;&nbsp;数量：".$val_list['quantity']."\n";
    }
	
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
	
	public function get_aftersales_order_info($arr_data)
	{
		$arr_order_items = array();		
		if (!$arr_data)
			return $arr_order_items;
		
		$arr_order_items = $arr_data;
		if (!$arr_order_items['products'])
		{
			$o = $this->app->model('order_items');
			$tmp = $o->getList('*', array('item_id'=>$arr_order_items['item_id']));
			$arr_order_items['products']['product_id'] = $tmp[0]['product_id'];
		}
		
		if ($arr_order_items['addon'])
		{
			$arrAddon = $arr_addon = unserialize($arr_order_items['addon']);
			if ($arr_addon['product_attr'])
				unset($arr_addon['product_attr']);
			$arr_order_items['product']['minfo'] = $arr_addon;
		}
		
		if ($arrAddon['product_attr'])
		{
			foreach ($arrAddon['product_attr'] as $arr_product_attr)
			{
				$arr_order_items['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
			}
		}
		
		if ($arr_order_items['product']['attr'])
			$arr_order_items['name'] .= '(' . $arr_order_items['product']['attr'] . ')';
		
		return $arr_order_items;
	}
	
	public function is_decomposition($goods_type)
	{
		return true;
	}
	
	public function is_item_edit($item_type)
	{
		return true;
	}
}