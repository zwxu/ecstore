<?php

 
/**
 * 单商品减固定价格购买
 * $ 2010-05-04 16:51 $
 */
class b2c_promotion_solutions_goods_byfixed implements b2c_interface_promotion_solution
{
    public $name = "减固定价格购买"; // 名称
    public $type = 'goods'; //默认goods
    public $desc_pre = '商品价格优惠(减去)';
    public $desc_post = '元出售';
    

    /**
     * 优惠方案模板
     * @param array $aConfig // 设置信息(修改的时候传入)
     * @return string // 返回要输出的模板html
     */
    public function config($aData = array()) {
        return <<<EOF
        {$this->desc_pre}<input name="action_solution[b2c_promotion_solutions_goods_byfixed][total_amount]" vtype='required&&unsigned' value="{$aData['total_amount']}" />{$this->desc_post}
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
            
            //echo $object['obj_items']['products'][0]['goods_id'],"------------\r\n";
            $object['obj_items']['products'][0]['price']['buy_price'] = $object['obj_items']['products'][0]['price']['price'] - $aConfig['total_amount'];
            $object['obj_items']['products'][0]['price']['buy_price'] = max(0,$object['obj_items']['products'][0]['price']['buy_price']);
        } else {// 购物车里的处理
            //print_r($object);exit;
            $productQuantity = $object['obj_items']['products'][0]['quantity'];
            $goodsQuantity = $object['quantity'];
            //echo "---", $goodsQuantity;exit;
            $qty = $productQuantity * $goodsQuantity;
            $productPrice = $object['obj_items']['products'][0]['price']['buy_price'];
            $discountAmount    = $qty * $aConfig['total_amount'];
            $object['discount_amount_order'] = max(0, $discountAmount);
            //print_r($object);exit;
            
        }
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
        //print_r($cart_object);exit;
        $object['discount_amount_order'] = $cart_object['items_quantity'] * $aConfig['total_amount'];
    }
    
    
    public function getString($aData=array()) {
        return $this->desc_pre . $aData['total_amount'] . $this->desc_post;
    }
    
    
    
}
?>
