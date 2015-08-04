<?php

 
/**
 * postfilter aggregator基类
 * $ 2010-05-09 19:39 $
 */
class b2c_sales_basic_postfilter_aggregator extends b2c_sales_basic_aggregator
{
    private $unuse_rule_cart = null;
    private $config_total_limit = null;
    
    // 集合器的处理(默认)
    public function get_unuse_rule($cart_objects, $rule) {
        
        $app = app::get('b2c');
        switch($app->getConf('cart.show_order_sales.type')){
            case "false":
                if( !$this->config_total_limit ) 
                    $this->config_total_limit = $app->getConf('cart.show_order_sales.total_limit');
                break;
            case "true":
                $this->config_total_limit = 'all';
                break;
        }
        
        $condition = $rule['conditions'];
        if( !is_array( $condition['conditions'] ) ) return false;
        $msg = array();
        
        if( $this->config_total_limit==='all' ) { //显示所有
            $msg[] = $rule['description'];
        } else {
            if( count($condition['conditions']) > 1 ) return false;//组合条件不再范围之内
            
            foreach ($condition['conditions'] as $_key => $_cond) {
                if( !isset($_cond['operator']) ) continue;
                switch( $_cond['operator'] ) {
                    case ">"  :
                    case ">=" :
                        if( !$this->unuse_rule_cart[$_cond['type']] )
                            $this->unuse_rule_cart[$_cond['type']] = kernel::single($_cond['type'])->getItem();

                        $tmp_limit_total = $cart_objects[$this->unuse_rule_cart[$_cond['type']][$_cond['attribute']]['path']] * ( 100 + $this->config_total_limit ) / 100;
                        if( $tmp_limit_total < $_cond['value'] ) break;
                    
                        
                        
                        $msg[$_key] = $this->unuse_rule_cart[$_cond['type']][$_cond['attribute']]['name'];
                        $msg[$_key] .= app::get('b2c')->_('还差');
                        #$msg[$_key] .= $_cond['value'] - $cart_objects[$this->unuse_rule_cart[$_cond['type']][$_cond['attribute']]['path']];
                        $unit = $this->unuse_rule_cart[$_cond['type']][$_cond['attribute']]['unit'];
                        $msg[$_key] .= is_array($unit) 
                                        ? ( 
                                            ($unit['app'] && $unit['model'] && $unit['func']) 
                                            ? @app::get($unit['app'])->model($unit['model'])->$unit['func']($_cond['value'] - $cart_objects[$this->unuse_rule_cart[$_cond['type']][$_cond['attribute']]['path']]) 
                                            : '' 
                                          ) 
                                        : $_cond['value'] - $cart_objects[$this->unuse_rule_cart[$_cond['type']][$_cond['attribute']]['path']] . $unit;
                        #$msg[$_key] .= '就可以享受';
                        break;
                    default : break;
                }
            }
        }

        return $msg ? implode( '、', (array)$msg ) : false;
    }
    
    
    
    // 集合器的处理(默认)
    public function validate($cart_objects, &$condition) {

        $all = $condition['aggregator'] === 'all';
        $true = (bool)$condition['value'];
        if(!isset($condition['conditions'])) {
            return true;
        }
        if( !is_array( $condition['conditions'] ) ) return false;
		
        foreach ($condition['conditions'] as $_cond) {
            if( !is_object($this->$_cond['type']) )
                $this->$_cond['type'] = kernel::single($_cond['type']);
            $oCond = $this->$_cond['type'];
			$_cond['rule_id'] = $condition['rule_id'];//lijun
            $validated = $oCond->validate($cart_objects, $_cond); // return boolean
            if ($all && $validated !== $true) { // 所有不符合 如果有一个满足返回false
                return false;
            } elseif (!$all && $validated === $true) {// 任意一条符合 则返回true
                return true;
            }
        }

        return $all ? true : false;
    }
}