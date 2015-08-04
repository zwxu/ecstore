<?php
class business_mdl_store_view_history extends dbeav_model{
  function add_history($member_id=null,$store_id=null){
        if(!$member_id || !$store_id) return false;
        $filter['member_id'] = $member_id;
        $filter['store_id'] = $store_id;
        $sdf = array(
           'store_id' =>$store_id,
           'member_id' =>$member_id,
           'last_modify'=>time()
          );
          if($this->save($sdf)){
              return true;
          }
          else{
              return false;
          }
	}
	
	function get_history($member_id=null){
		if(!$member_id) return null;
		$oStore = &$this->app->model('storemanger');
		$fav = $this->db->select("SELECT member_stores.`store_id`, stores.fav_count 
									FROM ".$this->table_name(1)." AS member_stores
									INNER JOIN ".$oStore->table_name(1)." AS stores ON member_stores.`store_id`=stores.`store_id` 
									WHERE member_stores.`member_id`=".intval($member_id)." AND stores.`marketable`='true'");
        $result = implode(',',(array)array_map('current',$fav));
        if($result) $result = ','.$result;
        return $result;
	}

###删除浏览历史商品

     function del_history($member_id,$sid){
        $is_delete = false;
		$is_delete = $this->delete(array('store_id' => $sid,'member_id' => $member_id));
    
		return $is_delete;
     }
	 
	 function count($filter=null){
		if (!$filter || !$filter['member_id']) return 0;
		
		$oStore = &$this->app->model('storemanger');
		$count = $this->db->selectrow("SELECT COUNT(member_stores.`store_id`) AS num 
									FROM ".$this->table_name(1)." AS member_stores
									INNER JOIN ".$oStore->table_name(1)." AS stores ON member_stores.`store_id`=stores.`store_id` 
									WHERE member_stores.`member_id`=".intval($filter['member_id']));
		
		return $count['num'];
	 }
     
####根据会员ID获得该会员浏览历史的商品

    function get_view_history($member_id=null,$page=1,$num=10){
        if(!$member_id){
           return array();
        }
        $count = $this->count(array('member_id'=>$member_id));
        if( !$num ) $num = 10;
        $maxPage = ceil($count / $num);
        if($page > $maxPage)
        $page=$maxPage;
        $start = ($page-1) * $num;
        $start = $start<0 ? 0 : $start;
        $aSid = $this->select()->columns(array('store_id','last_modify'))
                    ->where('member_id=?',$member_id)->order(array('last_modify DESC'))->limit($start,$num)->instance()->fetch_all();
        
        $atsid = array();
        foreach($aSid as $val){
            $atsid[]= intval($val['store_id']);
            $params['data'][$val['store_id']] = array();
        }

        $oStore = &$this->app->model('storemanger');
        if(is_array($atsid)&&$atsid){
            $aStore = $oStore->getList('shop_name,store_name,store_id,image',array('store_id' => $atsid));
            foreach ($aStore as $val) {
                
                $val['image_default_id'] = $val['image'];
                $params['data'][$val['store_id']] = $val;
            }
            $params['data'] = array_filter($params['data']);
            $params['totalpage'] = $maxPage;
            $params['currentpage'] = $page;
            return $params;
        }else{
            return false;
        }
    }
}