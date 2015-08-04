<?php
class package_auto_activity {
    function exec_auto(){
        $nowTime = time();
        $activityObj = app::get('package')->model('activity');
        $gObj = app::get('b2c')->model('goods');

        $sql = "select a.* from sdb_package_attendactivity as a".
        " join sdb_package_activity as s on s.act_id=a.aid".
        " where a.status='2' and s.act_open='true' and {$nowTime} > s.end_time and s.act_status != 2"; 
        $applys = $activityObj->db->select($sql);
        if($applys){
            foreach($applys as $k=>$v){
                $act_arr = array('act_status'=>2);
                $activityObj->update($act_arr,array('act_id'=>$v['aid']));
                $garr = array('act_type'=>'normal');
                $v['gid'] = array_filter(explode(',',$v['gid']));
                if($v['gid'])
                $gObj->update($garr,array('goods_id'=>$v['gid']));
            }
        }

        echo 'succ';

    }
}