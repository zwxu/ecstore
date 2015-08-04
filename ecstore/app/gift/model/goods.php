<?php

 
    
    
    
class gift_mdl_goods extends b2c_mdl_goods {
    private $use_filter_default = true;
    
    var $has_tag = true;
    var $defaultOrder = array('p_order',' DESC',',goods_id',' DESC');
    var $has_many = array(
        'product' => 'products:contrast',
        'images' => 'image_attach@image:contrast:goods_id^target_id',
        //'product' => 'products:contrast',
    );
    var $has_one = array(
        #'member_ref' => 'member_ref@gift:replace:goods_id^goods_id'
    );
    var $subSdf = array(
            'default' => array(
        
                'product'=>array(
                    '*',array(
                        'price/member_lv_price'=>array('*')
                    )
                ),
        
                ':cat@gift'=>array(
                    '*'
                ),
                'images'=>array(
                    '*',array(
                        ':image'=>array('*')
                    )
                ),
               # 'member_ref'=>array(
               #     '*',
               # ),
            ),
            'delete' => array(
                
                'product'=>array(
                    '*',array(
                        'price/member_lv_price'=>array('*')
                    )
                ),
                
                'images'=>array(
                    '*'
                ),
                #'member_ref'=>array(
                #    '*'
                #),
            )
        );
    
    
    var $filter_default = array('goods_type'=>'gift');
    #var $filter_default = array();
    

    public function dump($id, $col='*',$subSdf='default') {
        $filter = array(
                'goods_id'   => $id,
            );
        $filter = array_merge($filter, $this->filter_default);
        $filter['goods_type'] = array_merge( (array)$filter['goods_type'], array( 'normal') );
        #print_r($filter);exit;
        $arr_gift_info = parent::dump($filter, $col, $subSdf);
        #exit;
        if( isset( $arr_gift_info['product'] ) && is_array( $arr_gift_info['product'] ) ) {
            $o = app::get('gift')->model('ref');
            foreach( $arr_gift_info['product'] as &$row ) {
                $gift = $o->dump2dump( $row['product_id'] );
                if( $gift ) {
                    $gift['member_lv_ids'] = $gift['member_lv_ids'] ? explode(',', $gift['member_lv_ids']) : '';
                    $row['gift'] = $gift;
                    $tmp = $gift;
                }
            }
            
            $arr_gift_info['gift'] = $tmp;
        }
        
        return $arr_gift_info;
    }
    
    public function dump_b2c ( $filter, $col='*' ) {
        $goods_type = array('normal','gift');
        $filter['goods_type'] = $goods_type;
        $arr = parent::getList( $col,$filter );
        $arr = $arr[0];
        if( empty( $arr ) ) return false;
        $o = $this->app->model('products');
        $arr['products'] = $o->getList( '*',array('goods_id'=>$arr['goods_id'],'goods_type'=>$goods_type) );
        return $arr;
    }
    
    

    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null) {
        
        is_array($filter) or $filter = array();
        if( $this->get_filter_default() && $filter['goods_type'] != 'normal' )
            $filter = array_merge($filter, $this->filter_default);
        
        
        if( empty($filter['goods_id']) ) {
            //修改查询bn号是通过b2c_goods表里面的商品bn
            $sql = "select goods_id from sdb_b2c_goods where bn='{$filter['bn']}'";
            $arr = $this->db->select($sql);
            //end
            //$o = app::get('gift')->model('ref');
            //$arr = $o->getList( 'goods_id' , $filter);
            $filter['goods_id'] = array_unique( array_merge( (array)$filter['goods_id'], array_map( 'current',(array)$arr ) ) );
               
            if( empty($filter['goods_id']) ) return false;
        }
        $filter['goods_type'] = array_unique( array_merge( (array)$filter['goods_type'], array( 'normal' ) ) );
        return parent::getList($cols, $filter, $offset, $limit, $orderType);
    }
    
    public function getList_1($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null) {
        return parent::getList($cols, $filter, $offset, $limit, $orderType);
    }
    
    
    public function unuse_filter_default( $flag=false ) {
        $this->use_filter_default = $flag;
        return $this->use_filter_default;
    }
    
    private function get_filter_default() {
        return $this->use_filter_default;
    }
    
    public function count($filter = array()) {
        is_array($filter) or $filter = array();
        $filter = array_merge($filter, $this->filter_default);
        return parent::count($filter);
    }
    
    function modifier_cat_id($cols){
        if( !$cols )
            return '-';
        else{
            $a = app::get('gift')->model('cat')->dump($cols);
            return $a['cat_name'];
        }
    }
    public function get_schema(){
        $this->app = app::get('b2c');
        $columns = parent::get_schema();
        $a['goods_id']['label'] = app::get('gift')->_('赠品ID');
        $a['goods_id']['pkey'] = true;
        $a['bn']['label'] = app::get('gift')->_('赠品编号');
        $a['cat_id']['label'] = app::get('gift')->_('赠品分类');
        $a['name']['label'] = app::get('gift')->_('赠品名称');
        $a['marketable']['label'] = app::get('gift')->_('是否开启');
        $a['uptime']['label'] = app::get('gift')->_('兑换起始时间');
        $a['downtime']['label'] = app::get('gift')->_('兑换结束时间');
        if(is_array($columns['columns'])) {
            foreach($columns['columns'] as $key => &$val) {
                if(!in_array($key, array('goods_id', 'bn', 'cat_id', 'name', 'marketable', 'uptime', 'downtime', 'p_order', 'price', 'weight', 'store'))) {
                    unset($columns['in_list'][array_search($key, $columns['in_list'])]);
                }
                if($a[$key])
                    $val['label'] = $a[$key]['label'];
            }
        }
        //$this->app = app::get('gift');
        return $columns;
    }
    
    
    public function table_name($real=false){
        $app_id = $this->app->app_id;
        $table_name = substr(get_parent_class($this),strlen($app_id)+5);
        if($real){
            return kernel::database()->prefix.$this->app->app_id.'_'.$table_name;
        }else{
            return $table_name;
        }
    }
    
    /*
    function _filter($filter,$tbase=''){
        
        if($this->use_meta){
            foreach(array_keys((array)$filter) as $col){
                if(in_array($col,$this->metaColumn)){
                    $meta_filter[$col] = $filter[$col];
                    unset($filter[$col]);  #ȥfilterаmeta
                    $obj_meta = new dbeav_meta($this->table_name(true),$col);
                    $meta_filter_ret .= $obj_meta->filter($meta_filter);
                }
            }
        }
        $dbeav_filter = kernel::single('dbeav_filter');
        $dbeav_filter_ret = $dbeav_filter->dbeav_filter_parser($filter,$tbase,$baseWhere,$this);
        
        
        print_r($dbeav_filter_ret);echo '----';
        print_r($meta_filter_ret);exit;
        if($this->use_meta){
            return $dbeav_filter_ret.$meta_filter_ret;
        }
        return $dbeav_filter_ret;
        
    }
    */
    
    
    public function _columns() {
        $tmp = parent::_columns();
        $tmp['cat_id']['type'] = 'table:cat@gift';
        return $tmp;
    }
    
    
    public function save(&$goods,$mustUpdate = null){
        $arr = parent::save( $goods,$mustUpdate );
        if( isset( $goods['product'] ) && is_array( $goods['product'] ) ) {
            $o = app::get('gift')->model('ref');
            foreach( $goods['product'] as $row ) {
                if( isset( $row['gift'] ) && is_array( $row['gift'] ) ) {
                    $tmp = $row['gift'];
                    $tmp['goods_id'] = $goods['goods_id'];
                    $tmp['product_id'] = $row['product_id'];
                    $tmp['bn'] = $row['bn'];
                    $o->save2save( $tmp );
                }
            }
        }
        return $arr;
    }
    
    public function delete( $filter,$subSdf='delete' ) {
        if( $filter['goods_id'] ) {
            $goods_id = $filter['goods_id'];
        } else {
            $arr = $this->getList( 'goods_id',$filter );
            reset( $arr );
            $arr = current( $arr );
            $goods_id = $arr['goods_id'];
        }
        
        if( $goods_id ) {
            if( parent::delete( $filter,$subSdf ) ) {
                app::get('gift')->model('ref')->delete2delete( array('goods_id'=>$goods_id) );
            }
            return false;
        }
        return false;
    }
    
    
    /**
     * @params string goods_id
     * @params string product_id
     * @params string num
     */
    public function unfreez($goods_id, $product_id, $num)
    {
        return kernel::single(get_parent_class($this))->unfreez( $goods_id, $product_id, $num );
    }
    
    
     /**
     * 冻结产品的库存
     * @params string goods_id
     * @params string product_id
     * @params string num
     */
    public function freez( $goods_id, $product_id, $num )
    {
        if( !$product_id ) return false;
        $o = app::get('gift')->model('ref');
        $arr = $o->getList( 'real_limit', array('product_id'=>$product_id) );
        reset( $arr );
        $arr = current( $arr );
        $tmp = array('product_id'=>$product_id,'real_limit'=>($num+$arr['real_limit']));
        $o->save2save( $tmp );
        return kernel::single(get_parent_class($this))->freez( $goods_id, $product_id, $num );
    }
    
    
    
    function checkProductBn($bn, $gid=0){
        return app::get('b2c')->model('goods')->checkProductBn($bn, $gid);
    }
    
    
    public function _filter($filter,$tbase=''){
        if( !$filter['goods_type'] && $this->use_filter_default )
            $filter['goods_type'] = $this->filter_default['goods_type'];
        else $filter['goods_type'] = array('gift','normal');
        return  kernel::single('b2c_goods_filter')->goods_filter($filter, $this);
    }
    
    
}