<?php

 
/**
 * 购物车项model
 * $ 2010-04-28 20:02 $
 */
class b2c_cart_objects {
    private $__member_id = null;
        
        
    function __construct( &$app ) {
        $this->app = $app;
    }
    
    public function md5_cart_objects() {
        $arr = $this->app->model('cart')->get_basic_objects();
        $md5 = utils::array_md5($arr);
        return $md5;
    }
    
    
    //购物车会员信息统一接口
   public function get_current_member( $memberid=null ) {
       if( !$memberid ) $memberid = $this->__member_id;
	   $obj_members = $this->app->model('members');
       if( $memberid ) {
           if( $memberid=='-1' ) {
                $this->memberinfo[$memberid]['member_id'] = '-1';
                $this->memberinfo[$memberid]['uname'] =  '';
                $this->memberinfo[$memberid]['name'] = '';
                $this->memberinfo[$memberid]['sex'] =  '';
                $this->memberinfo[$memberid]['point'] = 0;
                $this->memberinfo[$memberid]['usage_point'] = 0;
           } else {
               if( !$this->memberinfo[$memberid] )
                   $this->memberinfo[$memberid] = $obj_members->get_member_info( $memberid );
               
           }
           return $this->memberinfo[$memberid];
       } else {
           return $obj_members->get_current_member();
       }
   }
   
   public function set_member_id( $memberid ) {
       $this->__member_id = $memberid;
   }
   
   /**
    * 检查购物车库存数量
	* @param object 商品处理的类型
	* @param array 将要加入数据库的商品
	* @param 
	* @return boolean true or false
	*/
	public function check_store($obj, $Data=array(), &$msg='')
	{
		if (!$obj || !$Data)
			return true;
		
		if (!method_exists($obj, 'check_store')) return true;
		$arr_carts = $this->get_cart_object_list_groupby_product_id();
		if (!$obj->check_store($Data, $arr_carts, $msg)) return false;
		
		return true;
	}
	
   /**
	 * 取出购物车已有商品的信息（包括商品的goods_id,product_id和quantity-购买数量）
	 * @param null
	 * @return boolean true or false
	 */
	private function get_cart_object_list_groupby_product_id()
	{
		$goods_info=array();
		$oCartObjects = $this->app->model('cart_objects');
		if (!$tmp_cart_object = $oCartObjects->getList('*')) 
		{
			return $goods_info;
		}
		
		$arr_objects = array();
		if ($objs = kernel::servicelist('b2c_cart_object_apps'))
		{
			foreach ($objs as $obj)
			{
				if ($obj->need_validate_store())
					$arr_objects[$obj->get_type()] = $obj;
			}
		}
			
		foreach ($tmp_cart_object as $arr)
		{
			if ($arr_objects[$arr['obj_type']] && method_exists($arr_objects[$arr['obj_type']],'generate_cart_object_products'))
			{
				$arr_objects[$arr['obj_type']]->generate_cart_object_products($arr, $goods_info);
			}

		}

		return $goods_info;
	}
	
	/**
	 * 统一添加购物车按钮
	 * @param object 处理的对象
	 * @param array 单据信息的数组
	 * @param string message
	 * @return null
	 */
	public function add_object($obj, $arr_data, &$msg='')
	{
		if (!$obj || !$arr_data) return;
		
		if (!method_exists($obj, 'add_object')) return;
		
		return $obj->add_object($arr_data, $msg);
	}
	
	/**
	 * 得到对应的购物车项的内容
	 * @param string ident
	 * @param object 购物车内容实例
	 * @param mixed 购物车项的内容
	 * @return boolean false时候为不存在
	 */
	public function get_object($ident,$obj,&$arr_obj=array())
	{
		if (!$obj || !$ident) return false;
		
		if (!method_exists($obj, 'get')) return false;
		
		$arr_obj = $obj->get($ident,true);
		return true;
	}
}
