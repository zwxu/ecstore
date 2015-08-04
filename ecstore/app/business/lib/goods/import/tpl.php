<?php

class business_goods_import_tpl
{
    function __construct(&$app){
       $this->app=$app;
    }
    public function get_list($post,$obj,$page=1){    
        $list_listnum =5; 
        $filter=$this->get_filter($post,$obj);
        $mdl_tpl=$this->app->model('goods_import_tpl');
        $count=$mdl_tpl->count($filter);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $mdl_tpl->getList('*',$filter,$start,$list_listnum);
        $params['page'] = $maxPage;
        return $params;
    }
    public function get_filter($post,$obj){
        $filter=array();
        $filter['store_id']=$post['store_id'];
        if($post['tpl_id']){
           $filter['tpl_id']=$post['tpl_id'];
        }
        if($post['cat_id']){
           $filter['cat_id']=$post['cat_id'];
        }
        $filter['disabled']='false';
        return $filter;
    }
}