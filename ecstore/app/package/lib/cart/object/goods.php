<?php 
class package_cart_object_goods extends b2c_cart_object_goods {
    
    public function _check_goods_with_get( &$aData, $arr_goods_id ) {
        parent::_check_goods( $aData,$arr_goods_id );
    }
    
    public function _check_products_with_get( &$aData, $arr_goods_id, $obj_type='package') {
        parent::_check_products( $aData,$arr_goods_id,$obj_type);
        if( !$aData ) return false;
        foreach( $aData as &$val ) {
            $val['quantity'] = 1;
        }
    }
    
    public function _check_products_with_add( $arr_params,$quantity ) {
        foreach( $arr_params as $key => $product_id ) {
            if( !$this->_check_products_info[$product_id] ) $arr[] = $product_id;
        }
        
        if( $arr ) {
            $arr = $this->_object('products')->getList( '*',array('product_id'=>$arr) );
            foreach( $arr as $row ) {
                $this->_check_products_info[$row['product_id']] = $row;
            }
        }
        
        foreach( $arr_params as $product_id ) {
            $data = $this->_check_products_info[$product_id];
            
            //货品名称
            $name = $data['name'];
            
            //报错信息
            $msg = null;
            
            //是否上架
            if( $data['marketable']=='false' ) { 
                $msg = '未上架';
            }
            
            if( !$this->nostore_sell[$data['goods_id']] ) {
                if( empty($data['store']) && $data['store']===0 ) {
                    $msg = '已缺货';
                } else if ($data['store'] && ( ($data['store']<$quantity) || ($data['store']<$data['freez']+$quantity) ) ) {
                    $msg = '库存不足';
                }
            }
            
            if( $msg ) {
                return $name.$msg;
            }
        }
        return true;
    }
    
    //////////////////////////////////////////////////////////////////////////
    // 验证商品信息
    ///////////////////////////////////////////////////////////////////////////
    public function _check_goods_with_add( $arr_params,$quantity ) {
        foreach( $arr_params as $key => $goods_id ) {
            if( !$this->_check_goods_info[$goods_id] ) $arr[] = $goods_id;
        }
        if( $arr ) {
            $arr = $this->_object('goods')->getList( '*',array('goods_id'=>$arr) );
            if( !$arr ) return '捆绑商品信息错误!';
            foreach( $arr as $row ) {
                $this->_check_goods_info[$row['goods_id']] = $row;
            }
        }
        foreach( $arr_params as $goods_id ) {
            $data = $this->_check_goods_info[$goods_id];
            $name = $data['name'];
            //报错信息
            $msg = null;
            
            
            //是否上架
            if( $data['marketable']=='false' ) { 
                $msg = '未上架';
            }
            
            //是否支持无库存销售
            if( $data['nostore_sell'] )
                $this->nostore_sell[$goods_id] = true;
            
            if( !$data['nostore_sell'] ) {
                if( empty($data['store']) && $data['store']===0 ) {
                    $msg = '已缺货!';
                } else if ($data['store'] && $data['store']<$quantity) {
                    $msg = '库存不足';
                }
            }
            
            if( $msg ) {
                return $name.$msg;
            }
        }
        return true;
    }
    
    private function _object( $model ) {
        if( !$this->_arr_object[$model] )
            $this->_arr_object[$model] = app::get('b2c')->model($model);
        return $this->_arr_object[$model];
    }
}