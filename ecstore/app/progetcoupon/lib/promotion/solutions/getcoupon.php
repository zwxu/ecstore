<?php

 
/**
 * 送优惠券
 * $ 2010-05-04 16:51 $
 */
class progetcoupon_promotion_solutions_getcoupon implements b2c_interface_promotion_solution
{
    public $name = "送优惠券"; // 名称
    public $type = 'goods'; //默认goods
    public $desc_pre = '送优惠券';
    public $desc_post = '';
    
    //开启此项时。此优惠也会应用到商品以外。如购物车中只有礼包
    public $actiontoall = true; 
    
    private $description = '';
    
    
    public function __construct( $app ) {
        $this->app = $app;
        $this->name = app::get('progetcoupon')->_($this->name);
        $this->desc_pre = app::get('progetcoupon')->_($this->desc_pre);
        $this->desc_post = app::get('progetcoupon')->_($this->desc_post);
    }
    
    
    /**
     * 优惠方案模板
     * @param array $aConfig  设置信息(修改的时候传入)
     * @return string  返回要输出的模板html
     */
    public function config($aData = array()) {
        $render = app::get('progetcoupon')->render(); 

        
        $render->pagedata['isfront'] =$aData['isfront'];  
        $render->pagedata['value'] = $aData['cpns_id'];
        $render->pagedata['object'] = 'coupons@b2c';
        // 格式：,3,
        $render->pagedata['filter']['cpns_id'] = kernel::single('b2c_coupon_filter')->get_coupon($aData['store_id']);

        $render->pagedata['name'] = 'action_solution[progetcoupon_promotion_solutions_getcoupon][cpns_id]';
        return $this->desc_pre . $render->fetch('admin/sales/dialog.html');
    }

    /**
     * 优惠方案应用
     *
     * @param array &$object     引用的一个商品信息
     * @param array $aConfig     优惠的设置
     * @param array &$cart_object     购物车信息(预过滤的时候这个为null)
     * @return void 引用处理了,没有返回值
     */
    public function apply(&$object,$aConfig,&$cart_object = null) {
        
        if(is_null($cart_object)) { // 商品预过滤
            $object['promotion_order_create'][get_class($this)][] = $aConfig;
        } else {// 购物车里的处理
            $object['promotion_order_create'][get_class($this)][] = $aConfig;
        }
        $this->setString($aConfig);
    }
    
    
    
    
    /**
     * 优惠方案应用
     *
     * @param array &$object   引用的一个商品信息
     * @param array &$aConfig  优惠的设置
     * @param array &$cart_object  购物车信息(预过滤的时候这个为null)
     * @return void 引用处理了,没有返回值
     */
    public function apply_order(&$object, &$aConfig,&$cart_object = null) {
        if(is_null($cart_object)) return false;
        $this->setString($aConfig);
        #$object['sales_score_order'] += $cart_object['subtotal_gain_score'] * ($aConfig['gain_score']-1);
        $cart_object['promotion_order_create'][get_class($this)][] = $aConfig;
        #print_r($aConfig);exit;
    }
    
    public function setString($aData) {
        if( $aData['cpns_id'] ) {
            $arr = app::get('b2c')->model('coupons')->getList( 'cpns_name',array('cpns_id'=>$aData['cpns_id']) );
            $desc = implode('、',array_map('current',$arr));
        }
        $this->description = $this->desc_pre . $desc . $this->desc_post;
    }
    
    public function getString() {
        return $this->description;
    }
    
    
    public function get_status() {
        return true;
    }
    
    
    /**
     * 执行送优惠券方案
     *
     * 在下订单时，优惠券便又已送出，但此时不可用(memc_isvalid为false）
     * 当订单付款后，优惠券即可用(memc_isvalid更改为true)
     *
     * @param array $aConfig 优惠券方案，指定优惠券id.
     * @param int $order_id 订单id.
     * @return void|boolean 不是会员返回false,执行成功会为会员添加指定优惠券
     */
    public function exec( $aConfig,$order_id ) {
        $o = app::get('b2c')->model('coupons');
        if( $aConfig['cpns_id'] && is_array($aConfig['cpns_id']) ) {
            $arr = $o->getList( '*',array('cpns_id'=>$aConfig['cpns_id']) );
            #echo "<pre>";print_r($arr);
            if( $arr ) {
                $arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
                $member_id = $arr_member_info['member_id']; //test 适用 
                if( !$member_id ) return false; //没有会员id时不赠送
                $o_mem_coupon = app::get('b2c')->model('member_coupon');
                foreach( $arr as $row ) {
                    if( $row['cpns_type']=='1' ) {  //b类
                        $coupons = $o->downloadCoupon( $row['cpns_id'],1,array('0','1') );
                        foreach( (array)$coupons as $code ) {
                            $aSave = array(
                                        'memc_code'=>$code,
                                        'cpns_id'=>$row['cpns_id'],
                                        'member_id'=>$member_id,
                                        'memc_gen_orderid'=>$order_id,
                                        'memc_gen_time'=>time(),
                                        'memc_isvalid'=>'false',
                                    );
                            $o_mem_coupon->save($aSave);
                            #echo "<pre>";print_r($aSave);exit;
                        }
                        
                    } else {    //a类
                        $aSave = array(
                                    'memc_code'=>$row['cpns_prefix'],
                                    'cpns_id'=>$row['cpns_id'],
                                    'member_id'=>$member_id,
                                    'memc_gen_orderid'=>$order_id,
                                    'memc_gen_time'=>time(),
                                    'memc_isvalid'=>'false',
                                );
                        $o_mem_coupon->save($aSave);
                        #echo "<pre>";print_r($aSave);exit;
                    }
                }
            }
        }
        #app::get('b2c')->model('coupons')->downloadCoupon();
        #print_r($aConfig);exit;
    }
    
}

