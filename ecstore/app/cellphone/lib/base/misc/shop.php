<?php
class cellphone_base_misc_shop extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }
    //店铺信息
    public function getstoreinfo(){
         $params = $this->params;

         $must_params = array(
            'store_id'=>'店铺标识',
        );
        $this->check_params($must_params);
        $store_id = intval($params['store_id']);
        $picSize = in_array(strtolower($params['picSize']), array('cl', 'cs'))? strtolower($params['picSize']):'cl';

        $sInfo=app::get('business')->model('storemanger')->getRow('store_id,shop_name,store_name,area,image,fav_count,tel,zip,company_ctel',array('store_id'=>$store_id));

        $area = $sInfo['area'];
        $aryAre = split('/', $area);
        $stemp['pro'] = substr($aryAre[0],strpos($aryAre[0], ':')+1);
        $stemp['city'] =  $aryAre[1];
        $stemp['district'] = substr($aryAre[2], 0, strpos($aryAre[2], ':'));
        $sInfo['area'] =$stemp['pro'].$stemp['city'].$stemp['district'];

        $sInfo['img_url']=$this->get_img_url($sInfo['image'],$picSize);
		unset($sInfo['image']);
        if(!$sInfo||empty($sInfo)){
            $this->send(true,null,'没有找到店铺的相关信息');

        }else{

            $member = $this->get_current_member();
            $member_id = $member['member_id'];

            if($member_id > 0){
                $result = app::get('business')->model('member_stores')->getRow('*',array('store_id'=>$store_id,'member_id'=>$member_id));
                if(empty($result)){
                    $sInfo['is_fav']= false;
                }
                else{
                    $sInfo['is_fav']= true;
                }
                @app :: get('business')->model('store_view_history')->add_history($member_id,$store_id);

            }
            $this->send(true,$sInfo,'店铺信息');

		}

    }



 // 取到当前分类下的下一级分类
    public function getnextcat(){
         $params = $this->params;

         $must_params = array(
            'store_id'=>'店铺标识',
            
        );
        $this->check_params($must_params);
        $store_id = intval($params['store_id']);
		$arr = app::get('business')->model('storemanger')->getRow('store_id,shop_name',array('store_id'=>$store_id));
        if(empty($arr)){
	    $this->send(false,null,app::get('cellphone')->_('没有该店铺'));
	    } 
		$catlist = array();
		$mdl_goods_cat = app::get('business')->model('goods_cat');
		if($params['custom_cat_id']){

        $custom_cat_id = intval($params['custom_cat_id']);
		$filter = array('store_id'=>$store_id,'parent_id'=>$custom_cat_id);
		$catlist = $mdl_goods_cat->getList('store_id,custom_cat_id,cat_name',$filter);
		}
	 // 获得店铺的一级分类
	  else{
        $filter = array('store_id'=>$store_id,'parent_id'=>0);
        $catlist = $mdl_goods_cat->getList('store_id,custom_cat_id,cat_name',$filter);
		foreach($catlist as &$val){
		$childcat = $mdl_goods_cat->getList('cat_name',array('store_id'=>$store_id,'parent_id'=>$val['custom_cat_id']),0,5);
		foreach( $childcat as $v){
		$cat[] = $v['cat_name'];
		
		}
		$val['childcat'] = implode('/',$cat);
		unset($cat);
		}
	  
	  }
        if(!$catlist||empty($catlist)){
		$this->send(true,null,app::get('cellphone')->_('无下级分类'));
		}
	    $this->send(true,$catlist,app::get('cellphone')->_('分类列表'));
    }
   //根据分类获取商品列表 此时传入的分类ID 可以是一级分类或者是二级分类
    public  function getgoodsbycat(){

         $params = $this->params;

         $must_params = array(
            'store_id'=>'店铺标识',
            'custom_cat_id'=>'分类标识',
        );
        $this->check_params($must_params);
        $store_id = intval($params['store_id']);
        $custom_cat_id = intval($params['custom_cat_id']);
		$page = $params['nPage']? $params['nPage']: 1;
		$pageLimit=$params['pagelimit']? $params['pagelimit']:10;
		$picSize = $params['picSize']? $params['picSize']:'cs';
		$orderby = 'view_count desc';
		if($params['orderby']==7){
	     $orderby = 'view_count desc';// 按照人气降序排序
		}
	    if($params['orderby']==9){
         $orderby = 'buy_count desc';//按照销量 降序排序
		}
	    if($params['orderby']==4){  
		 $orderby = 'price desc';    // 按照价格降序排序
		}
		if($params['orderby']==5){
		 $orderby = 'price asc';    //按照价格升序排序
		}
		$storearr = app::get('business')->model('storemanger')->getRow('store_id,shop_name',array('store_id'=>$store_id));
        if(empty($storearr)){
	    $this->send(false,null,app::get('cellphone')->_('没有该店铺'));
	    } 
        // 此处判断 custom_cat_id=-99 的情况
		if($custom_cat_id==-99){
		
		   $list = $this->getgoods($store_id,$page,$pageLimit,$picSize,$orderby);
		   if($list['data']){
		   $this->send(true,$list,app::get('cellphone')->_('商品列表'));
		   }
		   $this->send(true,null,app::get('cellphone')->_('没有商品'));
		   
		}
	    $mdl_goods_cat = app::get('business')->model('goods_cat');
		$parent_id = $mdl_goods_cat->getRow('parent_id,store_id',array('custom_cat_id'=>$custom_cat_id,'store_id'=>$store_id));
		//判断 是一级分类的ID 还是二级分类的ID
		if(!empty($parent_id)&&empty($parent_id['parent_id'])){
	 
		$list = $this->gettopcatlist($store_id,$custom_cat_id,$picSize,$page,$pageLimit,$orderby);
		
		}
		
	    if(!empty($parent_id)&&!empty($parent_id['parent_id'])){
		
		$list = $this->getsecondcatlist($custom_cat_id,$picSize,$page,$pageLimit,$orderby);
		
		}
		if($list['data']){
		$this->send(true,$list,app::get('cellphone')->_('商品列表'));
		}
		$this->send(true,null,app::get('cellphone')->_('没有商品'));
	        
    }
	//获得店铺某个一级分类下的商品列表
	private function gettopcatlist($store_id,$cat_id,$picSize,$page,$pageLimit,$orderby='view_count desc'){
	
	//先要取到二级分类id 再找到所有的id下的商品 最后再找到该ID下直属的商品 最后返回所有的商品
	$mdl_goods_cat = app::get('business')->model('goods_cat');
	$secondcatlist = $mdl_goods_cat->getList('custom_cat_id',array('store_id'=>$store_id,'parent_id'=>$cat_id));
	$cat_ids[] = $cat_id;
	
	//有二级分类的时候
	if(!empty($secondcatlist)){
	foreach($secondcatlist as $val){
	$cat_ids[] = $val['custom_cat_id'];
	   }
	}
	$filter = implode(',',$cat_ids);
	$countsql = 'select count(a.goods_id) from sdb_b2c_goods as a  left join sdb_business_goods_cat_conn as b on a.goods_id=b.goods_id left join sdb_business_goods_cat as c on b.cat_id=c.custom_cat_id where c.custom_cat_id in ('.$filter.') ';
	$cout = $mdl_goods_cat->db->select($countsql);
	$count = $cout[0]['count(a.goods_id)'];
	
    $sql = 'select a.goods_id,a.name,a.price ,a.mktprice,a.buy_m_count,a.act_type,a.freight_bear,a.image_default_id,c.store_id from sdb_b2c_goods as a  left join sdb_business_goods_cat_conn as b on a.goods_id=b.goods_id left join sdb_business_goods_cat as c on b.cat_id=c.custom_cat_id where c.custom_cat_id in ('.$filter.') order by a.'.$orderby.' limit '.($page-1)*$pageLimit.','.$pageLimit;
   // echo '$filter='.$filter."</br>";
	//echo '$sql='.$sql;
	//exit;
	$data = $mdl_goods_cat->db->select($sql);
	if(!empty($data)){
    foreach( $data as &$val){
    $val['image_default_id'] = $this->get_img_url($val['image_default_id'],$picSize);
	$val['act_type'] = $val['act_type']=='package'?'normal':$val['act_type'];
	   }
	 }
	
	 return $this->get_response_result($count,$page,$pageLimit,$data);


	}
    //获得店铺某个二级分类下的商品列表
	private function getsecondcatlist($cat_id,$picSize,$page,$pageLimit,$orderby='view_count desc'){
	 
      $mdl_goods_cat_conn = app::get('business')->model('goods_cat_conn');
	  $count = $mdl_goods_cat_conn->count(array('cat_id'=>$cat_id));
      $goods_ids = $mdl_goods_cat_conn->getList('goods_id',array('cat_id'=>$cat_id));
	  if(!empty($goods_ids)){
	   $mdl_goods = app::get('b2c')->model('goods');
	   $goodslist = array();
	   $ids = array();
	   foreach( $goods_ids as $v){
	   $ids[] = $v['goods_id'];
	   }
	   $goodslist = $mdl_goods->getList('goods_id,store_id,name,price,mktprice,buy_m_count,image_default_id',array('goods_id'=>$ids),($page-1)*$pageLimit,$pageLimit,$orderby);
	   if(!empty($goodslist)){
           foreach( $goodslist as &$val){
             $val['image_default_id'] = $this->get_img_url($val['image_default_id'],$picSize);
             $val['act_type'] = $val['act_type']=='package'?'normal':$val['act_type'];
	          }
	     }
	   return $this->get_response_result($count,$page,$pageLimit,$goodslist);
	  }
	  return $this->get_response_result($count,$page,$pageLimit,$goods_ids);;
	
	
	}
	 private  function get_response_result($count,$page,$pagelimit,$data){
         $pager['limit']= $pagelimit;
         $pager['tPage']= ceil($count/intval($pagelimit));
         $pager['cPage']= $page;
         $pager['count']= $count;
         return array('page'=>$pager,'data'=>$data);
         
    }
    //获得店铺顶级分类下的商品列表包括店铺的一级分类的多维数组
    public function gettopcatgoods(){
         $params = $this->params;

         $must_params = array(
            'store_id'=>'店铺标识',
            
        );
        $this->check_params($must_params);
        $store_id = intval($params['store_id']);
		$page = $params['nPage']? $params['nPage']: 1;
		$pageLimit= $params['pagelimit']? $params['pagelimit']:10;
		$picSize = $params['picSize']? $params['picSize']:'cs';
		$arr = app::get('business')->model('storemanger')->getRow('store_id,shop_name',array('store_id'=>$store_id));
        if(empty($arr)){
	    $this->send(false,null,app::get('cellphone')->_('没有该店铺'));
	    } 
		$mdl_goods_cat  = app::get('business')->model('goods_cat');
		$topcatlist = $mdl_goods_cat->getList('store_id,custom_cat_id,cat_name',array('store_id'=>$store_id,'parent_id'=>0));
		if(empty($topcatlist)){
		$this->send(true,null,app::get('cellphone')->_('该店铺没有自定义分类'));
		}
		foreach( $topcatlist as &$val){
		$val['goods']= $this->gettopcatlist($store_id,intval($val['custom_cat_id']),$picSize,$page,$pageLimit);
		}
	    if($topcatlist){
		 $add = array();
		 $add['store_id'] = $store_id;
         $add['custom_cat_id'] = -99;
         $add['cat_name'] = '未分类商品';
	     $add['goods']=$this->getgoods($store_id,$page,$pageLimit,$picSize);
		 $topcatlist = array_merge($topcatlist,array($add));
	    $this->send(true,$topcatlist,app::get('cellphone')->_('分类商品列表'));
		}
	    $this->send(true,null,app::get('cellphone')->_('没有数据'));
       
    }
   
   //获得当前店铺下的没有参与自定义分类的所有商品
     private function getgoods($store_id,$page=1,$pageLimit=10,$picSize='cs',$orderby='view_count desc'){
     
		$mdl_store = app::get('business')->model('storemanger');
		$countsql = 'select count(a.goods_id) from sdb_b2c_goods as a where a.store_id='.$store_id.' and  a.goods_id not in( select b.goods_id from sdb_business_goods_cat_conn as b ) ';
		$cout = $mdl_store->db->select($countsql);
		$count = $cout[0]['count(a.goods_id)'];

		$sql = 'select a.goods_id,a.name,a.price ,a.mktprice,a.buy_m_count,a.act_type,a.freight_bear,a.image_default_id,a.store_id  from sdb_b2c_goods as a where a.store_id='.$store_id.' and  a.goods_id not in( select b.goods_id from sdb_business_goods_cat_conn as b ) order by a.'.$orderby.' limit '.($page-1)*$pageLimit.','.$pageLimit;
		
		$data = $mdl_store->db->select($sql);
 
		if(!empty($data)){
           foreach( $data as &$val){
             $val['image_default_id'] = $this->get_img_url($val['image_default_id'],$picSize);
	         $val['act_type'] = $val['act_type']=='package'?'normal':$val['act_type'];
	          }
	    }
        $list = $this->get_response_result($count,$page,$pageLimit,$data);
		return  $list;
     }
}