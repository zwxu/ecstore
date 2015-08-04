<?php

 
/**
 * order aggregator(商品促销规则 购物车项组合条件)
 * $ 2010-05-16 18:34 $
 */
class b2c_sales_order_aggregator_found extends b2c_sales_order_aggregator
{
    public function getItem() {
        // 其实一个aggregator 只有一条记录的哈
        return array(
                   'b2c_sales_order_aggregator_found' => array(
                                       'name'=>app::get('b2c')->_('商品属性组合'),
                                       'object'=>'b2c_sales_order_aggregator_found',
                                       'support'=>array(
                                                     'aggregator'=>array(),
                                                     'item'=>array(
                                                                'goods'=>app::get('b2c')->_('-----商品属性-----'),
                                                                'subgoods'=>app::get('b2c')->_('-----商品扩展属性-----'),
                                                             )
                                                  ),
                                      )
               );
    }

    // 集合器的处理(found) 只处理goods的
    public function validate($cart_objects,$condition) {
        $all = $condition['aggregator'] === 'all';
        $true = (bool)$condition['value'];
        $found = null;

        // 循环购物中的商品信息
        foreach ($cart_objects['object']['goods'] as $object) {
            // 循环条件(aggregator found下的 conditions)
            foreach ($condition['conditions'] as $_cond) {
                $oCond = kernel::single($_cond['type']);
                $validated = $oCond->validate($object, $_cond);
                /*-------*/
                if($all) { // 所有
                    if($true) {// 符合
                        if(!$validated) {
                            $found = false;
                            break;
                        }
                    } else {// 不符合
                        if($validated) {
                            $found = false;
                            break;
                        }
                    }
                } else {// 任意
                    if($true) {// 符合
                        if($validated) {
                            $found = true;
                            break;
                        }
                    } else {// 不符合
                        if(!$validated) {
                            $found = true;
                            break;
                        }
                    }
                }
                /*-------*/
            }
            if(!is_null($found)) break;
        }
        // 如果都是以pass形式走过的 返回则与aggregator相关
        if(is_null($found)) $found = $all;
        return $found;

    }
}
