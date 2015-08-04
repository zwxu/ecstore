<?php



class groupbuy_goods_promotion
{
    function get_type(){
       return 'group';
    }
    function gen_url($goods_id,$promotion_id){
       $args=array($goods_id,null,null,$promotion_id);
      return app::get('site')->router()->gen_url( array('app'=>'groupbuy','ctl'=>'site_product','act'=>'index','args'=>$args) );
    }
    function get_icon($p_type){
        return '';
    }
}
