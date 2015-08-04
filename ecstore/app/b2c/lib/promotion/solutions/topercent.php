<?php

 
/**
 * 单商品价格减去固定折扣出售
 * $ 2010-05-04 17:06 $
 */
class b2c_promotion_solutions_topercent implements b2c_interface_promotion_solution
{
    
    
    public $name = "以固定折扣出售"; // 名称
    public $type = array('prefilter','cart'); // 应用范围 目前只有[预过滤(prefilter),购物车(cart)]
    public $desc_pre = '价格乘以';
    public $desc_post = '%折扣出售';
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
{$this->desc_pre}<input name="action_solution[b2c_promotion_solutions_topercent][percent]" vtype='required&&unsigned' value="{$aData['percent']}" />{$this->desc_post}
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
        $omath = kernel::single('ectools_math');
        $rulePercent = max(0, $aConfig['percent']);
        $rulePercent = min($rulePercent, 100);

        if(is_null($cart_object)) { // 商品预过滤
            $p = $rulePercent/100;
            $object['obj_items']['products'][0]['price']['buy_price'] = $omath->number_multiple( array($object['obj_items']['products'][0]['price']['buy_price'],$p) );
        } else {// 购物车里的处理
            $productQuantity = $object['obj_items']['products'][0]['quantity'];
            $goodsQuantity = $object['quantity'];
            $qty = $productQuantity*$goodsQuantity;
            $productPrice = $object['obj_items']['products'][0]['price']['buy_price'];
            $discountAmount = $omath->number_multiple( 
                                                        array(
                                                            $omath->number_multiple(array($qty,$productPrice)),
                                                            (1 - $rulePercent/100) 
                                                        )
                                                    );
            $object['discount_amount_order'] = $omath->number_plus( array($object['discount_amount_order'],$discountAmount) );
            //$this->desc_pre = '总价格乘以';
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
        $omath = kernel::single('ectools_math');
        
        $rulePercent = max(0, $aConfig['percent']);
        $rulePercent = min($rulePercent, 100);

        if($rule){
			$info = $this->get_store_object($rule['store_id'],unserialize(serialize ($cart_object)));
		}
		if($info){
			$object['discount_amount_order'] += $omath->number_multiple( 
														array(
															($info['subtotal'] - $info['discount_amount_prefilter']),
															(1 - $rulePercent/100)
														)
													);
		}else{
			$object['discount_amount_order'] += $omath->number_multiple( 
																	array(
																		($cart_object['subtotal'] - $cart_object['discount_amount_prefilter']),
																		(1 - $rulePercent/100)
																	)
																);
		}
        
        $this->desc_pre = app::get('b2c')->_('订单总价格乘以');
        $this->setString($aConfig);
    }
    
    
    
    /*
	*  过滤店铺商品 
	*  
	*/
	public function get_store_object($store_id,$arrr){
		
		if($store_id){
			foreach($arrr['object']['goods'] as $k=>$v){
				if($v['store_id']!=$store_id){
					unset($arrr['object']['goods'][$k]);
				}
			}
			$this->app->model('cart')->count_objects($arrr);
		}
		return $arrr;
	}
    
    
    public function setString($aData) {
        $this->description = $this->desc_pre . $aData['percent'] . $this->desc_post;
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
		if (!isset($data['action_solution']['b2c_promotion_solutions_topercent']['percent']) || !$data['action_solution']['b2c_promotion_solutions_topercent']['percent'])
		{
			$msg = app::get('b2c')->_('请指定订单固定折扣的金额！');
			return false;
		}
		
		if (!is_numeric($data['action_solution']['b2c_promotion_solutions_topercent']['percent']) || $data['action_solution']['b2c_promotion_solutions_topercent']['percent'] < 0 || $data['action_solution']['b2c_promotion_solutions_topercent']['percent'] > 100)
		{
			$msg = app::get('b2c')->_('固定折扣必须是在0-100的数字！');
			return false;
		}
		/** end **/
		
		return true;
	}
}
