<?php
  
class cellphone_goods_virtualcat extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }
    public function get(){
        $params = $this->params;
        $parent_id=0;
        if(isset($params['id'])){
            $parent_id=intval($params['id']);
        }
        if(empty($parent_id)){
           $this->send(false, null, app::get('b2c')->_('无效ID'));
           exit;
        }
        $mdl_category=$this->app->model('category');
        $filter=" and cat_id='".$parent_id."'";
        $sql="select cat_id,cat_name,parent_id,image,customized from sdb_cellphone_category where    disabled='false' ".$filter." ORDER BY p_order,cat_id";
        $cat_list=$mdl_category->db->select($sql);
        if(empty($cat_list)){
            $this->send(false, null, app::get('b2c')->_('无效ID'));
        }else{
            $this->send(true, $this->format_cat($cat_list[0]), app::get('b2c')->_('success'));
        }
    }
    public function child(){
        $params = $this->params;
        $parent_id=0;
        if(isset($params['pid'])){
            $parent_id=intval($params['pid']);
        }
        $cat_list=array();
        $is_cached=cachemgr::get('cellphone_goodscat_child_'.$parent_id, $cat_list);
        if(!$is_cached){
            $mdl_category=$this->app->model('category');
            $filter=" and parent_id='".$parent_id."'";
            $sql="select cat_id,cat_name,parent_id,image,customized from sdb_cellphone_category where    disabled='false' ".$filter." ORDER BY p_order,cat_id";
            $cat_list=$mdl_category->db->select($sql);
            if(empty($cat_list)){
                $this->send(false, null, app::get('b2c')->_('无效ID'));
            }
            foreach($cat_list as $key=>$cat){
                $cat_list[$key]=$this->format_cat($cat_list[$key]);
            }
            cachemgr::co_start();
            cachemgr::set('cellphone_goodscat_child_'.$parent_id, $cat_list, cachemgr::co_end());
        }
        $this->send(true,$cat_list , app::get('b2c')->_('success'));
    }
    public function tree(){
        $params = $this->params;
        $parent_id=0;
        $cache_key='';
        if(isset($params['pid'])){
            $parent_id=intval($params['pid']);
        }
        if(isset($params['cache_key'])){
            $cache_key=$params['cache_key'];
        }
        $this->get_tree_cat($parent_id,$cache_key);
    }
    private function get_tree_cat($parent_id,$cache_key=''){
        $result=array();
        $is_cached=cachemgr::get('cellphone_goodscat_tree_'.$parent_id, $result);
        if(!$is_cached || empty($cache_key)){
            $result=$this->get_cache_result($parent_id);
        }else{
            if($result['cache_key']!=$cache_key){
                $result=$this->get_cache_result($parent_id);
            }
        }
        if($result['cache_key']==$cache_key){
            unset($result['data']);
            $result['data']=array();
        }
        
        $this->send(true, $result, app::get('b2c')->_('success'));
    }
    private function get_cache_result($parent_id){
        $cat_list=$this->get_cat_list($parent_id);
        
        if(empty($cat_list)){
           $this->send(false, null, app::get('b2c')->_('无效ID'));
           exit;
        }
        $result['cache_key']=md5(serialize($cat_list));
        $result['data']=$cat_list;
        cachemgr::co_start();
        //$co_end=cachemgr::co_end();
        //$co_end['expires']=time()+300;
        cachemgr::set('cellphone_goodscat_tree_'.$parent_id, $result, cachemgr::co_end());
        return $result;
    }
    private function get_cat_list($parent_id){
        $mdl_category=$this->app->model('category');
        //[]="  ";
        $filter='';
        if($parent_id!==0){
            $filter.="cat_path like '%,".$parent_id.",%'";    
        }
        if(!empty($filter)){
            $filter=' and ('.$filter.' or cat_id ='.$parent_id.')';
        }
        $sql="select cat_id,cat_name,parent_id,image,customized from sdb_cellphone_category where    disabled='false' ".$filter." ORDER BY p_order,cat_id";
        $cat_list=$mdl_category->db->select($sql);
        if(empty($cat_list)){
            return array();
        }
        $cat_list_p=utils::array_change_key($cat_list,'parent_id',true);
        $cat_list=$this->get_sub_cat($cat_list_p,intval($parent_id));
        return $cat_list;
    }
    private function get_sub_cat($cat_p,$p_id){
        $sub_cat_child=$cat_p[$p_id];
        $result=array();
        foreach((array)$sub_cat_child as $cats){
            $cats=$this->format_cat($cats);
            $child=$this->get_sub_cat($cat_p,intval($cats['cat_id']));
            
            $cats['child']=empty($child)?array():$child;
            $result[]=$cats;
        }
        return $result;
    }
    private function format_cat($cat){
        if($cat['image']){
            $cat['image']=$this->get_img_url($cat['image'],'cs');
        }      
        if($cat['customized']){        
            $cat['customized']=unserialize($cat['customized']);
        }else{
            $cat['customized']=array();
        }
        return $cat;
    }
}