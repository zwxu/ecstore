<?php
class business_mdl_member_stores extends dbeav_model{
  function add_fav($member_id=null,$object_type='stores',$store_id=null){
        if(!$member_id || !$store_id) return false;
        $filter['member_id'] = $member_id;
        $filter['store_id'] = $store_id;
        if($row = $this->getList('snotify_id',$filter))
            return true;
        $sdf = array(
           'store_id' =>$store_id,
           'member_id' =>$member_id,
           'status' =>'ready',
           'create_time' => time(),
           'object_type'=> $object_type,
          );
          if($this->save($sdf)){
              $this->db->exec("UPDATE sdb_business_storemanger SET fav_count = fav_count+".intval(1)." WHERE store_id =".intval($store_id));
              return true;
          }
          else{
              return false;
          }
	}
	
	function get_member_fav($member_id=null){
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

###删除收藏商品

     function delFav($member_id,$sid,&$page=null,$num=10){
        $is_delete = false;
		$is_delete = $this->delete(array('store_id' => $sid,'member_id' => $member_id));
    if(!is_array($sid)) $sid = array(intval($sid));
    if(!empty($sid))
    $this->db->exec("UPDATE sdb_business_storemanger SET fav_count = fav_count-".intval(1)." WHERE store_id in(".implode(',',$sid).") and fav_count>0");
		/** 得到当前会员分页数 **/
		$count = $this->count(array('member_id'=>$member_id));
		$page = ceil($count / $num);

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
     
     function delAllFav($member_id){
        $oStore = &$this->app->model('storemanger');
        $sql = "update ".$oStore->table_name(1)." set fav_count = fav_count-".intval(1)." where store_id in (select store_id from ".$this->table_name(1)." where member_id=".intval($member_id).") and fav_count>0";
        $this->db->exec($sql);
        return $this->delete(array('member_id' => $member_id));
     }

####根据会员ID获得该会员收藏的商品

    function get_favorite($member_id,$member_lv_id,$page=1,$num=10){
        $count = $this->count(array('member_id'=>$member_id));
        if( !$num ) $num = 10;
        $maxPage = ceil($count / $num);
        if($page > $maxPage) return array();
        $start = ($page-1) * $num;
        $start = $start<0 ? 0 : $start;
        $aSid = $this->select()->columns(array('store_id','create_time'))
                    ->where('member_id=?',$member_id)
                    ->where('object_type=?','stores')->order(array('create_time DESC'))->limit($start,$num)->instance()->fetch_all();
        
        $asid = array();
        foreach($aSid as $val){
            $asid[]= intval($val['store_id']);
            $params['data'][$val['store_id']] = array();
        }

        $oStore = &$this->app->model('storemanger');
        if(is_array($asid)&&$asid){
            $aStore = $oStore->getList('shop_name,account_id,store_name,store_id,image,fav_count',array('store_id' => $asid));
            $sql = "select t.*,p.store_id,p.avg_point from sdb_b2c_comment_goods_type as t left join sdb_business_comment_stores_point as p on t.type_id=p.type_id and p.store_id in (".implode(',',$asid).") order by p.store_id,t.type_id";
            $point = array();
            foreach($this->db->select($sql) as $rows){
                $point[$rows['store_id']][$rows['type_id']] = array('type_id'=>$rows['type_id'],'type_name'=>$rows['name'],'avg_point'=>$rows['avg_point']);
            }
            $oImage = app::get('image')->model('image');
            foreach ($aStore as $val) {
                // 判断图片是否存在
                //$image_default_id = $oImage->select()->columns(array('image_id'))
                                   //->where('image_id=?',$val['image'])->instance()->fetch_one();
                if (empty($image_default_id)) {
                    //$val['image_default_id'] = '';
                }
                $val['image_default_id'] = $val['image'];
                if(array_key_exists($val['store_id'],$point)){
                    $val['store_point'] = $point[$val['store_id']];
                }else{
                    $val['store_point'] = array();
                }
                $params['data'][$val['store_id']] = $val;
            }
            $params['data'] = array_filter($params['data']);
            $params['page'] = $maxPage;
            return $params;
        }else{
            return false;
        }
    }
}