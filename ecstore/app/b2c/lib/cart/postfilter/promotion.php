<?php

 
/**
 * 订单促销过滤
 * $ 2010-05-04 17:30 $
 */
class b2c_cart_postfilter_promotion implements b2c_interface_cart_postfilter {
    private $app;
    private $_rules = null;
    private $FREE_SHIPPING_ITEM = 1;
    private $FREE_SHIPPING_ORDER = 2;

    public function __construct(&$app){
        $this->app = $app;
        $this->o_cond = kernel::single('b2c_sales_order_aggregator_combine');
        $this->o_sales_order = $this->app->model('sales_rule_order');
    }

    public function filter(&$aData,&$aResult,$aConfig = array()) {
        $this->use_rules = $this->pass_rules = $this->all_rules = $this->_rules = null;
        // 取出符合当前购物车条件的规则(已经使用了conditions过滤)
        $this->_filter_rules($aResult,$aConfig);
        
        // 只是对goods进行处理待解决 其它类型购物车项待扩展

        if(!isset($aResult['object']['goods']) || !is_array($aResult['object']['goods'])) return false;
        
        if( $aResult['object']['goods'] ) {
            foreach($aResult['object']['goods'] as &$object) {
                $this->_apply_to_item($object,$aResult);
            }
        } else {
            //原促销只针对于商品。但订单中包含礼包等信息时。应用于整个购物的促销无法应用。 故追加如下 2011-2-28 16:08
            $num = 0;
            foreach( kernel::servicelist('b2c_cart_object_apps') as $o ) {
                if( $o->intopromotion ) { //标识该类型“商品” 是否计入订单促销范围
                    if( method_exists($o,'get_type') )
                    $tmp[] = $o->get_type();
                }
            }
            
            if( $tmp ) {
                foreach($aResult['object'] as $_key => &$row) {
                    if( in_array($_key,$tmp) && $row ){
                        foreach($row as &$object) {
                            $this->_apply_to_item($object,$aResult);
                        }
                    }
        }
        
            }
            //End
        }
        #print_r($aResult);exit;
        
        
        if(isset($aResult['object']['coupon']) && !empty($aResult['object']['coupon'])) {
            foreach ($aResult['object']['coupon'] as &$val) {
                if(isset($this->all_rules[$val['rule_id']])) {
                    $val['name'] = $this->all_rules[$val['rule_id']]['cpns_name'];
                    $val["description"] = $aResult['promotion']['coupon'][$val['rule_id']]['desc'];//$this->all_rules[$val['rule_id']]['description'];
                    if(!$this->use_rules || !is_array($this->use_rules)) continue ;
                    if(isset($this->use_rules[$val['rule_id']])) {
                        if(!$this->use_rules[$val['rule_id']]['show'])  $val['used'] = true;
                        $this->use_rules[$val['rule_id']]['show'] = true;
                    }
                } else {
                    $val['name'] = $val['coupon'];
                    $val["description"] = '<font color="red">'.app::get('b2c')->_('该优惠券可能已过期或未启用！').'~</font>';
                }
                
                
            }
        }
         
        $this->dehistory();
         
        
    }



    // 初始化订单促销规则(根据当前时间,登录用户等级 从数据库中取出订单促销规则)
    private function _init_rules(){
        if (!$this->_rules){
            $mSRO = $this->o_sales_order;
            $arrMemberInfo = kernel::single("b2c_cart_objects")->get_current_member();
            
            $aFilter = array(
                'member_lv' => ($arrMemberInfo['member_lv'] ? $arrMemberInfo['member_lv'] : -1), // todo 这里要改成登录用户的会员等级
                'current_time' => time(),
                );

            $sSql = "SELECT sdb_b2c_sales_rule_order.rule_id,sdb_b2c_coupons.store_id,sdb_b2c_sales_rule_order.name,cpns_name, description, conditions, action_conditions, action_solution, free_shipping, stop_rules_processing, rule_type FROM `sdb_b2c_sales_rule_order` LEFT JOIN `sdb_b2c_coupons` ON `sdb_b2c_sales_rule_order`.rule_id = `sdb_b2c_coupons`.rule_id
                         WHERE ". $this->_filter_sql($aFilter) ."
                         ORDER BY  sort_order ASC,  sdb_b2c_sales_rule_order.rule_id DESC";
            $this->_rules = $mSRO->db->select($sSql);
            is_array($this->_rules) or $this->_rules=array();
            foreach($this->_rules as $_k => &$rule) {
				//begin
				$arr = explode(',',$rule['store_id']);
				$rule['store_id'] = $arr[1];//add  by lijun 2013/5/22
                foreach($rule as $_k1 => &$value) {	
                    if(in_array($_k1, array('rule_id', 'description', 'cpns_name', 'rule_type', 'free_shipping','name','store_id'))) continue;
                    if(in_array(strtolower($value), array('true', 'false'))) {
                        $value = (strtolower($value)=='true') ? true : false;
                        continue;
                    }
                    $value = is_array($value) ? $value : unserialize($value);
                }
            }
        }

        return true;
    }

    // 开发这个 主要是为了测试用例的
    public function getRule() {
        return $this->_rules;
    }

    private function _init_rules_order($aConfig) {
        // todo 订单修改时初始化 需要处理的规则
    }

    
    
    
    private function _filter_sql($aFilter) {
        $aWhere[] = "status = 'true'"; // 开启状态
        
        if (isset($aFilter['member_lv'])){
            $aWhere[] = sprintf(' (find_in_set(\'%s\', member_lv_ids))', $aFilter['member_lv']);
            unset($aFilter['member_lv']);
        }

        if (isset($aFilter['current_time'])){
            $aWhere[] = sprintf(' (%s >= from_time or from_time=0)',
                               $aFilter['current_time']);
            $aWhere[] = sprintf(' (%s <= to_time or to_time=0)', $aFilter['current_time']);
            unset($aFilter['current_time']);
        }
        return implode(' AND ',$aWhere);
    }
    
    
    
    /**
     * 过滤订单促销规则(cart_objects符合conditions 的促销规则) 去掉当前购物车内不符合的促销规则
     *
     * @param array $cart_objects
     */
    private function _filter_rules($cart_objects,$aConfig = array()){
        if(!empty($aConfig)) {
            $this->_init_rules_order($aConfig);
        } else {
            if (!$this->_rules) $this->_init_rules();
        }

        $validated = false;
		
        foreach($this->_rules as $_k => $rule){
			$aCart = $cart_objects;
            $oCond = $this->o_cond;

            $this->all_rules[$rule['rule_id']] = $rule;
			$this->get_store_object($rule['store_id'],$aCart);
			$rule['conditions']['rule_id'] = $rule['rule_id'];
            $validated = $oCond->validate($aCart,$rule['conditions']);
            if(!$validated){
                unset($this->_rules[$_k]);
            }
        }//echo '<pre>';print_r($this->_rules);exit;

    }
	/*
	*  过滤店铺商品 
	*  
	*/
	public function get_store_object($store_id,&$cart_objects){
		if($store_id){
			foreach($cart_objects['object']['goods'] as $k=>$v){
				if($v['store_id']!=$store_id){
					unset($cart_objects['object']['goods'][$k]);
				}
			}
			$this->app->model('cart')->count_objects($cart_objects);
		}
	}
    
    
    
    /**
     * 订单页面未执行的促销
     *
     * @param array $cart_objects
     */
    public function get_unuse_rule($cart_objects){
        if(!empty($aConfig)) {
            $this->_init_rules_order($aConfig);
        } else {
            if (!$this->_rules) $this->_init_rules();
        }
        

        $validated = false;
        foreach($this->_rules as $_k => $rule){
            $oCond = $this->o_cond;
            if( strtolower($rule['rule_type'])=='c' ) continue;
            $validated = $oCond->validate($cart_objects,$rule['conditions']);
            if(!$validated){
                $s = $oCond->get_unuse_rule( $cart_objects,$rule );
                if( !$s ) continue;
                $solution = $rule['action_solution'];
                reset( $solution );
                @kernel::single(key($solution))->setString(current($solution));
                $return[$_k]['desc'] = $s;
                $return[$_k]['name'] = $rule['name'];
                $return[$_k]['solution'] = @kernel::single(key($solution))->getString();
                unset($this->_rules[$_k]);
            }
        }
        return array_values($return);
    }

    private function _apply_action(&$object, &$cart_object, &$rule) {
        
        //优惠方案不存在直接返回
        if(!$rule['action_solution']) return false;
        foreach ($rule['action_solution'] as $key => &$val) {
            if($val['used']) continue;

            if(!is_string($key))continue;
            
            $o = kernel::single($key);
            if(method_exists($o, 'get_status')) {
                if(!$o->get_status()) return false;
            }
            
                
            $o->rule_id = $rule['rule_id'];
            $o->type = (strtolower($rule['rule_type'])=='c') ? 'coupon' : $val['type'];
            //针对于符合条件的商品
            if($val['type']=='goods') {
                //商品促销中。同种方案在同一商品上 适用 排他原则 addtime 2011-2-23 16:03
                //我最想要的效果是：最后应用的规则是最终生效的规则
                $pid = $object['obj_items']['products'][0]['product_id'];
                if( $this->__stop_rules_processing_goods[$key][$pid] ) {
                    $this->__stop_rules_processing_goods[$key][$pid];
                    kernel::single($key)->setString($val);
                    continue;
                }
                if( $o->stop_rule_with_same_solution ) {
                    $this->__stop_rules_processing_goods[$key][$pid] = $val;
                }
                
                $o->apply($object, $val, $cart_object);
            } else {
                //订单
                $o->apply_order($object, $val, $cart_object,$rule);
                $val['used'] = true;
            }
            
        }
        return $key;
    }

    private function _apply_to_item(&$object, &$cart_object){
        
        $oCond = $this->o_cond;
        
        $arr_use_rule = array();
		//echo '<pre>';print_r($this->_rules);exit;
        foreach($this->_rules as &$rule) {
                if($this->stop_rules_processing[$rule['rule_type']] && $this->stop_rules_processing[$rule['rule_type']]!==$rule['rule_id'] ) continue;
                // 如果action_conditions['conditions']不为空 验证要否对此商品进行优惠  为空 表示对全部购物车商品
                if(isset($rule['action_conditions']['conditions']) && !empty($rule['action_conditions']['conditions'])) {
                    // 不符合则跳过
                if (!$oCond->validate($object, $rule['action_conditions'])) continue;
                    }
				
                    // 是否免运费
                    switch($rule['free_shipping']){
                        case $this->FREE_SHIPPING_ITEM:
                            $object['is_free_shipping'] = true;
                            break;
                        case $this->FREE_SHIPPING_ORDER:
                            $cart_object['is_free_shipping'] = true;
                            break;
                    }

                    $this->all_rules[$rule['rule_id']] = $rule;
                    
                    
                    //用户适用的订单规则
                    $arr_use_rule[$rule['rule_type']][] = &$rule;
                    if( $rule['stop_rules_processing']&&!$rule['store_id'] ) {
                        $this->stop_rules_processing[$rule['rule_type']] = $rule['rule_id'];
                    }
    }

        if( isset($arr_use_rule) && is_array($arr_use_rule) ) {
            foreach( $arr_use_rule as $arr ) {
                if( is_array($arr) ) {
                    foreach( $arr as $tmp_use_rule ) {
                        if( $this->use_rules[$tmp_use_rule['rule_id']]['rule_used']==true ) continue;
            if( !$this->use_rules[$tmp_use_rule['rule_id']] )
                $this->use_rules[$tmp_use_rule['rule_id']] = $tmp_use_rule;
            
            //临时记录优惠价格 to：记录每个规则针对于商品的优惠 商超
            $tmp_promotion_amount = $object['discount_amount_order'];
            
            
            // 执行优惠处理
                        if( !is_array($tmp_use_rule['action_solution']) ) continue;
            reset( $tmp_use_rule['action_solution'] );
            
            //优惠方案的具体lib文件名
            $temp_solution_name = key($tmp_use_rule['action_solution']);  
            if( empty($object) ) {
                if( !kernel::single($temp_solution_name)->actiontoall ) {
                    unset( $this->use_rules[$tmp_use_rule['rule_id']] );
                                continue;
                }
            }
            
            //应用场景：1.促销应用于整个订单 2.订单中没有商品（如只添加了礼包进入购物车） 
            //此时传入object为空！  默认不允许应用“符合条件的商品”类促销
            if( 'goods'==$tmp_use_rule['action_solution'][$temp_solution_name]['type'] ) {
                            if( !$object ) continue;
            }
            $this->_apply_action($object, $cart_object, $tmp_use_rule);
            
            //优惠执行成功时返回解决方案适用的lib
            if($temp_solution_name) {

                //规则针对商品时处理购物车内所有符合条件的商品
                $solu_u_type = $tmp_use_rule['action_solution'][$temp_solution_name]['type'];
                if($solu_u_type=='goods') {
                   $this->pass_rules[$tmp_use_rule['rule_id']] = true;
                } else {
                    $this->use_rules[$tmp_use_rule['rule_id']]['rule_used'] = true;
                }

                //购物车：应用的优惠方案显示
                
                $oDefault = kernel::single($temp_solution_name);
                $obj_math = kernel::single("ectools_math");
                $tmp_promotion_name = 'order';
                
                if( strtolower($tmp_use_rule['rule_type'])=='c' ) $tmp_promotion_name = 'coupon';

                if(isset($cart_object['promotion'][$tmp_promotion_name][$tmp_use_rule['rule_id']])) {
                                if($oDefault->score_add) continue;
                    $cart_object['promotion'][$tmp_promotion_name][$tmp_use_rule['rule_id']]['discount_amount'] = $obj_math->number_plus(array($cart_object['promotion'][$tmp_promotion_name][$tmp_use_rule['rule_id']]['discount_amount'],$obj_math->number_minus(array($object['discount_amount_order'],$tmp_promotion_amount))));
                    if( $solu_u_type=='goods' && empty($rule['description']) ) 
                        $cart_object['promotion'][$tmp_promotion_name][$tmp_use_rule['rule_id']]['desc'] = $object['obj_items']['products'][0]['new_name'] .'、'. $cart_object['promotion'][$tmp_promotion_name][$tmp_use_rule['rule_id']]['desc'];
                } else {
                    $cart_object['promotion'][$tmp_promotion_name][$tmp_use_rule['rule_id']] = array(
                        'name' => $tmp_use_rule['name'],
                        'rule_id'   =>  $tmp_use_rule['rule_id'],
						'store_id' => $tmp_use_rule['store_id'],
                        'discount_amount' =>  0,
                                    'desc'  => ( empty($rule['description']) ? ( ( $solu_u_type=='goods' ? $object['obj_items']['products'][0]['new_name'] : '' ) . $oDefault->getString() ) : $tmp_use_rule['description'] ),
                        'solution' => $oDefault->getString(),
                    );
                                if($oDefault->score_add) continue;
                    $cart_object['promotion'][$tmp_promotion_name][$tmp_use_rule['rule_id']]['discount_amount'] = $obj_math->number_minus(array($object['discount_amount_order'],$tmp_promotion_amount));
                }
                            #print_r($object);exit;
                //记录商品享受的促销 商超
                $obj_key = $object['obj_items']['products'][0]['product_id'];
                $cart_object['promotion_solution'][$obj_key]['goods_id'] = $object['obj_items']['products'][0]['goods_id'];
                $cart_object['promotion_solution'][$obj_key]['order'][] = array(
                                                                'rule_id' => $tmp_use_rule['rule_id'],
																'store_id' => $tmp_use_rule['store_id'],
                                                                'amount'  => ($object['discount_amount_order']-$tmp_promotion_amount),
                                                                'type' => $tmp_use_rule['action_solution'][$temp_solution_name]['type'],
                                                            );

            }
        }
    }
            }
        }
    }
    
    
    
    
    private function dehistory() {
        $this->use_rules = $this->pass_rules = $this->all_rules = $this->_rules = $this->stop_rules_processing = $this->__stop_rules_processing_goods = null;
    }
    
}

