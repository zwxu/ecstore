<?php
class scorebuy_mdl_memberlvscore extends dbeav_model{
    function getMemLvScoreByIds($aid,$gid){
        $memberLvs = $this->getList('*',array('aid'=>$aid,'gid'=>$gid));
        $memlvObj = app::get('b2c')->model('member_lv');
        $memLvinfo = $memlvObj->getList('member_lv_id,name',array('display'=>'false'));
        $memLvs = array();
        if($memberLvs){
            foreach($memberLvs as $k=>$v){
                $memLvs[$v['level_id']] = $v;
            }
            foreach($memLvinfo as $key=>$value){
                $memLvs[$value['member_lv_id']]['name'] = $value['name'];
            }
            return $memLvs;
        }else{
            return false;
        }
    }
}