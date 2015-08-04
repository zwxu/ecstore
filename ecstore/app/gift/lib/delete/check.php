<?php 

class gift_delete_check
{
    
    
    function __construct( &$app ) {
        $this->app = $app;
        $this->o_goods_ref = $this->app->model('ref');
    }
    
    
    /**
     * 检查指定商品是否能被删除
     *
     * @params $gid 商品id
     * @params $pid 货品id
     * @return bool
     **/
    public function is_delete( $gid,$pid=null ) {
        $filter = array();
        
        if( $pid )
            $filter['product_id'] = $pid;
        
        if( $gid ) 
            $filter['goods_id'] = $gid;
        
        $count = $this->o_goods_ref->count( $filter );
        if( $count ) {
            $this->error_msg = '该商品在赠品中存在！无法删除！';
            return false;
        }
        return true;
    }
}
