
<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_homepage_banner extends cellphone_cellphone{
	var $pageLimit = 5;
	var $page=1;
	var $picSize = 'cs';
	
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

   //获取banner轮播区域启用的内容
    function getbannerlist(){
	
	 $params = $this->params;

   	   if(!isset($params['pageLimit']) || empty($params['pageLimit'])){
   	   	  $params['pageLimit'] = $this->pageLimit;
   	   }
   	   if(!isset($params['npage']) || empty($params['npage'])){
   	   	  $params['npage'] = $this->page;
   	   }
   	   if(!isset($params['picSize']) || empty($params['picSize'])){
   	   	  $params['picSize'] = $this->picSize;
   	   }
   	   if($params['picSize'] != 'cs' && $params['picSize'] != 'cl'){
   	   	  $params['picSize'] = $this->picSize;
   	   }

	  $banner = app::get('cellphone')->model('banner');
	  $curtime = time();
      $data = 
	  $banner->getList('associate_id,associate_type,image_id,d_order',array('is_active'=>'true','start_time|lthan'=>$curtime,'end_time|than'=>$curtime),($params['npage']-1)*$params['pageLimit'],$params['pageLimit'],'d_order ASC');
	  if($data){
		
		 foreach($data as $key=>&$val){
         //echo '<pre>';
			
		 $val['image_id']=$this->get_img_url($val['image_id'],$picSize);
		
		 }
		
	  $this->send(true,$data,app::get('cellphone')->_('轮播列表'));
	  }
	
      else{
	  
	  $this->send(true,null,app::get('cellphone')->_('没有数据'));
	  }
	}

    

    //获取文章内容接口
    function getarticle(){

     $params = $this->params;
     $must_params = array(
            'article_id'=>'文章ID'
        );
     $this->check_params($must_params);
	 if(!isset($params['picSize']) || empty($params['picSize'])){
   	   	  $params['picSize'] = $this->picSize;
   	   }
   	 if($params['picSize'] != 'cs' && $params['picSize'] != 'cl'){
   	   	  $params['picSize'] = $this->picSize;
   	   }
	 $article_id = intval($params['article_id']);
	 $mdl_article_indexs = app::get('content')->model('article_indexs');
	 $sql = ' select  a.article_id ,a.title, a.author,a.pubtime,a.uptime,b.content,b.length,b.image_id  from sdb_content_article_indexs as  a  left join sdb_content_article_bodys as b  on a.article_id =b.article_id 
where a.article_id ='.$article_id;
	 $data = $mdl_article_indexs->db->select($sql);
	 
	 if(!empty($data[0]['image_id'])){
     $data[0]['image_id']=$this->get_img_url(&$data[0]['image_id'],$params['picSize']);
	 }
	 if($data[0]){
     $this->send(true,$data[0],app::get('cellphone')->_('文章内容'));
	}
     $this->send(true,null,app::get('cellphone')->_('没有数据'));
 }

}
