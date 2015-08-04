<?php

 
class b2c_site_goods_detail_blocks{
    function get_blocks($blocks=array()){
        return;//todo 商品block 可以被注册
        $funcs = $system->getFuncList('goods_detail_blocks');
        foreach($funcs as $func){
            $func_list[$func['call']] = $func;            
        }
        $_pre = 'goods_detail_block_';
        if($blocks){
            foreach($blocks as $k=>$v){
                $func_name = $_pre.$k;
                $return[$k] = $system->execFunc($func_list[$func_name],$v);
            }
        }
        return $return;
    }
    
    function get_block_adjunct($params){
        return 'haha I am adjunct!';
    }
    
    function get_block_promotion($params){
        return 'haha I am promotion!';
    }
}
