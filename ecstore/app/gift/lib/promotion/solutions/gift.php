<?php

 
/**
 * 单商品减固定价格购买
 * $ 2010-05-04 16:51 $
 */
class gift_promotion_solutions_gift implements b2c_interface_promotion_solution
{
    public $name = "送赠品"; // 名称
    public $type = 'goods'; //默认goods
    public $desc_pre = '获得赠品';
    public $desc_post = '';
    public $score_add = true;
    
    //开启此项时。此优惠也会应用到商品以外。如购物车中只有礼包
    public $actiontoall = true; 
    
    private $description = '';
    
    
    

    /**
     * 优惠方案模板
     * @param array $aConfig // 设置信息(修改的时候传入)
     * @return string // 返回要输出的模板html
     */
    public function config($aData = array()) {
        $render = app::get('gift')->render();
        $render->pagedata['value'] = $aData['gain_gift'];

        
        $render->pagedata['isfront'] =$aData['isfront'];
        $render->pagedata['storeid_filter'] =$aData['storeid_filter']; 

        $render->pagedata['object'] = 'goods';
        $render->pagedata['name'] = 'action_solution[gift_promotion_solutions_gift][gain_gift]';
        return $this->desc_pre . $render->fetch('admin/sales/dialog.html');
    }

    /**
     * 优惠方案应用
     *
     * @param array $object  // 引用的一个商品信息
     * @param array $aConfig // 优惠的设置
     * @param array $cart_object // 购物车信息(预过滤的时候这个为null)
     * @return void // 引用处理了,没有返回值
     */
    public function apply(&$object,$aConfig,&$cart_object = null) {
        $t = $arr = $this->__process( $aConfig );
        if( !$arr ) return false;
        
        if( is_null($cart_object) || empty($cart_object) ) { // 商品预过滤
            $this->desc_pre = app::get('gift')->_('商品获得赠品');
            if( is_array( $object['gift'] ) ) {
                foreach( $object['gift'] as $_k => &$_v ) {
                    foreach( (array)$arr as $_key => $val ) {
                        if( $val['product_id']==$_v['product_id'] ) {
                            unset($arr[$_key]);
                            $_v['quantity'] += $val['quantity'];
                            $_v['rule_id'] = array_merge( (array)$_v['rule_id'],(array)$val['rule_id'] );
                        }
                    }
                }
            }
            $object['gift'] = array_merge((array)$object['gift'],$arr);
        } else {// 购物车里的处理
            $this->desc_pre = app::get('gift')->_('获得赠品');
            if( is_array( $cart_object['object']['gift']['order'] ) ) {
                foreach( $cart_object['object']['gift']['order'] as $_k => &$_v ) {
                    foreach( (array)$arr as $_key => $val ) {
                        if( $val['product_id']==$_v['product_id'] ) {
                            unset($arr[$_key]);
                            $_v['quantity'] += $val['quantity'];
                            $_v['rule_id'] = array_merge( (array)$_v['rule_id'],(array)$val['rule_id'] );
                        }
                    }
                }
            }
            $cart_object['object']['gift']['order'] = array_merge((array)$cart_object['object']['gift']['order'],$arr);
        }
        $this->setString($t);
        
    }
    
    
    
    
    /**
     * 优惠方案应用
     *
     * @param array $object  // 引用的一个商品信息
     * @param array $aConfig // 优惠的设置
     * @param array $cart_object // 购物车信息(预过滤的时候这个为null)
     * @return void // 引用处理了,没有返回值
     */
    public function apply_order(&$object, &$aConfig,&$cart_object = null) {
        
        if(is_null($cart_object)) return false;
        
        $t = $arr = $this->__process( $aConfig );
        if( !$arr ) return false;
        //送赠品 如果该赠品已存在则累加
        if( is_array( $cart_object['object']['gift']['order'] ) ) {
            foreach( $cart_object['object']['gift']['order'] as $_k => &$_v ) {
                foreach( (array)$arr as $_key => $val ) {
                    if( $val['product_id']==$_v['product_id'] ) {
                        unset($arr[$_key]);
                        $_v['quantity'] += $val['quantity'];
                        $_v['rule_id'] = array_merge( (array)$_v['rule_id'],(array)$val['rule_id'] );
                    }
                }
            }
        }
        $cart_object['object']['gift']['order'] = array_merge((array)$cart_object['object']['gift']['order'],$arr);
        
        
        $this->desc_pre = app::get('gift')->_('订单获得赠品');
        $this->setString($t);
    }
    
    
    private function __process( &$aConfig ) {
        if(!isset($aConfig['gain_gift']) || empty($aConfig['gain_gift'])) return false;
        $arr = kernel::single("gift_cart_object_goods")->_get_products($aConfig['gain_gift']);
        foreach( $aConfig['gain_gift'] as $_gift_id ) {
            if( !$arr[$_gift_id] ) continue;
            $t = $arr[$_gift_id];
            if( !($t['store']-$t['freez']) || !$t['_limit'] ) continue;
            $arr[$_gift_id]['rule_id'][] = $this->rule_id;
            $tmp[] = $arr[$_gift_id];
        }
        $this->rule_id = null;
        return $tmp;
        #$cart_object['object']['gift']['order'] = array_merge( (array)$cart_object['object']['gift']['order'], $arr );
    }
    
    
    public function setString($aData) {
        if( ($tmp=$this->__process($aData)) ) {
            $aData = $tmp;
        }
        
        if( is_array($aData) ) {
            $a = array();
            foreach( $aData as $row ) {
                if( !is_array($row) || !$row['name'] ) continue;
                $a[] = '<a href="'. app::get('site')->router()->gen_url( array('app'=>'gift','act'=>'index','ctl'=>'site_gift','arg0'=>$row['goods_id']) ) .'" target="_gift_blank">'.$row['name'].'</a>';
            }
            
            if( $a && is_array($a) )
                $str = implode('<br />', $a);
        }
        
        if( $str )
            $this->description = $this->desc_pre . $str;
        else 
            $this->description = '';
    }
    
    public function getString($aData=array()) {
        return $this->description;
    }
    
    public function get_status() {
        return true;
    }
    
	/**
	 * 校验参数是否正确
	 * @param mixed 需要校验的参数
	 * @param string error message
	 * @return boolean 是否成功
	 */
	public function verify_form($data=array(), &$msg='')
	{
		if (!$data) return true;
		
		/** 订单够满金额 **/
		if (!isset($data['action_solution']['gift_promotion_solutions_gift']['gain_gift']) || !$data['action_solution']['gift_promotion_solutions_gift']['gain_gift'])
		{
			$msg = app::get('b2c')->_('请指定需要赠送的赠品！');
			return false;
		}
		/** end **/
		
		return true;
	}
}

