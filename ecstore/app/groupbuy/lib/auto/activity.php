<?php
class groupbuy_auto_activity {
    function exec_auto(){
        $nowTime = time();
        $activityObj = app::get('groupbuy')->model('activity');
        $applyObj = app::get('groupbuy')->model('groupapply');
        $gObj = app::get('b2c')->model('goods');

        $sql = "select a.* from sdb_groupbuy_groupapply as a".
        " join sdb_groupbuy_activity as s on s.act_id=a.aid".
        " where a.status='2' and s.act_open='true' and {$nowTime} > s.end_time and s.act_status != '2'"; 
        $applys = $applyObj->db->select($sql);
        if($applys){
            foreach($applys as $k=>$v){
                $garr = array('store_freeze'=>0,'act_type'=>'normal');
                $gObj->update($garr,array('goods_id'=>$v['gid']));
                $act_arr = array('act_status'=>'2','act_open'=>'false');
                $activityObj->update($act_arr,array('act_id'=>$v['aid']));
            }
        }

        echo 'succ';

    }
}