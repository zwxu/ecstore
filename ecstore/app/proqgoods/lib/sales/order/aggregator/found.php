<?php

 
/**
 * order aggregator(商品促销规则 购物车项组合条件)
 * 只做了简单处理 针对promotion_conditions_goods_goodsofquantity
 * 如要应用类似场景需加工 购物车中每个项中是否满足
 * $ 2010-05-16 18:34 $
 */
class proqgoods_sales_order_aggregator_found extends b2c_sales_order_aggregator
{


    /**
     * 检验购物车内信息是否符合优惠条件
     * 集合器的处理(found) 只处理goods的
     *
     * @param array $cart_objects 购物车信息
     * @param array $condition 优惠条件
     * @return bool
     */
    public function validate($cart_objects,$condition) {
        $all = $condition['aggregator'] === 'all';
        $true = (bool)$condition['value'];
        
        // 循环购物中的商品信息
        foreach ($cart_objects['object']['goods'] as $object) {
            $found = null;
            // 循环条件(aggregator found下的 conditions)
            foreach ($condition['conditions'] as $_cond) {
                $oCond = kernel::single($_cond['type']);
                $validated = $oCond->validate($object, $_cond);

                /*-------*/
                if($all) { // 所有
                    if($true) {// 符合
                        if(!$validated) {
                            $found = false;
                            break 1;
                        }
                    } else {// 不符合
                        if($validated) {
                            $found = false;
                            break 1;
                        }
                    }
                } else {// 任意
                    if($true) {// 符合
                        if($validated) {
                            $found = true;
                            break 2;
                        }
                    } else {// 不符合
                        if(!$validated) {
                            $found = true;
                            break 2;
                        }
                    }
                }
                /*-------*/
            }
            if( is_null($found) )  {
                $found = (bool)($found^$all);
                if( $found )  break;
            }
        }
        // 如果都是以pass形式走过的 返回则与aggregator相关
        return $found;

    }
}
?>
