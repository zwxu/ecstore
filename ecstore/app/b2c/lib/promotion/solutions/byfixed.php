<?php

 
/**
 * 单商品减固定价格购买
 * $ 2010-05-04 16:51 $
 */
class b2c_promotion_solutions_byfixed implements b2c_interface_promotion_solution
{
    public $name = "减固定价格购买"; // 名称
    public $type = 'goods'; //默认goods
    public $desc_pre = '价格优惠';
    public $desc_post = '出售';
    private $description = '';
    
    //同种方案在同一商品上 适用 排他原则
    public $stop_rule_with_same_solution = true; 
    
    
    public function __construct( $app ) {
        $this->app = $app;
        $this->name = app::get('b2c')->_($this->name);
        $this->desc_pre = app::get('b2c')->_($this->desc_pre);
        $this->desc_post = app::get('b2c')->_($this->desc_post);
    }
    

    /**
     * 优惠方案模板
     * @param array $aConfig // 设置信息(修改的时候传入)
     * @return string // 返回要输出的模板html
     */
    public function config($aData = array()) {
        return <<<EOF
{$this->desc_pre}<input name="action_solution[b2c_promotion_solutions_byfixed][total_amount]" vtype='required&&unsigned' value="{$aData['total_amount']}" />{$this->desc_post}
EOF;
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
        #print_r($aConfig);echo "<HR>";
        if(is_null($cart_object)) { // 商品预过滤
            
            $object['obj_items']['products'][0]['price']['buy_price'] = $object['obj_items']['products'][0]['price']['buy_price'] - $aConfig['total_amount'];
            $object['obj_items']['products'][0]['price']['buy_price'] = max(0,$object['obj_items']['products'][0]['price']['buy_price']);
            $this->desc_pre = app::get('b2c')->_('商品价格优惠');
        } else {// 购物车里的处理
            $productQuantity = $object['obj_items']['products'][0]['quantity'];
            $goodsQuantity = $object['quantity'];
            $qty = $productQuantity * $goodsQuantity;
            $productPrice = $object['obj_items']['products'][0]['price']['buy_price'];
            $discountAmount    = min(($qty*$aConfig['total_amount']),($productPrice*$qty));
            $object['discount_amount_order'] += max(0, $discountAmount);
            $this->desc_pre = app::get('b2c')->_('价格优惠');
        }
        
        $this->setString($aConfig);
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
        
        $object['discount_amount_order'] += $aConfig['total_amount'];
        $this->desc_pre = app::get('b2c')->_('订单总价格优惠');
        $this->setString($aConfig);
    }
    
    
    public function setString($aData) {
        $this->description = $this->desc_pre . kernel::single('ectools_mdl_currency')->changer($aData['total_amount']) . $this->desc_post;
    }
    
    public function getString() {
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
		if (!isset($data['action_solution']['b2c_promotion_solutions_byfixed']['total_amount']) || !$data['action_solution']['b2c_promotion_solutions_byfixed']['total_amount'])
		{
			$msg = app::get('b2c')->_('请指定订单优惠出售的金额！');
			return false;
		}
		
		if (!preg_match('/^\d*$/', $data['action_solution']['b2c_promotion_solutions_byfixed']['total_amount']) || $data['action_solution']['b2c_promotion_solutions_byfixed']['total_amount'] <= 0)
		{
			$msg = app::get('b2c')->_('提交的金额不是数字或者金额小于0了！');
			return false;
		}
		/** end **/
		
		return true;
	}
}
