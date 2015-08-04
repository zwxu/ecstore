<?php

 
/**
 * 单商品减固定价格购买
 * $ 2010-05-04 16:51 $
 */
class b2c_promotion_solutions_freeshipping implements b2c_interface_promotion_solution
{
    public $name = "免运费"; // 名称
    public $type = 'order'; //默认goods
    public $desc_pre = '免运费';
    public $desc_post = '';
    
    //开启此项时。此优惠也会应用到商品以外。如购物车中只有礼包
    public $actiontoall = true; 
    
    private $description = '';
    
    
    public function __construct( $app ) {
        $this->app = $app;
        $this->name = app::get('b2c')->_($this->name);
        $this->desc_pre = app::get('b2c')->_($this->desc_pre);
    }
    
    
    
    
    
    /**
     * 优惠方案模板
     * @param array $aConfig // 设置信息(修改的时候传入)
     * @return string // 返回要输出的模板html
     */
    public function config($aData = array()) {
        return <<<EOF
            <input name="action_solution[b2c_promotion_solutions_freeshipping][solution]" type="hidden" value="true" />
免运费!!
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
        if(is_null($cart_object)) { // 商品预过滤
            #$object['obj_items']['products'][0]['gain_score'] += $aConfig['gain_score'];
            $object['is_free_shipping'] = true;
        } else {// 购物车里的处理
            $object['is_free_shipping'] = true;
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
    public function apply_order(&$object, &$aConfig,&$cart_object = null,$rule=array()) {
        if(is_null($cart_object)) return false;
        $cart_object['is_free_shipping'][$rule['store_id']] = true;
        $cart_object['free_shipping_rule_id'][$rule['store_id']] = $this->rule_id;
        $cart_object['free_shipping_rule_type'][$rule['store_id']] = $this->type;
        $this->setString($aConfig);
    }
    
    
    public function setString($aData) {
        $this->description = $this->desc_pre;
    }
    
    public function getString() {
        return $this->description;
    }
    
    
    
    public function get_status() {
        return true;
    }
    
    public function allow( $is_order ) {
        return 'order';
    }
    
	/**
	 * 校验参数是否正确
	 * @param mixed 需要校验的参数
	 * @param string error message
	 * @return boolean 是否成功
	 */
	public function verify_form($data=array(), &$msg='')
	{		
		return true;
	}
}

