<?php

 
/**
 * 更新订单购买了的数据
 * 
 * @version 0.1
 * @package recommended.lib.data
 */
class recommended_data_operaction
{
    /**
     * 构造方法
     * @params object 当前应用的app对象
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
        $this->b2c_app = app::get('b2c');
    }
	
    /**
     * 更新数据的方法
     * @param string message 错误消息
     * @return null
     */
    public function update(&$msg='')
    {
        // get orders id
        $orders = $this->b2c_app->model('orders');
        $to   = strtotime('now');
        $timeline = $this->app->getConf( 'recommended.timeline' );

        if ( !empty($timeline) ) {
            $from = $timeline;
            $orders_filter['last_modified|between']  = array( $from, $to );
        }
        else {
            $orders_filter = array();
        }

        $orders_cols    = 'order_id,last_modified';
        $orders_orderby = array( 'last_modified', 'DESC');

        $start = 0;
        $step = 100;
        
        while(true ) {
            $orders = $this->b2c_app->model('orders');
            $re = $orders->getList( $orders_cols, $orders_filter, $start, $step, $orders_orderby );
            if ( !empty( $re ) ) {
                // get goods id in the same order
                $order_array = array();
                foreach ( $re as $v ){
                    (array)$orders_id[] = $v['order_id'];
                
                    $items   = $this->b2c_app->model( 'order_items' );
                    $filter  = array( 'order_id' => $v['order_id'] );
                    $cols    = 'order_id,goods_id';
                    $orderby = array( 'order_id', 'DESC' );
                    $result  = $items->getList( $cols, $filter, 0, -1, $orderby );
                
                    (array)$order_array[] = $result;
                }
            
                $orders     = array();
                $orders_ids = array();
                $goods_ids  = array();
                $arr        = array();
                $temp       = array();
            
                foreach ( $order_array as $k => $v ){
                    foreach ( $v as $v2 ){
                        $orders[$v[0]['order_id']][] = $v2['goods_id'];
                    }
                }
    
                // get unique goods id and last_modified time
                foreach ( $orders as $k => $v ){
                    foreach ( $re as $k1 => $v1 ){
                        if ( $v1['order_id'] == $k ){
                            $orders_ids[$k]['last_modified'] = $v1['last_modified'];
                        }
                    }
                    $orders_ids[$k]['goods_id'] = array_values( array_unique( $v ) );
                }
    
                // get goods number between 2 and 20
                $i = 0;
                foreach ( $orders_ids as $v ){
                    if ( 1 < count( $v['goods_id'] ) && 20 > count( $v['goods_id'] )){
                        $goods_ids[$i]['goods_id'] = $v['goods_id'];
                        $goods_ids[$i]['last_modified'] = $v['last_modified'];
                        $i++;
                    }
                }

                $z = 0;
                for ( $i = 0; $i < count( $goods_ids ); $i++ ){
                    for ( $j = 0; $j < count( $goods_ids[$i]['goods_id'] ); $j++ ){
                        $temp[$i][$j] = $goods_ids[$i]['goods_id'];
                        $arr[$z]['primary_goods_id'] = $temp[$i][$j][$j];
                        unset($temp[$i][$j][$j]);
                        $arr[$z]['last_modified'] = $goods_ids[$i]['last_modified'];					
                    
                        if (!is_array($temp[$i][$j]))
                            $arr[$z]['secondary_goods_id'] = $temp[$i][$j];
                        else{
                            $temp[$i][$j] = array_values( $temp[$i][$j] );
                            foreach ($temp[$i][$j] as $key=>$arr_goods_id){
                                $arr[$z]['secondary_goods_id'] = $arr_goods_id;
                                $z++;
                                if ($key < count($temp[$i][$j])-1){
                                    $arr[$z]['primary_goods_id'] = $arr[$z-1]['primary_goods_id'];
                                    $arr[$z]['last_modified'] = $arr[$z-1]['last_modified'];
                                }
                            }
                        }                  
                    }
                }
			
                $rec = app::get( 'recommended' ) -> model( 'goods' );
                foreach ( $arr as $v ){
                    if (!$rec -> replace($v,array('primary_goods_id'=>$v['primary_goods_id'],'secondary_goods_id'=>$v['secondary_goods_id']))){
                        $msg = app::get( 'recommended' )->_('更新数据失败');
                        return false;
                    }
                    else {
                        $this->app->setConf( 'recommended.timeline', $to );
                    }
                }
                
                $start += $step;    
            } else {
                break;
            }
            
            
        }
        return true;
    }
	
    /**
     * 移除数据
     * @param string message 错误消息
     * @return null
     */
    public function move(&$msg='')
    {
        // clear up table recommended_goods_period
        $period = $this->app->getConf('period');
        $str = "-" . $period ." Month";

        $from = strtotime( $str );
        $to   = strtotime( 'now' );

        $rec_goods = app::get( 'recommended' ) -> model( 'goods' );
        $rec_goods_period = app::get( 'recommended' ) -> model( 'goods_period' );
        $goods_filter['last_modified|between']  = array( $from, $to );
        $goods_orderby = array( 'last_modified', 'DESC' );
        $recommended_goods = $rec_goods->getList( '*', $goods_filter, 0, -1, $goods_orderby );

        if ( !$rec_goods_period->delete( array() ) ){
            $msg = app::get('recommended')->_('recommended_good_period表数据清空失败');
            return false;
        }
        
        foreach ( $recommended_goods as $v ){
            if ( !$rec_goods_period -> insert( $v ) ){
                $msg = app::get('recommended')->_('recommended_good_period表数据插入失败');
                return false;
            }
        }
		
        return true;
    }
}