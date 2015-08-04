<?php
  
class cellphone_shop_search extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->b2c=app::get('b2c');
        $this->objGoods = $this->b2c->model('goods');
        $this->productCat = $this->b2c->model('goods_cat');
        $this->mdl_store=app::get('business')->model('storemanger');
    }
    public function get(){
        $params = $this->params;        
        //echo '<pre>';
        //print_r($params);
        $this->_check($this->params);
        $data=$this->search();
        
    }
    protected function get_search_type(){
        return 's';
    }
    function microtime_float(){
        list($usec, $sec) = explode(" ", microtime());
        $t=((float)$usec + (float)$sec);
        if($this->float_t){
            echo (float)($t-$this->float_t),'<br>';
        }else{
            echo (float)$t,'<br>';
        }
        $this->float_t=$t;
    }
    private function search(){
        $filter=$this->get_filter($params);
        $st=$this->get_search_type();
        $scount=0;
        foreach($filter as $key=>$v){
            if(substr($key,0,2)==='p_'){
               $scount++;
            }
        }
        $pager=$this->get_page();
        $orderby=$this->get_order();
        if(app::get('base')->getConf('server.search_server.search_goods')){
           $searchApp = search_core::instance('search_goods');
           $sfilter['filter'] = $filter;
           $sfilter['from'] = $pager['pagelimit']*($pager['page']-1);     //
           $sfilter['to'] = $pager['pagelimit'];
           $sfilter['order'] = $orderby;
           $sfilter['scount'] = $scount;
           $sfilter['st']=$st;
        }
        //print_r($sfilter);
        $goods_count=0;
        $count=0;
        $res = false;   //
        if(is_object($searchApp)){
            $queryRes = $searchApp->phone_query_store($sfilter);
            if($queryRes){
                $res = $searchApp->commit();
                if(!empty($res['store'])){
                    $store_filter['store_id']=array_keys($res['store']);
                    $search_data=$this->mdl_store->getList('store_id,store_name,image,area,store_grade',$store_filter); 
                   
                    $count=count($res['all_store']);
                    $goods_count = array_sum($res['all_store']);
                    $search_data=$this->format_shop($search_data,$res);
                }else{                    
                    $search_data=array();
                }               
            }
        }
        if($res === false){            
            if (isset($filter['tag'][0])&&!$filter['tag'][0])
                unset($filter['tag']);
            if(!empty($filter['name'][0])){
                $segmentObj = search_core::segment();
                $segmentObj->pre_filter(new search_service_filter_cjk);
                $segmentObj->token_filter(new search_service_filter_lowercase);
                $segmentObj->set($filter['name'][0], 'utf8');
                while($row = $segmentObj->next()){
                    $res[] = $row['text'];
                }
                $filter['name'][0]=implode(' ',$res);
            }
            $arr_cat_id=array();
            //默认分类要考虑分类相关性，鸡肋呀。
            if(empty($orderby)){
                if($filter['name'][0]){
                    $_catfilter = array(
                        'cat_name|has'=>$filter['name'][0],
                        'disabled'=>'false',
                    );                    
                    $arr_cat_id=$this->productCat->getList('cat_id',$_catfilter);
                    $arr_cat_id=utils::array_change_key($arr_cat_id,'cat_id', 0);
                    $arr_cat_id=array_keys($arr_cat_id);
                }
            }
            if($st=='s'){
                $filter['store_name']=$filter['name'][0];
            }
            $tmp_filter['str_where'] = $this->objGoods->_extend_filter($filter);
            //过滤掉店铺未审核、或者状态为不可用或者是过期的店铺对应的商品。
            $tmp_filter['str_where']=$tmp_filter['str_where'].' and '.$this->_filterStore();
            if(empty($orderby)){   
                $orderby=' dorder desc';
            }
            $ttemp_product=$this->objGoods->getSearchGoods('`sdb_b2c_goods`.store_id,`sdb_b2c_goods`.goods_id',$tmp_filter,0,-1,$orderby,$arr_cat_id,$temp);
            if(!empty($ttemp_product)){
                $temp_store_array=utils::array_change_key($ttemp_product,'store_id', 1);
                $arr_store_id=array_unique(array_map('current',$ttemp_product));//array_keys($temp_store_array);
                $count=count($arr_store_id);
                $limit_store_id=array_slice($arr_store_id,$pager['pagelimit']*($pager['page']-1),$pager['pagelimit']);
                $goods_count=count($ttemp_product);
                $shop_goods_count=array();
                foreach($temp_store_array as $key=>$v){
                    if(in_array($key,$limit_store_id)){
                        $shop_goods_count[$key]=count($v);
                    }
                }
                $store_filter['store_id']=$limit_store_id;
                $search_data=$this->mdl_store->getList('store_id,store_name,image,area,store_grade',$store_filter); 
                $search_data=$this->format_shop($search_data,array('store'=>$shop_goods_count));
            }else{
                $search_data=array();
            }            
        }
        $result=$this->get_response_result($count,$pager,$search_data,$goods_count);
        
        $this->send(true, $result, app::get('b2c')->_('success'));
    }
    function format_shop($data=array(),$res=array()){
        $store_filter['store_id']=array_keys($res['store']);
        $business_brand=app::get('business')->model('brand')->getList('brand_name,store_id',$store_filter);
        $storegrade=app::get('business')->model('storegrade')->getlist('grade_id,issue_type');
        $storegrade=utils::array_change_key($storegrade,'grade_id', 0);
        $brands=utils::array_change_key($business_brand,'store_id', 1);
        foreach($data as $key=>&$store){
            $store['logo']=base_storager::image_path($store['image'],'s');
            unset($store['image']);
            $store['goods_count']=$res['store'][$store['store_id']];
            $store['issue_type']=$storegrade[$store['store_grade']]['issue_type'];
            $store['brand']=array_map('current',$brands[$store['store_id']]);
            unset($store['store_grade']);
        }
        return $data;
    }
    function get_response_result($count,$qpage,$data,$goods_count){
         $page['limit']=$qpage['pagelimit'];
         $page['tPage']=ceil($count/intval($qpage['pagelimit']));
         $page['cPage']=$qpage['page'];
         $page['count']=$count;
         return array('page'=>$page,'goods_count'=>$goods_count,'data'=>$data);
         
    }
    //有效店铺
    function _filterStore(){
        $str="`sdb_b2c_goods`.store_id not in(select store_id from sdb_business_storemanger";
        $str.=" where approved <>'1' or status<>'1' or (last_time is not null and last_time <=".mktime(0, 0, 0, date("m")  , date("d"), date("Y"))."))";
        return $str;
    }
    
    protected function get_page(){
        $page=empty($this->params['nPage'])?1:intval($this->params['nPage']);
        $pageLimit=empty($this->params['pagelimit'])?5:intval($this->params['pagelimit']);
        return array(
            'pagelimit'=>($pageLimit > 0) ? intval($pageLimit) : 5,
            'page'=>($page > 1) ? intval($page) : 1
            );
    }
    protected function get_order(){
        $aOrderBy= $this->objGoods->orderBy();//
        $orderBy=empty($this->params['orderby'])?1:intval($this->params['orderby']);
        
        if(!isset($aOrderBy[$orderBy])){
            $this->send(false,null,'不存在该排序');
        }else{
            $orderby = $aOrderBy[$orderBy]['sql'];
        }
        return $orderby;
    }
    protected function get_filter(){
        if($this->params['cat_id']){
            $virCatObj = &$this->app->model('category');
            $vcatid = $this->params['cat_id'];
            /**  **/
            if(!cachemgr::get('cellphone_goods_virtual_cat_'.intval($vcatid), $vcat)){
                cachemgr::co_start();
                $vcat = $virCatObj->getList('cat_id,cat_path,cat_id,filter,cat_name',array('cat_id'=>intval($vcatid)));
                cachemgr::set('cellphone_goods_virtual_cat_'.intval($vcatid), $vcat, cachemgr::co_end());
            }
            $vcat = current( $vcat );
            $vcatFilters = $virCatObj->_mkFilter($vcat['filter']);
            
            $filter = $virCatObj->getFilter($vcatFilters);
            
            $oSearch = $this->b2c->model('search');
            $param=$oSearch->encode($filter);
            $filter=$oSearch->decode($param);
            foreach($filter as $key=>$v){
                if($v[0]==='_ANY_'||empty($v[0])){
                    unset($filter[$key]);
                }
            }
            if($filter['price']){
               if($filter['price'][0]==0 && $filter['price'][1]==''){
                   unset($filter['price']);
               }
            }
        }
        
        $_key=$this->get_key();
        if(empty($_key)){
            unset($filter['name']);
        }else{
            $filter['name'][0]=$_key;
        }
        $filter['goods_type'] = 'normal';
        $filter['marketable'] = 'true';
        if(!empty($this->params['store_id'])){
            if(is_array($this->params['store_id'])){
                $filter['store_id']=array_values($this->params['store_id']);
            }else{
                $filter['store_id']=explode(',',$this->params['store_id']);
            }
        }
        return $filter;
    }
    protected function get_key(){
        $key='';
        if(!empty($this->params['key'])){
            $key=htmlspecialchars($this->params['key']);
        }
        return $key;
    }
    protected function _check($params){
        if(empty($params['cat_id'])&& !isset($params['key'])){
            $this->send(false,null,'分类ID和关键字不能同时为空');
        }
    }
}