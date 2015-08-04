<?php
class scorebuy_order_score{

    //检索积分是否足够
    function isScoreEnough($gid,$num,$member_id){
        // 得到当前会员的积分
        $obj_members = app::get('b2c')->model('members');
        $arr_member = $obj_members->dump($member_id, 'point,member_lv_id');
        $member_point = $arr_member['score']['total'];
        $obj_extend_point = kernel::service('b2c.member_extend_point_info');
        if ($obj_extend_point){
            // 当前会员实际可以使用的积分
            $obj_extend_point->get_usage_point($member_id, $member_point);
        }

        //商品使用积分
        $applyObj = app::get('scorebuy')->model('scoreapply');
        $aid = $applyObj->getOnActIdByGoodsId($gid);
        $apply = $applyObj->dump(array('id'=>$aid),'*');

        if($apply['isMemLv'] == '1'){
            $memLvScoreObj = app::get('scorebuy')->model('memberlvscore');
            $memLvs = $memLvScoreObj->getMemLvScoreByIds($aid,$gid);
            $subtotal_consume_score += $memLvs[$arr_member['member_lv']['member_group_id']]['score'] * $num;
        }else{
            $subtotal_consume_score += $apply['score'] * $num;
        }

        if($subtotal_consume_score > $member_point){
            return false;
        }

        return true;

    }
}