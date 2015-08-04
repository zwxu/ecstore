<?php 
class package_delete_check
{
    function __construct( &$app ) {
        $this->app = $app;
    }
    
    /**
     * 
     * @params $gid 商品id
     * @params $pid 货品id
     * @return bool
     **/
    public function is_delete( $gid,$pid=null ) {
        $filter = array();
        $o = $this->app->model('attendactivity');
        
        $arr = $o->db->select("select id from sdb_package_attendactivity where gid like '%,{$gid},%'");
        
        if( !$arr || !is_array($arr) ) return true;
        $this->error_msg = '该商品在捆绑商品中存在！无法删除！';
        return false;
    }
}