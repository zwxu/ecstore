<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class gift_site_goodsspec
{
    public function __construct( &$app ) {
        $this->app = $app;
    }
    
    
    public function trim_spec( &$arr,$arr_gift_id='' ) {
        if( !$arr['spec'] || !is_array($arr['spec']) ) return false;
        
        if( !$arr_gift_id ) {
            $tmp = $this->app->model('ref')->getList( 'product_id,max_limit,real_limit',array('goods_id'=>$arr['goods_id']) );
            if( !$tmp ) return false;
            foreach( $tmp as $row ) {
                $arr_gift_id[] = $row['product_id'];
                $arr_store[$row['product_id']] = $row['max_limit'] != null?(floatval($row['max_limit'] - $row['real_limit'])):"nolimit";
            }
        }
        
        if( !$arr['product2spec'] ) return false;
        $tmp = json_decode( $arr['product2spec'] );
        foreach( $tmp as $pid => $row ) {
            if( !in_array($pid,$arr_gift_id) ) {
                unset($tmp->$pid);
            } else {
                $tmp->$pid->store = ( $arr_store[$pid]== "nolimit" || $tmp->$pid->store < $arr_store[$pid] ) ? $tmp->$pid->store : $arr_store[$pid];
                if( $tmp->$pid->store<0 ) $tmp->$pid->store = 0;
                $arr_spec_value_id[] = $tmp->$pid->spec_private_value_id;
            }
        }
        if( count($arr['spec'])==1 ) {
            $_key = current(array_keys($arr['spec']));
            $_val  = $arr['spec'][$_key];
            if( $arr_spec_value_id && is_array($arr_spec_value_id) ) {
                foreach( $arr_spec_value_id as $val ) {
                    $val = (array)$val;
                    if( count($val)>1 ) break;
                    $key = current($val);
                    if( $_val['option'][$key] )
                        $arr_option_spec[$key] = $_val['option'][$key];
                }
                if( $arr_option_spec )
                    $arr['spec'][$_key]['option'] = $arr_option_spec;
            }
        }
        $arr['product2spec'] = json_encode($tmp);

    }
}