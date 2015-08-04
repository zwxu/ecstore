<?php

class b2c_order_check_products
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = app::get('b2c');
    }

    public function check_products($data,&$msg)
    {

    	if (!$data || !$data['order_objects']){
    		return true;
    	}
		
		if($data['store_id']){
			$oGoods = $this->app->model('goods');
			foreach($data['order_objects'] as $k=>$v){
				$goods_ids[] = $v['goods_id'];
			}
			$store_ids = $oGoods->getList('store_id',array('goods_id'=>$goods_ids));
			$arr = array_map('current',$store_ids);
			$arr = array_unique($arr);
			if(count($arr)>1){
				$msg = '订单数据异常，请重新下单';
				return false;
			}
			foreach($store_ids as $k=>$v){
				if($data['store_id']!=$v['store_id']){
					$msg = '订单数据异常，请重新下单';
					return false;
				}
			}
		}else{
			$msg = '订单数据异常，请重新下单';
			return false;
		}
		
        return true;
    }

	
}
