<?php 

class timedbuy_cart_object_goods
{
    function __construct(&$app) {
        $this->app = &$app;
        $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        $this->member_ident = kernel::single("base_session")->sess_id();
    }

    function check($gid,$pid,$quantity=0,&$msg){

        if( !$gid ) {
            $msg = '商品ID丢失！';
            return false;
        }
        $arr = kernel::single('timedbuy_cart_process_goods')->checkgoods($gid);
        if(!$arr){
            return true;
        }
        
        if( !$this->arr_member_info || !$this->arr_member_info['member_id'] ) {
            $msg = '只限会员抢购！！！';
            #return false;
            $jump_to_url = app::get('site')->router()->gen_url( array('app'=>'b2c','ctl'=>'site_passport','act'=>'login','full'=>'true') );
            if($_POST['mini_cart']){
                echo json_encode( array('url'=>$jump_to_url) );exit;
            } else {
				return true;
                //echo json_encode( array('status'=>'nologin','error'=>'请先登录') );exit;
            }
        }
        $filter = array('member_id'=>$this->arr_member_info['member_id'],'member_ident'=>$this->member_ident);
        $arr_cart_objects = app::get('b2c')->model('cart_objects')->getList( '*',$filter );
        foreach( (array)$arr_cart_objects as $cart_objects ) {
            if($cart_objects['params']['goods_id']==$gid && $cart_objects['params']['product_id']!=$pid ) {
                $quantity += $cart_objects['quantity'];
            } 
        }
        
        $memberbuy = $this->app->model('memberbuy');
        $buys = $memberbuy->getList('*',array('member_id'=>$this->arr_member_info['member_id'],'gid'=>$gid,'aid'=>$arr['aid'],'disable'=>'false'));
        $num=0;
        foreach($buys as $key=>$value){
            $num = $num + $value['nums'];
        }

        if( $arr['presonlimit'] && $arr['presonlimit']<$num+$quantity ) {
            $msg = '累计购买数量超出每人限购数量！' ;
            return false;
        }
        if( $arr['nums'] && $arr['remainnums']<$quantity ) {
            $msg = '已超出限购库存！';
            return false;
        }
        return true;

    }
}