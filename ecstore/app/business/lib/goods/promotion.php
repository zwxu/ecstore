<?php

class business_goods_promotion
{
    function __construct(&$app){
       $this->app=$app;
       $this->mdl_promotion=$this->app->model('goods_promotion_price');
    }
    function gen_site_url($goods_id,$promotion_id=0,$p_type='normal'){
        if($p_type=='normal'){
            return app::get('site')->router()->gen_url(array('app' => 'b2c','ctl' => 'site_product', 'act'=>'index','arg0'=>$goods_id));
        }
        foreach(kernel::servicelist('gallery_list.goods_promotion') as $object) {
            if( method_exists($object,'get_type')){
                $type_name = $object->get_type();
            }else{
                $type_name = array_pop(explode('_',get_class($object)));
            }
            if($type_name==$p_type){
                if(method_exists($object,'gen_url')){
                    return $object->gen_url($goods_id,$promotion_id);
                }
            }
        }
        return '';
    }
    function get_icon($p_id='',$p_type='normal'){
        if($p_type=='normal'){
            return '';
        }
        foreach(kernel::servicelist('gallery_list.goods_promotion') as $object) {
            if( method_exists($object,'get_type') ){
                $type_name = $object->get_type();
            }else{
                $type_name = array_pop(explode('_',get_class($object)));
            }
            if($type_name==$p_type){
                if(method_exists($object,'get_icon')){
                    return $object->get_icon($p_id);
                }
            }
        }
        return '';
    }
    function deletePrice($p_type='normal',$p_id=0,$goods_id=0){
        if($goods_id!==0){
            return $this->mdl_promotion->deleteByGID($goods_id);
        }
        if($p_id!==0){            
            return $this->mdl_promotion->deleteByPID($p_id,$p_type);
        }
        return $this->mdl_promotion->deleteByPType($p_type);
    }
    function addPrice($data=array()){
        if(empty($data)){
           return false;
        }
        if(empty($data['goods_id'])||empty($data['ref_id'])||
           empty($data['p_price'])||empty($data['p_name'])||
           empty($data['p_type'])){
           return false;
        }
        $p_types=array();
        foreach(kernel::servicelist('gallery_list.goods_promotion') as $object) {
            if( method_exists($object,'get_type') ){
                $type_name = $object->get_type();
            }else{
                $type_name = array_pop(explode('_',get_class($object)));
            }
            $p_types[]=$type_name;
        }
        if(empty($p_types)||!in_array($data['p_type'],$p_types)){
            return false;
        }
        if(empty($data['from_time'])){
           $data['from_time']=time();
        }        
        if(empty($data['to_time'])){
           $data['to_time']=time()+10*365*24*60*60;
        }
        return $this->mdl_promotion->save($data);
    }
}