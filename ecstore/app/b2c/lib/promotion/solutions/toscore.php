<?php

 
/**
 * 单商品减固定价格购买
 * $ 2010-05-04 16:51 $
 */
class b2c_promotion_solutions_toscore implements b2c_interface_promotion_solution
{
    public $name = "X倍积分"; // 名称
    public $type = 'goods'; //默认goods
    public $desc_pre = '获得';
    public $desc_post = '倍积分';
    public $score_add = true;
    private $description = '';
    
    
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
        if( !$this->get_status() ) return '<p class="red">'.app::get('b2c')->_('积分已取消!如使用积分请修改商店设置-》积分').'</p>';
        
        return <<<EOF
{$this->desc_pre}<input name="action_solution[b2c_promotion_solutions_toscore][gain_score]" vtype='required&&unsigned' value="{$aData['gain_score']}" />{$this->desc_post}
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
            $object['obj_items']['products'][0]['gain_score'] *= ($aConfig['gain_score']);
        } else {// 购物车里的处理
            $object['sales_score_order'] += $object['subtotal_gain_score'] * ($aConfig['gain_score']-1);
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
        $this->setString($aConfig);
        $object['sales_score_order'] += $cart_object['subtotal_gain_score'] * ($aConfig['gain_score']-1);
    }
    
    public function setString($aData) {
        $this->description = $this->desc_pre . $aData['gain_score'] . $this->desc_post;
    }
    
    public function getString() {
        return $this->description;
    }
    
    
    public function get_status() {
        if(app::get('b2c')->getConf('site.get_policy.method')==1) return false;
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
		if (!isset($data['action_solution']['b2c_promotion_solutions_toscore']['gain_score']) || !$data['action_solution']['b2c_promotion_solutions_toscore']['gain_score'])
		{
			$msg = app::get('b2c')->_('请指定订单赠送积分的倍数！');
			return false;
		}
		
		if (preg_match('/[^\d]/',$data['action_solution']['b2c_promotion_solutions_addscore']['gain_score']) || $data['action_solution']['b2c_promotion_solutions_toscore']['gain_score'] < 0)
		{
			$msg = app::get('b2c')->_('订单赠送积分必须是大等于0的数字！');
			return false;
		}
		/** end **/
		
		return true;
	}
}

