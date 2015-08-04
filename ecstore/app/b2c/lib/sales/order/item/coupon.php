<?php

 
/**
 * 只是coupon validate的操作 不用在规则添加上显示,所以不用注册在b2c_sales_order_item_apps里
 * $ 2010-05-17 13:28 $
 */
class b2c_sales_order_item_coupon extends b2c_sales_order_item
{
    
    
    public function __construct() {
        parent::__construct();
        $this->o_coupon = app::get('b2c')->model('coupons');
    }
    
    
    /**
     * item validate 重载
     *
     * @param array $objects     // 购物车数据 传入的是整个的购物车数组数据
     * @param array $aCondition  // 条件规则
     * @return boolean
     */
    public function validate($objects,$aCondition) {
        // 没有coupon号 说明规则有问题 返回false
        
        if(empty($aCondition['value'])) return false; //
        if(empty($objects['object']['coupon'])) return false; // 购物车没有加入过coupon

        $couponsModel = $this->o_coupon;
        $value = $aCondition['value'];
		
        $couponFlag = $couponsModel->getFlagFromCouponCode($value);
        while (list($_k, $_v) = each($objects['object']['coupon'])) {
              switch ($couponFlag) {
                  case 'A':
                      if ($value == $_v['coupon'] ) {
                          return true;
                      }
                      break;
                  case 'B':
                      $couponPre = $couponsModel->getPrefixFromCouponCode($_v['coupon']);
                      if ($couponPre == $value&&$aCondition['rule_id']==$_v['rule_id']) {// && $aCondition['rule_id']==$_v['rule_id']
                          return true;
                      }
                      break;
                  default :
                      if ($value == $_v['coupon'] ) {
                          return true;
                      }
              }
        }
        return false;

    }
}
?>
