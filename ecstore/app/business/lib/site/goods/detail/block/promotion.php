<?php


class business_site_goods_detail_block_promotion {

    public function __construct( $app ) {
        $this->app = $app;
    }

    public function get_blocks($params = array(),$arr_member_info=null) {
        $goods_id = $params['promotion']['goods_id'];
        if(!$goods_id) return false;

        $time = time();
        $activity = app::get('timedbuy')->model('activity');
        $businessActivity = app::get('timedbuy')->model('businessactivity');
        $sql = "select b.price,b.aid,b.gid,a.name,a.description from ".$businessActivity->table_name(1)." as b join ".$activity->table_name(1)." as a on b.aid = a.act_id and a.act_open='true' ".
            " and a.start_time<={$time} and a.end_time>{$time} where b.status='2' and b.gid='{$goods_id}'";
        
        $aResult = $businessActivity->db->select($sql);
        return $aResult;
    }
}
