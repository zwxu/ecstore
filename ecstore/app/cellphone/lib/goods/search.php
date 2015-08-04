<?php
  
class cellphone_goods_search extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->b2c=app::get('b2c');
        $this->objGoods = $this->b2c->model('goods');
        $this->productCat = $this->b2c->model('goods_cat');
    }
    public function get(){
        $params = $this->params;        
        //echo '<pre>';
        //print_r($params);
        $this->_check($this->params);
        $data=$this->search();
        
    }
    protected function get_search_type(){
        return 'g';
    }
    private function search(){
        $filter=$this->get_filter($params);
        $st=$this->get_search_type();//empty($this->params['st'])?'goods':($this->params['st']=='goods'?'g':'s');
        $scount=0;
        foreach($filter as $key=>$v){
            if(substr($key,0,2)==='p_'){
               $scount++;
            }
        }
        $sType=isset($this->params['sType'])?$this->params['sType']:'goods';
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
        $shop_count=0;
        $count=0;
        $res = false;   //
        if(is_object($searchApp)){
            $queryRes = $searchApp->phone_query($sfilter);
            if($queryRes){
                $res = $searchApp->commit();
                if(!empty($res['result'])){
                    $shop_count=count($res['all_store']);
                    $count = $res['total'];
                    $tmp_filter['str_where'] = $this->objGoods->_filter(array('goods_id'=>$res['result']));
                    $search =$this->objGoods->getList_1($this->getGoodsCol(),$tmp_filter);
                    $tmp_search_data=array();
                    foreach($search AS $tmp_data){
                       $tmp_search_data[$tmp_data['goods_id']] = $tmp_data;
                    }
                    foreach($res['result'] AS $v){
                       $search_data[] = $tmp_search_data[$v];
                    }
                    unset($tmp_search_data);
                    
                    unset($search);
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
            $temp=0;
            $ttemp_product=$this->objGoods->getSearchGoods('`sdb_b2c_goods`.store_id,`sdb_b2c_goods`.goods_id',$tmp_filter,0,-1,$orderby,$arr_cat_id,$temp);
            //$ttemp_store_array=utils::array_change_key($ttemp_product,'store_id', 1);
            $shop_count=count(array_unique(array_map('current',$ttemp_product)));
            $search_data =$this->objGoods->getSearchGoods($this->getGoodsCol(),$tmp_filter,$pager['pagelimit']*($pager['page']-1),$pager['pagelimit'],$orderby,$arr_cat_id,$temp);
            $count=$temp;
        }
        foreach($search_data as $key=>&$product){
            if($product['udfimg']=='true'){
                $product['image']=base_storager::image_path($product['thumbnail_pic'],'s');
            }else{
                $product['image']=base_storager::image_path($product['image_default_id'],'s');
            }
            //$product['image']='<img src="'.$product['image'].'">';
            unset($product['udfimg']);
            unset($product['image_default_id']);
            unset($product['thumbnail_pic']);
            unset($product['dorder']);
            unset($product['orderWeight']);
            
            $product['act_type'] = $product['act_type']=='package'?'normal':$product['act_type'];
        }
        $result=$this->get_response_result($count,$pager,$search_data,$shop_count);
        
        $this->send(true, $result, app::get('b2c')->_('success'));
        exit;
    }
    function get_response_result($count,$qpage,$data,$shop_count){
    
         $page['limit']=$qpage['pagelimit'];
         $page['tPage']=ceil($count/intval($qpage['pagelimit']));
         $page['cPage']=$qpage['page'];
         $page['count']=$count;
         return array('page'=>$page,'store_count'=>$shop_count,'data'=>$data);
         //return array('page'=>$page,'data'=>$data);
         
    }
    //有效店铺
    function _filterStore(){
        $str="`sdb_b2c_goods`.store_id not in(select store_id from sdb_business_storemanger";
        $str.=" where approved <>'1' or status<>'1' or (last_time is not null and last_time <=".mktime(0, 0, 0, date("m")  , date("d"), date("Y"))."))";
        return $str;
    }
    protected function getGoodsCol(){
        $col=array();
        $col[]='sdb_b2c_goods.goods_id';
        //$col[]='sdb_b2c_goods.bn';
        $col[]='sdb_b2c_goods.name';
        //$col[]='sdb_b2c_goods.price';
        $col[]='IFNULL(pp.p_price,`sdb_b2c_goods`.price) as price';
        //$col[]='sdb_b2c_goods.type_id';
        //$col[]='sdb_b2c_goods.cat_id';
        //$col[]='sdb_b2c_goods.brand_id';
        //$col[]='sdb_b2c_goods.marketable';
        //$col[]='sdb_b2c_goods.store';
        //$col[]='sdb_b2c_goods.notify_num';
        //$col[]='sdb_b2c_goods.uptime';
        //$col[]='sdb_b2c_goods.downtime';
        //$col[]='sdb_b2c_goods.last_modify';
        //$col[]='sdb_b2c_goods.p_order';
        //$col[]='sdb_b2c_goods.d_order';
        //$col[]='sdb_b2c_goods.score';
        //$col[]='sdb_b2c_goods.cost';
        $col[]='sdb_b2c_goods.mktprice';
        //$col[]='sdb_b2c_goods.weight';
        //$col[]='sdb_b2c_goods.unit';
        //$col[]='sdb_b2c_goods.brief';
        //$col[]='sdb_b2c_goods.goods_type';
        $col[]='sdb_b2c_goods.image_default_id';
        $col[]='sdb_b2c_goods.udfimg';
        $col[]='sdb_b2c_goods.thumbnail_pic';
        //$col[]='sdb_b2c_goods.small_pic';
        //$col[]='sdb_b2c_goods.big_pic';
        //$col[]='sdb_b2c_goods.store_place';
        //$col[]='sdb_b2c_goods.min_buy';
        /*$col[]='sdb_b2c_goods.package_scale';
        $col[]='sdb_b2c_goods.package_unit';
        $col[]='sdb_b2c_goods.package_use';
        $col[]='sdb_b2c_goods.score_setting';
        $col[]='sdb_b2c_goods.nostore_sell';*/
        //$col[]='sdb_b2c_goods.goods_setting';
        //$col[]='sdb_b2c_goods.spec_desc';
        //$col[]='sdb_b2c_goods.params';
        //$col[]='sdb_b2c_goods.disabled';
        //$col[]='sdb_b2c_goods.rank_count';
        //$col[]='sdb_b2c_goods.comments_count';
        //$col[]='sdb_b2c_goods.view_w_count';
        //$col[]='sdb_b2c_goods.view_count';
        //$col[]='sdb_b2c_goods.count_stat';
        //$col[]='sdb_b2c_goods.buy_count';
        //$col[]='sdb_b2c_goods.buy_w_count';
        /*$col[]='sdb_b2c_goods.p_1';
        $col[]='sdb_b2c_goods.p_2';
        $col[]='sdb_b2c_goods.p_3';
        $col[]='sdb_b2c_goods.p_4';
        $col[]='sdb_b2c_goods.p_5';
        $col[]='sdb_b2c_goods.p_6';
        $col[]='sdb_b2c_goods.p_7';
        $col[]='sdb_b2c_goods.p_8';
        $col[]='sdb_b2c_goods.p_9';
        $col[]='sdb_b2c_goods.p_10';
        $col[]='sdb_b2c_goods.p_11';
        $col[]='sdb_b2c_goods.p_12';
        $col[]='sdb_b2c_goods.p_13';
        $col[]='sdb_b2c_goods.p_14';
        $col[]='sdb_b2c_goods.p_15';
        $col[]='sdb_b2c_goods.p_16';
        $col[]='sdb_b2c_goods.p_17';
        $col[]='sdb_b2c_goods.p_18';
        $col[]='sdb_b2c_goods.p_19';
        $col[]='sdb_b2c_goods.p_20';
        $col[]='sdb_b2c_goods.p_21';
        $col[]='sdb_b2c_goods.p_22';
        $col[]='sdb_b2c_goods.p_23';
        $col[]='sdb_b2c_goods.p_24';
        $col[]='sdb_b2c_goods.p_25';
        $col[]='sdb_b2c_goods.p_26';
        $col[]='sdb_b2c_goods.p_27';
        $col[]='sdb_b2c_goods.p_28';
        $col[]='sdb_b2c_goods.p_29';
        $col[]='sdb_b2c_goods.p_30';
        $col[]='sdb_b2c_goods.p_31';
        $col[]='sdb_b2c_goods.p_32';
        $col[]='sdb_b2c_goods.p_33';
        $col[]='sdb_b2c_goods.p_34';
        $col[]='sdb_b2c_goods.p_35';
        $col[]='sdb_b2c_goods.p_36';
        $col[]='sdb_b2c_goods.p_37';
        $col[]='sdb_b2c_goods.p_38';
        $col[]='sdb_b2c_goods.p_39';
        $col[]='sdb_b2c_goods.p_40';
        $col[]='sdb_b2c_goods.p_41';
        $col[]='sdb_b2c_goods.p_42';
        $col[]='sdb_b2c_goods.p_43';
        $col[]='sdb_b2c_goods.p_44';
        $col[]='sdb_b2c_goods.p_45';
        $col[]='sdb_b2c_goods.p_46';
        $col[]='sdb_b2c_goods.p_47';
        $col[]='sdb_b2c_goods.p_48';`sdb_b2c_goods`
        $col[]='sdb_b2c_goods.p_49';
        $col[]='sdb_b2c_goods.p_50';*/
        $col[]='sdb_b2c_goods.store_id';
        //$col[]='sdb_b2c_goods.goods_state';
        $col[]='sdb_b2c_goods.buy_m_count';
        //$col[]='sdb_b2c_goods.view_m_count';
        //$col[]='sdb_b2c_goods.fav_count';
        $col[]='sdb_b2c_goods.freight_bear';
        /*$col[]='sdb_b2c_goods.marketable_allow';
        $col[]='sdb_b2c_goods.marketable_content';
        $col[]='sdb_b2c_goods.avg_point';*/
        $col[]='sdb_b2c_goods.act_type';
        $col[]='s.area';
        $col[]='IFNULL(pp.ref_id,0) as promotion_id';
        return implode(',',$col);
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
        $sql='';
        if(!isset($aOrderBy[$orderBy])){
            $this->send(false,null,'不存在该排序');
        }else{
            $sql = $aOrderBy[$orderBy]['sql'];
        }return $sql;
        if($sql){
            $aTemp = explode(',',$sql);
            foreach($aTemp as $key => &$value){
                $value = 'sdb_b2c_goods.'.$value;
            }
            //$sql = implode(',',$aTemp);
        }
        return $sql;
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