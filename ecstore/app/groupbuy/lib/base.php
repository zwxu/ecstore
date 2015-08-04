<?php

class groupbuy_base
{
    function __construct(&$app){
       $this->app=$app;
    }
    public function get_list($filter=array(),$npage=1){    
        $list_listnum =12; 
        $data=$this->getInfo('*',$filter);
        $count=count($data);
        $maxPage = ceil($count / $list_listnum);
        if($npage > $maxPage) $npage = $maxPage;
        $start = ($npage-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $this->getInfo('*',$filter,$start,$list_listnum);

        $token = '';
        $arrPager = array(
            'current' => $npage,
            'total' => $maxPage,
            'token' => $token,
        );
	    $params['pager'] = $arrPager;
		return $params;
    }
    public function getInfo($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
         $sql = "select gd.name as g_name,gd.image_default_id as g_image,c.cat_name,a.start_time,a.end_time,g.id,g.gid,g.cat_id,g.aid,g.last_price,g.nums,g.remainnums,g.personlimit,gd.price as g_price  from sdb_groupbuy_groupapply as g".
        " join sdb_b2c_goods_cat as c on g.cat_id=c.cat_id and g.status='2'".
        " join sdb_groupbuy_activity as a on g.aid=a.act_id".
        " join sdb_b2c_goods as gd on g.gid=gd.goods_id".
        " where g.status='2' and a.act_open='true'";
       
        if($filter['cat_id']){
          $sql=$sql.' and g.cat_id='.$filter['cat_id'];
        }
		if($filter['price']=='all'){
			$sql=$sql;
		}
        if($filter['price']=='1'){
           $sql=$sql.' and g.last_price between 0 and 99';
        }
		 if($filter['price']=='2'){
           $sql=$sql.' and g.last_price between 100 and 199';
        }
		 if($filter['price']=='3'){
           $sql=$sql.' and g.last_price between 200 and 499';
        }
		 if($filter['price']=='4'){
           $sql=$sql.' and g.last_price between 500 and 999';
        }
		 if($filter['price']=='5'){
           $sql=$sql.' and g.last_price >=1000';
        }

        $rs = app::get('groupbuy')->model('activity')->db->selectLimit($sql,$limit,$offset);
		
		$result=array();
		if($rs && !empty($rs)){
			foreach($rs as $k=>$v){
				$result[$k]['g_name']=$v['g_name'];
				$result[$k]['cat_name']=$v['cat_name'];
				$result[$k]['start_time']=$v['start_time'];
				$result[$k]['end_time']=$v['end_time'];
				$result[$k]['id']=$v['id'];
				$result[$k]['gid']=$v['gid'];
				$result[$k]['cat_id']=$v['cat_id'];
				$result[$k]['aid']=$v['aid'];
				$result[$k]['last_price']=$v['last_price'];
				$result[$k]['nums']=$v['nums'];
				$result[$k]['remainnums']=$v['remainnums'];
				$result[$k]['personlimit']=$v['personlimit'];
				$result[$k]['g_price']=$v['g_price'];	
				$imageDefault = app::get('image')->getConf('image.set');
				$oImage = app::get('image')->model('image');
				$img=$oImage->getList("image_id",array('image_id'=>$v['g_image']));
			    $result[$k]['image']=$img[0]['image_id'];
			    $result[$k]['args'] = array($v['gid'],'','',$v['id']);
			}
			return $result;
    }
	return $result;
}
}