<?php

 
/**
 * 购物车项model
 * $ 2010-04-28 20:02 $
 */
class b2c_mdl_cart_objects extends dbeav_model{
    private $kv_prefix = 'cart_objects';
    private $kv_prefix_locked = false; //kv锁
    
    /**
     * 加入购物车 (目前的相法goods,package,coupon) 以后有扩展都以 b2c_cart_add_扩展就行了  有数量的修改也在这里处理
     *
     * @param array  $aData  // $_POST $_GET 等数据
     * @param string $sType  // 类型对应于b2c_cart_apps 里的 b2c_cart_$sType里的add处理
     * @return boolean
     */
    public function add_object($aData,$sType='goods') {
        if(empty($sType)) return false;
        $aResult = false;
        foreach($this->_get_cart_object_apps() as $object){
            if($object->get_type()==$sType) {
                $aResult = $object->add_object($aData);break;
            }
        }
        return $aResult;
        
        /*
        $obj = kernel::single('b2c_cart_object_'.$sType);
        if(is_object($obj)) {
            $aResult = $obj->add_object($aData);
            return $aResult;
        } else {
            return false;
        }
        */
        //$this->setCartNum();
        
    }

    /**
     * 获取指定的类型购物车数据 指定$sIdent的数据
     *
     * @param string $sType
     * @param string $sIdent
     * @return array()
     */
    public function get_object($sType = null,$sIdent = null) {
        

        // 清空所有
        if(empty($sType)) {
            $result = array('object'=>array());
            foreach($this->_get_cart_object_apps() as $object){
                $arr = $object->getAll(true);
                if($arr) $result['object'][$object->get_type()] = $arr;
            }
            $flag = $result;
        } else {
            // $sIdent 为空 返回指定类型所有的购物车数据
            $flag = kernel::single('b2c_cart_object_'.$sType)->get($sIdent);
        }
        
        //$this->setCartNum();
        return $flag;
    }

    /**
     * 删除指定的类型购物车数据
     *
     * @param string $sType
     * @param string $sIdent
	 * @param string $msg
     */
    public function remove_object($sType='', $sIdent = null, &$msg='') {
        foreach($this->_get_cart_object_apps() as $key => $object){
            if(!is_object($object)) continue;
            if(empty($sType)) { //不带参数清空所有
                $flag = $object->delete($sIdent);
                $this->_del_cookie();
                if( $flag==='false' ) break;
                continue;
            }
            
            if(method_exists($object, 'get_type')) {
                $t_type = $object->get_type();
                if(empty($t_type) || $t_type!=$sType) {
                    continue;
                } else {
                    $object->delete($sIdent);
                }
            } else {
                return false;
            }
        }
        
        //$this->setCartNum();
        return true;

    }
    
     /**
     * 删除指定的类型购物车数据
     *
     * @param string $sType
     * @param string $sIdent
	 * @param string $quantity
	 * @param string $msg
     */
    public function remove_object_part($sType='', $sIdent, $quantity, &$msg='') {
        if(empty($sType)) return false;
        $status = true;
        foreach($this->_get_cart_object_apps() as $object){
            if(in_array($sType,$object->get_part_type())) {
                if( method_exists($object,'remove_object_part') ) {
                    $status = $object->remove_object_part($sIdent, $sType, $quantity, $msg);break;
                }
            }
        }
        //$this->setCartNum();
        return $status;
    }


    /**
     * 修改指定$sIdent的数据的数量
     *
     * @param string $sType
     * @param string $sIdent
     * @return array()
     */
    public function update_object($sType='', $sIdent,$quantity) {
        if(empty($sType)) return false;
        foreach($this->_get_cart_object_apps() as $object){
            if($object->get_type()==$sType) {
                $status = $object->update($sIdent, $quantity);break;
            }
        }
        //$this->setCartNum();
        return $status;
    }
    
    
    private function _get_cart_object_apps() {
        if( !$this->_cart_object_apps ) {
            $this->_cart_object_apps = array();
            $tmp = array();
            foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
                if(!is_object($object)) continue;
                if( method_exists($object,'get_order') ) 
                    $index = $object->get_order('del');
                else $index = 10;
                while(true) {
                    if( !isset($tmp[$index]) )break;
                    $index++;
                }
                $tmp[$index] = $object;
            }
            krsort($tmp);
            $this->_cart_object_apps = $tmp;
        }
        return $this->_cart_object_apps;
    }
    /**
     * 将购物车中几种/几个商品数量写入 $_COOKIE['S[CART_COUNT]'] $_COOKIE['S[CART_NUMBER]']
     * @global string document the fact that this function uses $_myvar
     * @staticvar integer $staticvar this is actually what is returned
     * @param string $param1 name to declare
     * @param array $aCart 购物车数组
     * @return bool
     */
    function setCartNum( &$aCart )
    {
        if(empty($aCart)) {
            $aCart = $this->get_object();
            $this->app->model('cart')->count_objects($aCart);
        }

        $trading = $aCart['object'];
        $number = $count = 0;

        #$count = count($trading['goods'])+count($trading['gift_e'])+count($trading['package']);
        $count = $aCart['items_count_widgets'];

        $number = $aCart['items_quantity_widgets'];
        
        $totalPrice = $aCart['subtotal'] - $aCart['discount_amount'];
        
        $trading['totalPrice'] = $totalPrice;
        
        $this->_setCookie($count, $number, $totalPrice);
        $arr = $aCart['_cookie'] = array(
                                'CART_COUNT'    =>  $count,
                                'CART_NUMBER'   =>  $number,
                                'CART_TOTAL_PRICE'   =>  $totalPrice,
                                //'trading'       =>  $trading, //没查出来那里用。注释掉了
                            );
        return $arr;
       
    }
    
    public function _setCookie($count=0, $number=0, $totalPrice=0) {
        ob_start();
        if($count!==$_COOKIE['S[CART_COUNT]']){
            setCookie('S[CART_COUNT]',$count, null, '/');
        } else {
        }
        if($number!==$_COOKIE['S[CART_NUMBER]']){
            setCookie('S[CART_NUMBER]',$number, null, '/');
        }
        
        setcookie('S[CART_TOTAL_PRICE]', kernel::single('ectools_mdl_currency')->changer_odr($totalPrice), null, '/');
        ob_end_clean();
    }
    
    
    public function _del_cookie() {
        ob_start();
        setCookie('S[CART_NUMBER]', 0, time()-3600, '/');
        setCookie('S[CART_COUNT]', 0, time()-3600, '/');
        setcookie('S[CART_TOTAL_PRICE]',0, time()-3600, '/');
        ob_end_clean();
    }
    
    
    
    
    public function save( &$data,$mustUpdate = null ){
        $arr_member_info = $this->get_member_info();
        if( $arr_member_info['member_id'] )
            $data['member_ident'] = $this->get_md5_ident( $arr_member_info['member_id'] );
        if( !$data['member_ident'] ) $data['member_ident']=kernel::single('base_session')->sess_id();
        if( !$data['member_ident'] || !$data['obj_type'] || !$data['obj_ident'] ) return false;
        
        $data['member_id'] = ( $arr_member_info['member_id'] ? $arr_member_info['member_id'] : -1 );
        
        if( empty( $data['member_id'] ) ) return false;
        if( empty( $data['obj_ident']) ) return false;
        if( empty( $data['params'] ) ) {
            $arr = $this->getList( '*', array( 'obj_ident'=>$data['obj_ident'] ) );
            $arr = $arr[0];
            $data['params'] = $arr['params'];
        }
        
		$this->checkCoupons($data); 

        $data['time'] = time();
        return $this->parent_save( $data, $mustUpdate );
    }
    /*
	* 一个店铺只能使用一张优惠券
	* 
	*/
	public function checkCoupons($data){
		$arr = $this->getList('*',array('member_ident'=>$data['member_ident'],'obj_type'=>'coupon'));
		foreach($arr as $value){
			if($data['params']['store_id']==$value['params']['store_id']){
				$this->delete(array('member_ident'=>$value['member_ident'], 'obj_ident'=>$value['obj_ident'],'obj_type'=>'coupon'));
				break;
			}
		}

	}
	/*
	* 删除某个店铺的优惠券
	*  
	*/
	public function delete_store_coupon($store_id){
		$arr_member_info = $this->get_member_info();
		if( $arr_member_info && $arr_member_info['member_id'] ){
            $member_ident = $this->get_md5_ident( $arr_member_info['member_id'] );
			$arr = $this->getList('*',array('member_ident'=>$member_ident,'obj_type'=>'coupon'));
			foreach($arr as $value){
				if($store_id==$value['params']['store_id']){
					$this->delete(array('member_ident'=>$value['member_ident'], 'obj_ident'=>$value['obj_ident'],'obj_type'=>'coupon'));
					break;
				}
			}
		}
	}
    
    public function getList( $cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null ){
        if( !$filter['member_ident'] ) $filter['member_ident']=kernel::single('base_session')->sess_id();
        
        $arr_member_info = $this->get_member_info();
        if( $arr_member_info && $arr_member_info['member_id'] )
            $member_ident = $this->get_md5_ident( $arr_member_info['member_id'] );
        if( $arr_member_info['member_id'] ) {
            if( is_array($filter) ) {
                $tmp = $filter;
                $tmp['member_id'] = '-1';
                unset( $tmp['obj_ident'] );
                $arr = $this->parent_getlist( $cols, $tmp, $offset, $limit, $orderType );
                if( $arr && is_array($arr) ) {
                    reset( $arr );
                    $tmp = current( $arr );
                    if( $tmp['member_ident'] ) {
                        foreach( (array)$arr as $row ) {
                            $f = array('obj_ident'=>$row['obj_ident'],'member_ident'=>$tmp['member_ident'],'member_id'=>'-1');
                            parent::delete( $f );
                            $this->parent_delete( $f );
                            $row['member_id'] = $arr_member_info['member_id'];
                            $this->save( $row );
                        }
                    }
                }
            }
            $filter['member_id'] = $arr_member_info['member_id'];
            unset( $filter['member_ident'] );
        } else {
            $filter['member_id'] = '-1';
        }
        
        if( $member_ident )  //统一会员标识
            $filter['member_ident'] = $member_ident;
        
        
        $arr = $this->parent_getlist ( $cols, $filter, $offset, $limit, $orderType );
        return $arr;
    }
    
    
    public function count( $filter=array() ) {
        if( $tmp['member_id']=='-1' ) {
            $arr = $_SESSION['b2c_cart_objects'][$tmp['member_ident']];
            $arr = $this->filter_getlist( $arr,$filter );
        } else {
            if( $this->use_kv() ) { //使用kv
                $this->kv_instance()->fetch( $this->kv_prefix,$arr );
                $arr = $this->filter_getlist( $arr,$filter );
            } else {  //使用database
                return parent::count( $filter );
            }
        }
        return $arr ? count($arr) : 0;
    }
    
    private function parent_getlist( $cols, $tmp, $offset, $limit, $orderType ) {
        if( $tmp['member_id']=='-1' ) {
            $arr = $_SESSION['b2c_cart_objects'][$tmp['member_ident']];
            $arr = $this->filter_getlist( $arr,$tmp );
            return $arr ? $arr : array();
        } else {
            if( $this->use_kv() ) { //使用kv
                $this->kv_instance()->fetch( $this->kv_prefix,$arr );
                $arr = $this->filter_getlist( $arr,$tmp );
                return $arr ? $arr : array();
            } else {  //使用database
                return parent::getList( $cols, $tmp, $offset, $limit, $orderType );
            }
        }
    }
    
    private function filter_getlist( $arr,$tmp ) {
        $return = array();
        if( $arr && is_array($arr) ) {
            foreach( $arr as $row ) {
                if( $row['obj_type']!=$tmp['obj_type'] && $tmp['obj_type'] )  continue;
                if(is_array($tmp['obj_ident'])){
                    if( !in_array($row['obj_ident'], $tmp['obj_ident']) && $tmp['obj_ident'] )  continue;
                }else{
                    if( $row['obj_ident']!=$tmp['obj_ident'] && $tmp['obj_ident'] )  continue;
                }
                if( $row['member_ident']!=$tmp['member_ident'] && $tmp['member_ident'] ) continue;
                
                $return[] = $row;
            }
        } else {
            $return = $arr;
        }
        return $return;
    }
    
    
    
    private function parent_save( $data, $mustUpdate ) {
        if( $data['member_id']=='-1' ) {
            $arr = $_SESSION['b2c_cart_objects'][$data['member_ident']];
            if( $arr && is_array($arr) ) {
                $add = true;
                foreach( $arr as &$row ) {
                    if( $row['obj_ident']==$data['obj_ident'] ) {
                        $add =  false;
                        $row = array_merge($row,$data);break;
                    }
                }
                if( $add )
                    $arr[] = $data;
            } else {
                $arr = array($data);
            }
            $_SESSION['b2c_cart_objects'][$data['member_ident']] = $arr;
            return true;
        } else {
            
            $flag = parent::save( $data, $mustUpdate );
            if( $this->use_kv() ) {
                $filter = array('member_ident'=>$data['member_ident'],'member_id'=>$data['member_id']);
                $arr = parent::getList( '*',$filter );
                return $this->kv_instance()->store( $this->kv_prefix,$arr );
            }
            return $flag;
        }
    }
    
    private function parent_delete( $filter,$subSdf=array() ) {
         if( $filter['member_id']=='-1' ) {
            $arr = $_SESSION['b2c_cart_objects'][$filter['member_ident']];
            if( $arr && is_array($arr) ) {
                foreach( $arr as $key => &$row ) {
                    if( $row['obj_ident']==$filter['obj_ident'] || empty($filter['obj_ident']) ) {
                        unset($arr[$key]);
                    }
                }
            } else {
                $arr = array($data);
            }
            $_SESSION['b2c_cart_objects'][$filter['member_ident']] = $arr;
            return true;
        } else {
            $flag = parent::delete( $filter,$subSdf );
            if( $filter['member_ident'] )
                $f = array('member_ident'=>$filter['member_ident']);
            if( $filter['member_id'] )
                $f['member_id'] = $filter['member_id'];
            $arr = parent::getList( '*',$f );
            #if( $arr )
                $this->kv_instance()->store( $this->kv_prefix,$arr );
            #else 
            #    $this->kv_instance()->delete( $this->kv_prefix );
            return $flag;
        }
    }
    
    
    //是否使用kv
    private function use_kv() {
        return true;
    }
    
    //kv实例化
    private function kv_instance() {
        return kernel::single("base_kvstore")->instance('b2c-cart');
    }
    
    public function delete( $filter,$subSdf = 'delete' ) { 
        if( !$filter['member_ident'] ) $filter['member_ident']=kernel::single('base_session')->sess_id();
        
        $arr_member_info = $this->get_member_info();
        
        if( $arr_member_info['member_id'] ) {
            if( is_array($filter) ) {
                $filter['member_id'] = $arr_member_info['member_id'];
                unset( $filter['member_ident'] );
                return $this->parent_delete( $filter,$subSdf );
            }
            return false;
        } else {
            $filter['member_id'] = -1;
            return $this->parent_delete( $filter,$subSdf );
        }
    }
    
    
    private function get_member_info() {
        if( empty( $this->arr_member_info ) ) {
            $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        }
        
        //kv键值
        if( $this->arr_member_info['member_id'] && !$this->kv_prefix_locked) {
            $this->kv_prefix = $this->kv_prefix.'/'.$this->arr_member_info['member_id'];
            $this->kv_prefix_locked = true;
        }
        
        return $this->arr_member_info;
    }
    
    private function get_md5_ident( $str='' ) {
        return md5( $str );
    }
    
    

}
