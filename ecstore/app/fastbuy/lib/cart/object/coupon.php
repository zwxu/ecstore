<?php

 
/**
 * 购物车项处理(优惠券)
 * $ 2010-05-25 14:09 $
 */
class fastbuy_cart_object_coupon extends b2c_cart_object_coupon{

    private $app;
    private $member_ident; // 用户标识
    private $oCartObject;

    /**
     * 构造函数
     *
     * @param $object $app  // service 调用必须的
     */
    public function __construct() {
        $this->app = app::get('b2c');
        
        $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        $this->member_ident = kernel::single("base_session")->sess_id();
        
        $this->oCartObjects = $this->app->model('cart_objects');
    }
    
    /**
	 * 购物车是否需要验证库存
	 * @param null
	 * @return boolean true or false
	 */
	public function need_validate_store() {
		return true;
	}
    
    public function get_type() {
        return 'coupon';
    }
	
	public function get_part_type() {
		return array('coupon');
	}
	
	/**
	 * 处理加入购物车商品的数据
	 * @param mixed array 传入的参数
	 * @return mixed array 处理后的数据
	 */
	public function get_data($params=array())
	{
		return $params;
	}
	
	/**
	 * 得到失败应该返回的url - app 数组
	 * @param array
	 * @return array
	 */
	public function get_fail_url($data=array())
	{
		return array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'checkout');
	}
	
	/**
	 * 校验加入购物车数据是否符合要求-各种类型的数据的特殊性校验
	 * @param array 加入购物车数据
	 * @param string message 引用值
	 * @return boolean true or false
	 */
	public function check_object($arr_data,&$msg='')
	{
		if(empty($arr_data) || empty($arr_data['coupon']))
		{
			$msg = app::get('b2c')->_('优惠券为空！');
			return false;
		}
        
		if (!$this->app->model("coupons")->verify_coupons($arr_data)){
			$msg = app::get('b2c')->_('优惠券添加失败！');
			return false;
		}
		return true;
	}
	
	/**
	 * 检查库存
	 * @param array 加入购物车的商品结构
	 * @param array 现有购物车的数量
	 * @param string message
	 * @return boolean true or false
	 */
	public function check_store($arr_data, $arr_carts, &$msg='')
	{
		return true;
	}
	
	/**
	 * 添加购物车项(coupon)
	 * @param array $aData // array(
     *                          'goods_id'=>'xxxx',   // 商品编号
     *                          'product_id'=>'xxxx', // 货品编号
     *                          'adjunct'=>'xxxx',    // 配件信息
     *                          'quantity'=>'xxxx',   // 购买数量
     *                        )
	 * @param string message
	 * @return boolean
	 */
	public function add_object($aData, &$msg='', $append=true)
	{	//echo '<pre>';print_r($aData);exit;
        $objIdent = $this->_generateIdent($aData);
        $aCouponRule = $this->app->model('coupons')->getCouponByCouponCode($aData['coupon']);
        $arr = $this->app->model('sales_rule_order')->getList( '*',array('rule_id'=>$aCouponRule[0]['rule_id']) );
        if( !$arr || !is_array($arr) ) {
			$msg = app::get('b2c')->_('优惠券信息错误！');
			return false;
		}
        reset( $arr );
        $arr = current( $arr );
        if( $arr['status']!=='true' ) {
			$msg = app::get('b2c')->_('该优惠券不能使用！！活动未开启！');
			return false;
		}
    
        $aSave = array(
		   'obj_ident'    => $objIdent,
		   'member_ident' => $this->member_ident,
		   'obj_type'     => 'coupon',
		   'params'       => array(
								'name'  =>  $aData['coupon'],
								'rule_id'   => $aCouponRule[0]['rule_id'],
								'cpns_id'   => $aCouponRule[0]['cpns_id'],
								'cpns_type' => $aCouponRule[0]['cpns_type'],
								'extends_params' => $aData['extends_params'],
								'store_id' => $aData['store_id'],
							),
		   'quantity'     => 1,  // 一张优惠券只能使用一次不能叠加
		 );
        
        if(true) {
            $this->coupon_object[$aSave['obj_ident']] = $aSave;
            return $aSave['obj_ident'];
            //todo
        }; //no database
        
		$is_save = $this->oCartObjects->save($aSave);
		if (!$is_save){
			$msg = app::get('b2c')->_('优惠券使用失败！');
			return false;
		}
        return $aSave['obj_ident'];
	}

    // 优惠券没有更新这一说
    public function update($sIdent,$quantity) {
        return false;
    }

    /**
     * 指定的购物车优惠券
     *
     * @param string $sIdent
     * @param boolean $rich        // 是否只取cart_objects中的数据 还是完整的sdf数据
     * @return array
     */
    public function get($sIdent = null,$rich = false) {
        if(empty($sIdent)) return $this->getAll($rich);
        
        $aResult = $this->oCartObjects->getList('*',array(
                                           'obj_ident' => $sIdent,
                                           'member_ident'=> $this->member_ident,
                                        ));
        if(empty($aResult)) return array();
        if($rich) {
            $aResult = $this->_get($aResult);
            $aResult = $aResult[0];
        }
        
        return $aResult;
    }

    public function _get($aData){
        // todo要从数据库中取出对应用的优惠券的描述
		$obj_sales_rule_order = $this->app->model('sales_rule_order');
        foreach($aData as $row) {
            $params = $row['params'];
			$tmp = $obj_sales_rule_order->getList('name', array('rule_id'=>$params['rule_id']));
			//echo '<pre>';print_r($params);exit;
            $aResult[] = array(
                            'obj_ident' => $row['obj_ident'],
                            'obj_type' => 'coupon',
                            'quantity' => 1,
                            'description' => '',
                            'coupon'=>$params['name'],
                            'rule_id' => $params['rule_id'],
                            'cpns_id'=> $params['cpns_id'],
                            'cpns_type'=> $params['cpns_type'],
							'name'=>$tmp[0]['name'],
							'store_id'=>$params['store_id'],//
                            'used' => false // 是否使用 order conditions时处理
                        );
        }
        return $aResult;
    }

    // 购物车里的所有优惠券
    public function getAll($rich = false) {
        
        if(true) {
            $aResult = $this->coupon_object;
        } else {
            $aResult= $this->oCartObjects->getList('*',array(
                                               'obj_type' => 'coupon',
                                               'member_ident'=> $this->member_ident,
                                           ));
        }
        if(empty($aResult)) return array();
        if(!$rich) return $aResult;
        return $this->_get($aResult);
    }

    // 删除购物车中指定优惠券
    public function delete($sIdent = null) {
        if(empty($sIdent)) return $this->deleteAll();
        // todo 如果dbeav中有delete方法邓 再悠修改下面
        return $this->oCartObjects->delete(array('member_ident'=>$this->member_ident, 'obj_ident'=>$sIdent, 'obj_type'=>'coupon'));
    }

    // 清空购物车中优惠券数据
    public function deleteAll() {
        return $this->oCartObjects->delete(array('member_ident'=>$this->member_ident, 'obj_type'=>'coupon'));
    }

    // 统计购物车中优惠券数据
    public function count(&$aData) {}

    // todo 优惠券添加到购物车中的数据检测在这里处理
    // 优惠券的正确性 类型 是否已使用
    private function _check(&$aData) {
        if(empty($aData) || empty($aData['coupon'])) return array('status'=>'false','msg'=>'优惠券为空！');;
        return $this->app->model("coupons")->verify_coupons($aData);
        
        // 通过 $aData['coupon'] 验证coupon的有效性

        return true;
    }

    private function _generateIdent($aData) {
        return "coupon_".$aData['coupon'];# .'_'. ( $this->arr_member_info['member_id'] ? $this->arr_member_info['member_id'] : 0 );
    }
    
    
    public function apply_to_disabled( $data,$session,$flag ) {
        return $data;
    }
}
