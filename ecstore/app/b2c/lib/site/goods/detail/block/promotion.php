<?php


class b2c_site_goods_detail_block_promotion {

    public function __construct( $app ) {
        $this->app = $app;
    }

    public function get_blocks($params = array(),$arr_member_info=null) {
        $goods_id = $params['promotion']['goods_id'];
        if(!$goods_id) return false;

        $time = time();

        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $aResult = $this->app->model('goods_promotion_ref')->getList('*', array('goods_id'=>$goods_id, 'from_time|sthan'=>$time, 'to_time|bthan'=>$time,'status'=>'true'),0,-1,$order );
        if(!$aResult) return false;
        //$arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        //if( empty($arr_member_info) || empty($arr_member_info['member_lv']) ) $m_lv = -1;
        //else $m_lv = $arr_member_info['member_lv'];
        foreach($aResult as $row) {
            //if( empty($row['member_lv_ids']) ) continue;
            //if( !in_array( $m_lv, explode(',',$row['member_lv_ids']) ) ) continue;
            $arr[] = $row;
            if( $row['stop_rules_processing']=='true' ) break;
        }

        $return = array();
        foreach( (array)$arr as $key => $row ) {
            $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
            foreach($temp as $key => $val) {
                $obj = kernel::single($key);
                $obj->setString($val);
                $return[$key]['name'] = ( $row['description'] ? $row['description'] : $obj->getString() );
                $return[$key]['member_lv_ids'] = $row['member_lv_ids'];
            }
        }
        return $return;
    }

    /**
    * 获取指定商品满足的商品优惠信息 避免重复读取数据库 
    **/
    /*
    public function get_blocks_kv($params = array())
    {
        $goods_id = $params['promotion']['goods_id'];
        if(!$goods_id) return false;
        $time = time();
        if(base_kvstore::instance('b2c_sale_goods_info')->fetch('b2c_sale_goods_info_'.$goods_id, $aResult)!== false){
            $arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
            if( empty($arr_member_info) || empty($arr_member_info['member_lv']) ) $m_lv = -1;
            else $m_lv = $arr_member_info['member_lv'];
            foreach($aResult as $row) {
                if($row['status'] !='true') continue;
                if( $row['from_time'] > $time || $time > $row['to_time']) continue; //过滤时间
                if( empty($row['member_lv_ids']) ) continue;
                if( !in_array( $m_lv, explode(',',$row['member_lv_ids']) ) ) continue;
                $arr[] = $row;
                if( $row['stop_rules_processing']=='true' ) break;
            }

            $return = array();
            foreach( (array)$arr as $row ) {
                $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
                foreach($temp as $key => $val) {
                    $obj = kernel::single($key);
                    $obj->setString($val);
                    $return[] = ( $row['description'] ? $row['description'] : $obj->getString() );
                }
            }
            return implode('<br>', $return);
        }
        else{
            return $this->get_blocks($params);

        }

    }
    */
}
