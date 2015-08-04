<?php


/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_homepage_column extends cellphone_cellphone{
	var $pageLimit = 10;
	var $page=1;
	var $picSize = 'cs';
	
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }



//  获取专栏类型列表
    function gettypelist(){

	 $params = $this->params;
	  if($params['pagelimit']){
            $pagelimit=$params['pagelimit'];
        }else{
            $pagelimit = $this->pageLimit;
        }

        if($params['nPage']){
            $nPage=$params['nPage'];
        }else{
            $nPage = $this->page;
        }
	 $columntype= app::get('cellphone')->model('columntype');
	 $typedata = $columntype->getList('columntype_id,columntype_name,columntype_description,css_type,d_order',array(), $pagelimit*($nPage-1),$pagelimit,'d_order asc');
     if(!$typedata){
	 
	 $this->send(true,null,app::get('cellphone')->_('没有数据'));
	 
	 }
	 else{
	
	$this->send(true,$typedata,app::get('cellphone')->_('栏目类型'));
	
	}
	
	
	}
	//传入类目类型获得其相应的列表数据
    function getlist(){
	
	   $params = $this->params;
	   $must_params = array(
            'columntype_id'=>'栏目类型',
		    'css_type'=>'样式标识',
        );
       $this->check_params($must_params);
	   $columntype_id = intval($params['columntype_id']);
	   $css_type = $params['css_type'];

	  if(!isset($params['pagelimit']) || empty($params['pagelimit'])){
   	   	  $params['pagelimit'] = $this->pageLimit;
   	   }
   	   if(!isset($params['nPage']) || empty($params['nPage'])){
   	   	  $params['nPage'] = $this->page;
   	   }
   	   if(!isset($params['picSize']) || empty($params['picSize'])){
   	   	  $params['picSize'] = $this->picSize;
   	   }
   	   if($params['picSize'] != 'cs' && $params['picSize'] != 'cl'){
   	   	  $params['picSize'] = $this->picSize;
   	   }
  
	  $data = array();
	 
	// 返回样式1下的数据结构
	  if($css_type=='1'){
       $data = $this->getfirstlist($columntype_id,$params['nPage'],$params['pagelimit'], $params['picSize']);
       if($data){
		    
	    $this->send(true,$data,app::get('cellphone')->_('商品列表'));
	    }
		else{
		$this->send(true,null,app::get('cellphone')->_('没有数据'));
		}

	 
	 
	  } 
	//返回样式2 下的数据结构
	  if($css_type=='2'){
	   $data = $this->getsecondlist($columntype_id,$params['nPage'],$params['pagelimit'], $params['picSize']);
       if($data){
	    $this->send(true,$data,app::get('cellphone')->_('店铺商品列表'));
	    }
		else{
		$this->send(true,null,app::get('cellphone')->_('没有数据'));
		}
	 
	  }
	  else{
	  $this->send(false,null,app::get('cellphone')->_('参数错误'));
	  
	  }
	
	
	}   
	

	// 样式1下的数据结构
    private function getfirstlist ($type_id,$page,$pageLimit,$picSize){
	 
	  $mdl_column = app::get('cellphone')->model('column');
	  $curtime=time();
	  $data = $mdl_column->getList('image_id,goods_id',array('columntype_id'=>$type_id,'is_active'=>'true','start_time|lthan'=>$curtime,'end_time|than'=>$curtime),($page-1)*$pageLimit,$pageLimit,'d_order ASC');  
	  if($data){
		$mdl_goods = app::get('b2c')->model('goods');
	
		 foreach($data as $key=>&$val){
			
		 $val['image_id'] = $this->get_img_url($val['image_id'],$picSize);
		 $arr = $mdl_goods->getList('name,price,mktprice',array('goods_id'=>intval($val['goods_id'])));
		 $val['name'] = $arr[0]['name'];
		 $val['price'] = $arr[0]['price'];
		 $val['mktprice'] =$arr[0]['mktprice'];


		 }
		
	    return $data;
	  }
      return $data; 
	}


    //样式2 下的数据结构
	private function getsecondlist($type_id,$page,$pageLimit,$picSize){
	
	 $mdl_column = app::get('cellphone')->model('column');
	 $curtime=time();
	 $filter = ' where c.is_active="true" and  c.start_time<='.$curtime.'  and c.end_time >='.$curtime.'  and c.columntype_id='.$type_id;

	 $sql = ' select  c.goods_id ,c.image_id, g.name,g.price,g.mktprice,g.store_id,s.store_name,s.remark  from sdb_cellphone_column as c  left join sdb_b2c_goods  as g  on c.goods_id =g.goods_id
left join sdb_business_storemanger   as s on   g.store_id= s.store_id '.$filter.' order by c.d_order ASC';
	 $data = $mdl_column->db->select($sql);

     if($data){
	 foreach($data as &$val){
	 $val['image_id'] = $this->get_img_url($val['image_id'],$picSize);
	 
	 }

     $list = array();
	
	 foreach($data as $key=>$v){
		 if(count($list[$v['store_id']])>=3){
		continue;
		}
		$list[$v['store_id']][]=$v;
		
	 }

	 foreach( $list as $value ){
	    $datalist[] =$value;  
	 }
	 //$datalist 中存放所有的店铺及商品信息 并对                         其进行分页
	
     $limitdatalist = array_slice($datalist,($page-1)*$pageLimit,$pageLimit);
	 unset($data);
	 unset($list);

	   return $limitdatalist;
	 }
    return $data;
  }
  

}