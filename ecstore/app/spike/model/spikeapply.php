<?php
class spike_mdl_spikeapply extends dbeav_model{

    function getOnActIdByGoodsId($goods_id){
        $nowTime = time();
        $sql = "select a.id as act_id from sdb_spike_spikeapply as a".
        " join sdb_spike_activity as s on s.act_id=a.aid".
        " where a.gid={$goods_id} and a.status='2' and s.act_open='true' and {$nowTime} >= s.start_time and {$nowTime} <= s.end_time";
        $result = $this->db->selectrow($sql);
        if ($result && !empty($result)){
            return $result['act_id'];
        }else{
            return false;
        }

    }

    function getNoEndActIdByGoodsId($goods_id){
        $nowTime = time();
        $sql = "select a.id as act_id from sdb_spike_spikeapply as a".
        " join sdb_spike_activity as s on s.act_id=a.aid".
        " where a.gid={$goods_id} and a.status='2' and s.act_open='true' and {$nowTime} <= s.end_time";
        $result = $this->db->selectrow($sql);
        if ($result && !empty($result)){
            return $result['act_id'];
        }else{
            return false;
        }

    }

    function getOnActGIdByStatus($status){
        $nowTime = time();
        $sql = "select a.gid from sdb_spike_spikeapply as a".
        " join sdb_spike_activity as s on s.act_id=a.aid".
        " where a.status={$status} and s.act_open='true' and {$nowTime} <= s.end_time";
        $result = $this->db->select($sql);
        if ($result && !empty($result)){
            foreach($result as $k=>$v){
                $goods_ids[] = $v['gid'];
            }
            return $goods_ids;
        }else{
            return false;
        }

    }

    function loadActInfoById($id){
        $sql = "select a.gid,a.cat_id,a.price,a.last_price,a.nums as act_store,a.remainnums,a.personlimit,a.status,a.remark,a.act_desc,".
        "s.name,s.description,s.start_time,s.end_time,s.act_open ".
        "from sdb_spike_spikeapply as a".
        " join sdb_spike_activity as s on s.act_id=a.aid".
        " where a.id={$id}";
        $result = $this->db->selectrow($sql);

        return $result;
    }

    function _filter($filter,$tbase=''){
    	//活动名称搜索wxq
    	if(isset($filter['actname']) && $filter['actname'] != ''){
    		$actObj = app::get('spike')->model('activity');
    		$actInfo = $actObj->getList('act_id',array('name|has'=>$filter['actname']));
    		$act_ids = array();
    		foreach($actInfo as $k=>$v){
    			$act_ids[] = $v['act_id'];
    		}
    		unset($filter['actname']);
    		$filter['aid'] = $act_ids;
    	}
        //按商品名称搜索
        if(isset($filter['gname']) && $filter['gname'] != ''){
            $gObj = app::get('b2c')->model('goods');
            $goods = $gObj->getList('goods_id',array('name|has'=>$filter['gname']));
            $gids = array();
            foreach($goods as $k=>$v){
                $gids[] = $v['goods_id'];
            }
            unset($filter['gname']);
            $filter['gid'] = $gids;
        }

        //按商品名称搜索
        if(isset($filter['stname']) && $filter['stname'] != ''){
            $stObj = app::get('business')->model('storemanger');
            $stores = $stObj->getList('store_id',array('store_name|has'=>$filter['stname']));
            $store_ids = array();
            foreach($stores as $k=>$v){
                $store_ids[] = $v['store_id'];
            }
            unset($filter['stname']);
            $filter['store_id'] = $store_ids;
        }

        if($this->use_meta){
            foreach(array_keys((array)$filter) as $col){
                if(in_array(strval($col),$this->metaColumn)){
                    $meta_filter[$col] = $filter[$col];
                    unset($filter[$col]);  #ȥfilterаmeta
                    $obj_meta = new dbeav_meta($this->table_name(true),$col);
                    $meta_filter_ret .= $obj_meta->filter($meta_filter);
                }
            }
        }
        $dbeav_filter = kernel::single('dbeav_filter');
        $dbeav_filter_ret = $dbeav_filter->dbeav_filter_parser($filter,$tableAlias,$baseWhere,$this);
        if($this->use_meta){
            return $dbeav_filter_ret.$meta_filter_ret;
        }
        return $dbeav_filter_ret;
    }

    function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                $columns[$k] = $v['label'];
            }
        }

        $virtul = array('gname'=>'商品名称','stname'=>'店铺名称','actname'=>'活动名称');

        return array_merge($columns,$virtul);
    }
}