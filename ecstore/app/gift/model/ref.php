<?php

 
    
    
    
class gift_mdl_ref extends dbeav_model {
    
    public function __construct( &$app ) {
        $this->app = $app;
        parent::__construct( $app );
        parent::delete( array('product_id'=>0) ); //临时处理  莫名出现空数据 未查到原因
    }


    public function get_list_finder($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        if( $filter['bn'] ) {
            $arr = $this->app->model('goods')->getList( 'goods_id',array('bn'=>$filter['bn']) );
            reset( $arr );
            $arr = current( $arr );
            if( $arr['goods_id'] ) {
                unset( $filter['bn'] );
                $filter['goods_id'] = $arr['goods_id'];
                $tmp = $this->getList( '*', $filter, 0, -1, $orderType);
            }
        } else {
            
            $tmp = $this->getList( '*', $filter, 0, -1, $orderType);
            
        }
       
        
        //判断商品库是否存在  删除商品时会判断赠品是否存在 
        $arr = array();
        $o = $this->app->model('goods');
        foreach( (array)$tmp as $_key => $_val ) {
            if( $o->getList( 'goods_id',array('goods_id'=>$_val['goods_id']) ) ){
                if( isset($arr[$_val['goods_id']]) ){
                    $_val['hasspec'] =  'true';
                } 
                $arr[$_val['goods_id']] = $_val;
            }
        }
        
        return array_slice( $arr,$offset,$limit);
    }
    
    /*
     * dump save delete 函数重写 用于desktop删除操作
     */
    public function dump($filter,$field = '*',$subSdf = null){
        if( !is_array($filter) )
            $filter = array('product_id'=>$filter);
        $arr = $this->getList( 'goods_id', $filter );
        reset( $arr );
        $arr = current( $arr );
        $return = array();
        $goods_id = $arr['goods_id'];
        if( $goods_id ) {
            $arr = $this->app->model('goods')->dump( array('goods_id'=>$goods_id) );
            $return['type'] = $arr['goods_type'];
            if( $arr['goods_type']=='gift' ) {
                $return['info'] = $arr;
            } else {
                foreach( (array)$arr['product'] as $row ) {
                    if( isset($row['gift']) && is_array($row['gift']) ) {
                        $tmp = $row['gift'];
                        $tmp['bn'] = $row['bn'];
                        $return['info'][] = $tmp;
                    }
                }
            }
        }
        
        return $return;
    }
	function checkProductBn($bn, $gid=0){
        if(empty($bn)){
            return false;
        }
        if($gid){
            $sql = 'SELECT count(*) AS num FROM sdb_b2c_products WHERE bn = \''.$bn.'\' AND goods_id != '.$gid;
            $Gsql = 'SELECT count(*) AS num FROM sdb_b2c_goods WHERE bn = \''.$bn.'\' AND goods_id != '.$gid;
        }else{
            $sql = 'SELECT count(*) AS num FROM sdb_b2c_products WHERE bn = \''.$bn.'\'';
            $Gsql = 'SELECT count(*) AS num FROM sdb_b2c_goods WHERE bn = \''.$bn.'\'';
        }
        $aTmp = $this->db->select($sql);
        $GaTmp = $this->db->select($Gsql);
        return $aTmp[0]['num']+$GaTmp[0]['num'];
    }
    function pre_restore(&$data,$restore_type='add'){
    	//print_r($data);exit();
    	if($data['type']=='gift'){
	        if( $restore_type == 'add' ){
	            if( $this->checkProductBn( $data['info']['bn']) ){
	                $data['info']['bn'] = '';
	            }
	            foreach( $data['info']['product'] as $k => $p ){
	                if( $this->checkProductBn( $p['bn'] ) ){
	                    $data['info']['product'][$k]['bn'] = '';
	                }
	            }
	
	        }
	        if( $restore_type == 'none' ){
	            if( $this->checkProductBn( $data['info']['bn'] ) ){
	                return false;
	            }
	            foreach( $data['info']['product'] as $k => $p ){
	                if( $this->checkProductBn( $p['bn'] ) ){
	                    return false;
	                }
	            }
	        }
	        $data['goods_id'] = $data['info']['goods_id'];
	        $data['product_id'] = (int)key($data['info']['product']);
    	}
    	else
    	{
    		foreach( (array)$data['info'] as $key => $row ) {
		        if( !$this->checkProductBn( $row['bn']) ){
		           return false;
		        }
    		}
	        $data['product_id'] = (int) $data['info'][0]['product_id'];
    	}
        $data['need_delete'] = true;
        return true;
    }

    public function save(&$data,$mustUpdate = null){
        if( !isset($data['type']) ) return false;
        if( $data['type']=='gift' ) {
            return $this->app->model('goods')->save($data['info']);
        } else {
            foreach( (array)$data['info'] as $row ) {
                $row['member_lv_ids'] = implode(',', (array)$row['member_lv_ids']);
                $flag = $this->save2save( $row );
                if( !$flag ) break;
            }
        }
        return $flag;
    }
    public function delete($filter,$subSdf = 'delete'){
        $arr_ref = $this->getList( 'goods_id',$filter );
        
        //验证是否可以删除
        $obj_check_order = kernel::single('b2c_order_checkorder');
        
        foreach( (array)$arr_ref as $row ) {
            $goods_id = $row['goods_id'];
            if( $goods_id ){
                $arr = $this->app->model('goods')->getList( 'goods_type', array('goods_id'=>$goods_id) );
                if( $arr && is_array($arr) ) { 
                    reset( $arr );
                    $arr = current( $arr );
                    
                    
                    if( $arr['goods_type']!='gift' ) { //非赠品 删除本身数据
                        $flag = $this->delete2delete( array('goods_id'=>$goods_id) );
                    } else {
                        $flag = $this->app->model('goods')->delete( array('goods_id'=>$goods_id) );
                    }
                } else { //数据异常 当商品表中无数据时
                    $flag = $this->delete2delete( array('goods_id'=>$goods_id) );
                }
                    if( !$flag ) break;
            }
        }
        return $flag;
    }
    
    
    function pre_recycle($rows){
        //验证是否可以删除
        $obj_check_order = kernel::single('b2c_order_checkorder');
        
        foreach( (array)$rows as $row ) {
            $goods_id = $row['goods_id'];
            if( $goods_id ){
                //是否可以删除
                if(!$obj_check_order->check_order_product(array('goods_id'=>$goods_id,'product_id'=>''),$msg,array('gift'))){
                    $this->recycle_msg = '该赠品有订单未处理！删除失败！';
                    return false;
                }
                
            }
        }
        return true;
    }
    
    
    
    
    
    
    public function dump2dump($filter,$field = '*',$subSdf = null){
        return parent::dump( $filter,$field,$subSdf );
    }
    
    public function delete2delete($filter,$subSdf = 'delete'){
        return parent::delete( $filter,$subSdf );
    }
    
    public function save2save(&$data,$mustUpdate = null){
        return parent::save( $data,$mustUpdate );
    }
    
    public function count_finder($filter=null){
        $row = $this->db->select('SELECT count( DISTINCT goods_id) as _count FROM '.$this->table_name(1).' WHERE '.$this->_filter($filter));
        return intval($row[0]['_count']);
    }
    
    
    public function modifier_cat_id( $cols ) {
        if( !$cols ) return '-';
        
        $arr = $this->app->model('cat')->dump( $cols );
        return $arr['cat_name'];
    }
    
    
    
    
    
    public function modifier_bn( $cols ) {
        if( !$cols ) return '-';
        $arr = $this->getList( 'goods_id',array('bn'=>$cols) );
        if( $arr ) {
            reset( $arr );
            $arr = current( $arr );
            $arr = $this->app->model('goods')->getList( 'bn',array('goods_id'=>$arr['goods_id']) );
            reset( $arr );
            $arr = current( $arr );
            return $arr['bn'];
        }
        return '-';
    }
    

}