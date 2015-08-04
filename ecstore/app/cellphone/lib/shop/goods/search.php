<?php
  
class cellphone_shop_goods_search extends cellphone_goods_search
{
    public function __construct($app){
        parent::__construct($app);
    }
    protected function get_filter(){
        
        
        $_key=$this->get_key();
        if(empty($_key)){
            
        }else{
            $filter['name'][0]=$_key;
        }
        $filter['goods_type'] = 'normal';
        $filter['marketable'] = 'true';
        if(!empty($this->params['shop_id'])){
            if(is_array($this->params['shop_id'])){
                $filter['store_id']=array_values($this->params['shop_id']);
            }else{
                $filter['store_id']=explode(',',$this->params['shop_id']);
            }
        }
        return $filter;
    }
    function get_response_result($count,$qpage,$data,$goods_count){
         $page['limit']=$qpage['pagelimit'];
         $page['tPage']=ceil($count/intval($qpage['pagelimit']));
         $page['cPage']=$qpage['page'];
         $page['count']=$count;
         foreach($data as $key=>&$goods){
         
            unset($goods['store_id']);
            unset($goods['store_name']);
            unset($goods['area']);
         }
         return array('page'=>$page,'data'=>$data);
         
    }
    protected function _check($params){
        if(!isset($params['shop_id'])){
            $this->send(false,null,'店铺ID不能为空');
        }
    }
}