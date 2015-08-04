<?php

 
    
    /**
     * 购物车赠品相关逻辑
     *
     */ 
    class gift_cart_object_gift {
        /**
         * 用户标识
         *
         * @access private
         */
        private $member_ident;
        /**
         * 默认最大限购数量
         *
         * @access private
         */
        private $max_store = 999999999;
        
        public function __construct() {
            $this->app = app::get('b2c');
		$this->current_app = app::get('gift');
            
            $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
            $this->member_ident = kernel::single("base_session")->sess_id();
            
            $this->oCartObjects = $this->app->model('cart_objects');
            
		$o_ctl = $this->current_app->controller('site_gift');
            $this->o_p_ctl = kernel::single(get_parent_class($o_ctl));
            
            $this->o_gift = kernel::single('gift_mdl_goods');
            
            $this->o_product = kernel::single('gift_mdl_products');
            
        }
	
	public function get_type() {
		return 'gift';
	}
	
	public function get_max_store() {
		return $this->max_store;
	}
	
	public function get_part_type() {
		return array('gift');
	}
	
	/**
	 * 购物车是否需要验证库存
	 * @param null
	 * @return boolean true or false
	 */
	public function need_validate_store() {
		return true;
	}
	
	/**
	 * 得到失败应该返回的url - app 数组
	 * @param array
	 * @return array
	 */
	public function get_fail_url($data=array())
	{
		$this->fail_return_url = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index');
		return $this->fail_return_url;
	}
	
	/**
	 * 处理加入购物车商品的数据
	 * @param mixed array 传入的参数
	 * @param string 消息
	 * @return mixed array 处理后的数据
	 */
	public function get_data($params=array(),&$msg='')
	{
		if( $params['goods'] ) {
            $arr = $params['goods'];

            if( !$arr['product_id'] ) {
                if( $arr['goods_id'] ) {
                    $arr_gift = $this->current_app->model('ref')->getList( 'product_id',array('goods_id'=>$arr['goods_id']) );
                    if( count($arr_gift)==1 ) {
                        $arr_gift = $arr_gift[0];
                        $arr['gift_id'] = (int)$arr_gift['product_id'];
                    }
                }
            } else {
                $arr['gift_id'] = $arr['product_id'];
            }
            unset( $arr['product_id'] );
        } else {
            $arr['goods_id'] = (int)$params[1];
            $arr['gift_id'] = (int)$params[2];
            $arr['num'] = (int)$params[3];
        }

        if ( empty( $arr['gift_id'] ) || empty( $arr['goods_id'] ) ) return false;
		
        return $arr;
	}
	
        /**
	 * 校验加入购物车数据是否符合要求-各种类型的数据的特殊性校验
	 * @param array 加入购物车数据
	 * @param string message 引用值
	 * @return boolean true or false
         */ 
	public function check_object($arr_data,&$msg='')
	{
		return true;
        }
	
        /**
	 * 检查库存
	 * @param array 加入购物车的商品结构
	 * @param array 现有购物车的数量
	 * @param string message
	 * @return boolean true or false
         */ 
	public function check_store($arr_data, $arr_carts, &$msg='')
	{
		if(empty($this->member_ident)){
			$msg = app::get('gift')->_('添加的赠品标识不存在！');
			return false;
		}
		
		if(empty($arr_data['gift_id'])){
			$msg = app::get('gift')->_('赠品不存在！');
			return false;
		}
		$goods_id = $arr_data['goods_id'];
		
		if($this->app->getConf('site.get_policy.method')==1) {
			$this->fail_return_url = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index', 'arg0'=>$goods_id);
			$msg = app::get('gift')->_('未开启积分不能兑换！');
			return false;
		}
		
		$arr_member_info = $this->arr_member_info;# = array('member_id'=>1);		
		
		$appgift = $this->current_app;
		
		//跳转地址
		$this->fail_return_url = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'lists');
		
		if($arr_member_info['member_id']) {
			if($goods_id) {
				$arr_gift_info = $this->o_product->dump_1( array('product_id'=>$arr_data['gift_id']),'*'  );
				$arr_gift_info['gift']['max_limit'] = $arr_gift_info['gift']['max_limit'] 
														? $arr_gift_info['gift']['max_limit'] 
														: ( (empty($arr_gift_info['gift']['max_limit']) && $arr_gift_info['gift']['max_limit']!==0 ) 
															? $this->max_store 
															: 0
														  );
				#跳转地址
				$this->fail_return_url = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index', 'arg0'=>$goods_id);
				
				//是否超出了最大限购数量
				if( $arr_gift_info['gift']['real_limit'] >= $arr_gift_info['gift']['max_limit'] ) {
					$msg = $appgift->_('加入购物车失败！该赠品已赠送结束！');
					return false;
					
				//是否在兑换时间范围之内
				} elseif( $arr_gift_info['gift']['start_time']>=time() || $arr_gift_info['gift']['to_time']<=time() ) {
					$msg = $appgift->_('加入购物车失败！该赠品不在兑换时间范围之内！');
					return false;
					
				//其他判断
				}else {
					//b2c库存信息
					if( empty($arr_gift_info['store']) && $arr_gift_info['store'] !==0 ) $arr_gift_info['store']  = $this->max_store;
					$arr_gift_info['store'] = $arr_gift_info['store'] - $arr_gift_info['freez'];
					if( $arr_gift_info['store']<0 ) $arr_gift_info['store'] = 0;
					
					if( empty($arr_gift_info['gift']['max_buy_store']) ) $arr_gift_info['gift']['max_buy_store'] = $this->max_store;
					
					//赠品实际配额信息
					$buy_store = $arr_gift_info['gift']['max_limit'] - $arr_gift_info['gift']['real_limit'];
					if( $arr_gift_info['store']>$buy_store ) $arr_gift_info['store'] = $buy_store;
					
					$this->fail_return_url = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index', 'arg0'=>$goods_id);
					
					
					if( $data['quantity']<=$arr_gift_info['store'] ) {
						if($data['quantity']>$arr_gift_info['gift']['max_buy_store']){
							$msg = $appgift->_('超出限购数量！');
							return false;
						}
						
						//会员信息数组 会员只限积分兑换 没有会员信息错误
						if($arr_member_info) {  
							
							//查看是否符合允许兑换的会员等级
							if(in_array($arr_member_info['member_lv'], explode(',', $arr_gift_info['gift']['member_lv_ids']))) {
								$num = (int)$data['quantity'];
								$num = $num ? $num : 1;
								
								//判断积分是否足够
								if(isset($arr_gift_info['gift']['consume_score']) && (($arr_gift_info['gift']['consume_score']*$num)>$arr_member_info['usage_point'])) {
									if($tmp_arr[0]) $data['quantity'] = $tmp_arr[0]['quantity'];
									$msg = $appgift->_('积分不足！！加入购物车失败!');
									return false;
								} else {
									//条件符合 
									return true;
								}
							} else {
								 $msg = $appgift->_('加入购物车失败！您所在会员类型不能兑换');
								 return false;
							}
						} else {
							 $msg = $appgift->_('加入购物车失败！');
							 return false;
						}
					} else {
						$msg = $appgift->_('加入购物车失败！赠品数量不足@！');
						return false;
					}
				}
			} else {
				$msg = $appgift->_('赠品不存在！相关商品数据丢失！');
				return false;
			}
		} else {
			$msg = $appgift->_('赠品只限会员积分兑换！！');
			return false;
		}
		
		return true;
        }
    
        /**
	 * 添加购物车项(goods)
	 * @param array 需要保存的数据
	 * @param string message
	 * @return boolean
         */ 
	public function add_object($arr_data, &$msg='', $append=true)
	{
		$data = $arr_data;
            if(empty($data)) return false;
            $objIdent = $this->_generateIdent($data);
            $filter = $aSave = array(
                   'obj_ident'    => $objIdent,
                   'member_ident' => $this->member_ident,
                   'obj_type'     => 'gift',
                 );
            $aSave['params']       = array(
                                        'gift_id'   =>  $data['gift_id'],
                                        'goods_id' => $data['goods_id'],
                                        'extends_params' => $data['extends_params'],
                                        );
            $aSave['quantity']     = $data['num'] ? $data['num'] : 1;
            if ($aData = $this->oCartObjects->getList('*', $filter, 0, -1, -1)){
                $aSave['quantity'] += $aData[0]['quantity'];
            }
            
            if(($flag=$this->_check($aSave))!==true) return $flag;
            $flag = $this->oCartObjects->save($aSave);
		if (!$flag){
			$msg = app::get('b2c')->_('购物车保存失败！');
			return false;
		}
        return $aSave['obj_ident'];
	}
            
        
        /**
         * 生成赠品标识：格式为gift_商品ID_赠品ID
         *
         * @return string
         */ 
        private function _generateIdent($data) {
            return "gift_". $data['goods_id'] .'_'. $data['gift_id'];#.'_'. ( $this->arr_member_info['member_id'] ? $this->arr_member_info['member_id'] : 0 );
        }
        
        
        
    /**
     * 修改购物车内赠品数量
     *
     * @param string $sIdent 赠品标识
     * @param int $quantity 赠品数量
     * @return boolean
     */
    public function update($sIdent='', $quantity=0) {
        if( empty($sIdent) || empty($quantity) ) return false;
        $arr_data = array(
                        'obj_ident' => $sIdent,
                        'member_ident' => $this->member_ident,
                        'obj_type' => 'gift',
                        'quantity' => $quantity,
                    );
        
        if(($flag=$this->_check($arr_data))!==true) return $flag['end'];
        if( $arr_data['quantity']!=$quantity ) return false;
        $flag = $this->oCartObjects->save($arr_data);
        return $flag ? true : false;
    }
    /**
     * 获取指定购物车内指定赠品标识的数据
     *
     * @param string $sIdent 赠品标识
     * @param boolean $rich 为true时返回更详细的数据
     * @return array
     */
    public function get($sIdent = null,$rich = false) {

        if(empty($sIdent)) return $this->getAll($rich);
        
        $aResult = $this->oCartObjects->getList('*',array(
                                           'obj_ident' => $sIdent,
                                           'member_ident'=> $this->member_ident,
                                        ));
        if(empty($aResult)) return array();
        if($rich) {
            $aResult = $this->_get($aResult);
            $aResult = $aResult[0];
        }
        
        return $aResult;
    }
    
    
    /**
     * 根据赠品信息取出对应的商品信息
     *
     * @param array $aData 赠品信息
     * @return array
     */
    private function _get_gift_info( $aData ) {
        foreach($aData as $_key => $row) {
            $params = $row['params'];
            if(!isset($params['gift_id']) || empty($params['gift_id'])) continue;
            $arr_gift_id[] = $params['gift_id'];
        }
        if( $arr_gift_id ) {
            return kernel::single("gift_cart_object_goods")->_get_products($arr_gift_id);
        }
        return array();
    }
    /**
     * 根据赠品信息组装购物车数据
     *
     * @param array $aData 赠品信息
     * return array 
     */
    public function _get($aData){

        $aResult['cart'] = array();
        
        $arr_gift = $this->_get_gift_info( $aData );
        
        #if( !isset($this->arr_member_info) ) {
        #    $this->arr_member_info = $this->o_p_ctl->get_current_member();
        #}
        $arr_member_info = $this->arr_member_info;
        
        foreach($aData as $_key => $row) {
            
            $params = $row['params'];
            if(!isset($params['gift_id']) || empty($params['gift_id'])) continue;
            
            $info[intval($params['gift_id'])] =  $arr_gift[intval($params['gift_id'])];
            if( empty($arr_member_info) ) {
                unset($aData[$key]);
                continue;
            }
            if( is_array($info) ) {
                foreach( $info as &$_gift ) {
                    if( !empty($_gift['params']['consume_score']) ) 
                        $store = floor($arr_member_info['usage_point'] / $_gift['params']['consume_score']);
                    else $store = $this->max_store;
                    
                    !empty($_gift['params']['max_store']) or $_gift['params']['max_store'] = $this->max_store;
                    if( $store < $_gift['params']['max_store'] ) $_gift['params']['real'] = $store;
                    else $_gift['params']['real'] = $_gift['params']['max_store'];
                    
                    if( $_gift['params']['real']>$_gift['max_buy_store'] ) $_gift['params']['real']=$_gift['max_buy_store'];
                }
            }
            if( empty($info) ) continue;

            $tmp = array(
                        'obj_ident' => $row['obj_ident'],
                        'obj_type' => 'gift',
                        'quantity' => $row['quantity'],
                        'gift_id'=> $params['gift_id'],
                    );
            foreach( $tmp as $_k => $_v ) {
                unset( $info[$params['gift_id']][$_k] );
            }
            $aResult['cart'][] = array_merge( $tmp, (array)$info[$params['gift_id']] );
            
        }

        return $aResult;
    }
    /**
     *  获取购物车内所有赠品信息
     *
     *  @param boolean $rich 为true时返回较详细的数据
     *  @return array
     */
    public function getAll($rich = false) {
        //积分设置的用途
		$site_point_usage = $this->app->getConf('site.point_usage');
      if($site_point_usage != '1'){
          //只用于抵扣，不能兑换
         return array();
        }
		
		if(kernel::single("b2c_cart_object_goods")->get_cart_status()) {
            return array();
        } else {
			$aResult = $this->oCartObjects->getList('*',array(
                                           'obj_type' => 'gift',
                                           'member_ident'=> $this->member_ident,
                                       ));
		}

        if(empty($aResult)) return array();
        

        if(!$rich) return $aResult;
        
        return $this->_get($aResult);
    }
    /**
     * 删除购物车内赠品
     *
     * @param string $sIdent 赠品标识
     * @return boolean 
     */
    public function delete($sIdent = null) {
        if(empty($sIdent)) return $this->deleteAll();
        return $this->oCartObjects->delete(array('obj_ident'=>$sIdent, 'obj_type'=>'gift', 'member_ident'=> $this->member_ident));
    }

    /**
     * 删除购物车内所有赠品
     *
     * @return boolean
     */
    public function deleteAll() {
        return $this->oCartObjects->delete(array( 'obj_type'=>'gift', 'member_ident'=> $this->member_ident));
    }

    /**
     * 获取购物车内赠品数量、积分、重量信息
     * 
     * @param array &$aData 购物车数据
     * @return array
     */
    public function count(&$aData) {
        if(isset($aData['object']) && isset($aData['object']['gift'])) {
           $obj_math = kernel::single('ectools_math');
            $aResult['items_count_widgets'] = 0;
            $aResult['items_quantity_widgets'] = 0;
            if( isset($aData['object']['gift']['cart']) ) {
                foreach($aData['object']['gift']['cart'] as $row) {
                    $aData['subtotal_consume_score'] += $row['consume_score'] * $row['quantity'];
                    
                    $aResult['items_quantity_widgets'] += $row['quantity'];
                    $aResult['items_count_widgets']++;
                    $aResult['subtotal_weight'] += $obj_math->number_multiple(array($row['weight'],$row['quantity']));
                }
            }
            
            if( isset($aData['object']['gift']['order']) ) {
                foreach($aData['object']['gift']['order'] as $row) {
                    #$aResult['items_quantity'] += $row['quantity'];
                    #$aResult['items_count']++;
                }
            }
        }
        return $aResult;
    }


    
    /**
     * 检查是否能兑换赠品
     *
     * @param array &data 要兑换的赠品数据
     * @return boolean|array 不能兑换返回false或包含出错信息的数组，成功返回true
     */ 
    private function _check( &$data ) {
        if(empty($this->member_ident)) return false;
        $params = $data['params'];

        if( !isset($data['params']) || empty($data['params']) ) {
            $filter['obj_ident'] = $data['obj_ident'];
            $filter['member_ident'] = $data['member_ident'];
            $filter['obj_type'] = $data['obj_type'];
            $tmp_arr = $this->oCartObjects->getList('*', $filter);
            $params = $tmp_arr[0]['params'];
        }
        //购物车所有赠品数据
       $gift_all = $this->getAll(true);
        //购物车中有赠品兑换,计算兑换总积分
        $totla_score_gift = 0;
     
        if($gift_all['cart'])
        {
            foreach($gift_all['cart'] as $gift_k=>$gift_v)
            {   if($gift_v['gift_id'] != $params['gift_id']) 
                $totla_score_gift = $totla_score_gift+$gift_v['consume_score']*$gift_v['quantity'];
            }
        }
        #print_r($params);

        if(empty($params['gift_id'])) return false;
        
        $goods_id = $params['goods_id'];
        
        
		if($this->app->getConf('site.get_policy.method')==1) {
            $return['begin'] = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index', 'arg0'=>$goods_id);
            $return['end'] = array('status'=>false,  'msg'=>'未开启积分不能兑换！');
            return $return;
        }
        
        #if( !$this->arr_member_info ) {
        #    $this->arr_member_info = $this->o_p_ctl->get_current_member();
        #}
        $arr_member_info = $this->arr_member_info;# = array('member_id'=>1);;
        
        
		$appgift = $this->current_app;
        
        //跳转地址
        $return['begin'] = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'lists');
        
        if($arr_member_info['member_id']) {
            if($goods_id) {
                $arr_gift_info = $this->o_product->dump_1( array('product_id'=>$params['gift_id']),'*'  );
                $arr_gift_info['gift']['max_limit'] = $arr_gift_info['gift']['max_limit'] 
                                                        ? $arr_gift_info['gift']['max_limit'] 
                                                        : ( (empty($arr_gift_info['gift']['max_limit']) && $arr_gift_info['gift']['max_limit']!==0 ) 
                                                            ? $this->max_store 
                                                            : 0
                                                          );
                #跳转地址
                $return['begin'] = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index', 'arg0'=>$goods_id);
                
                //是否超出了最大限购数量
                if( $arr_gift_info['gift']['real_limit'] >= $arr_gift_info['gift']['max_limit'] ) {
                    $msg = $appgift->_('加入购物车失败！该赠品已赠送结束！');
                    
                //是否在兑换时间范围之内
                } elseif( $arr_gift_info['gift']['start_time']>=time() || $arr_gift_info['gift']['to_time']<=time() ) {
                    $msg = $appgift->_('加入购物车失败！该赠品不在兑换时间范围之内！');
                    
                //其他判断
                }else {
                    //b2c库存信息
                    if( empty($arr_gift_info['store']) && $arr_gift_info['store'] !==0 ) $arr_gift_info['store']  = $this->max_store;
                    $arr_gift_info['store'] = $arr_gift_info['store'] - $arr_gift_info['freez'];
                    if( $arr_gift_info['store']<0 ) $arr_gift_info['store'] = 0;
                    
                    if( empty($arr_gift_info['gift']['max_buy_store']) ) $arr_gift_info['gift']['max_buy_store'] = $this->max_store;
                    
                    //赠品实际配额信息
                    $buy_store = $arr_gift_info['gift']['max_limit'] - $arr_gift_info['gift']['real_limit'];
                    if( $arr_gift_info['store']>$buy_store ) $arr_gift_info['store'] = $buy_store;
                    
                    $return['begin'] = array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'index', 'arg0'=>$goods_id);
                    
                    $mCart = app::get('b2c')->model('cart');
                    $aCart = $mCart->get_basic_objects();
                    $data['total_quantity'] = $data['quantity'];
                    foreach($aCart as $k=>$v) {
                        if($v['params']['goods_id'] == $arr_gift_info['goods_id'] && $v['params']['gift_id'] != $arr_gift_info['product_id']){
                            $data['total_quantity'] += $v['quantity'];
                        }
                    }
                    if( $data['quantity']<=$arr_gift_info['store'] ) {
                        #if($data['quantity']>$arr_gift_info['gift']['max_buy_store']) $data['quantity']=$arr_gift_info['gift']['max_buy_store'];
                        if($data['total_quantity']>$arr_gift_info['gift']['max_buy_store']){
                            $return['end']   = array('status'=>false,  'msg'=>$appgift->_('超出限购数量！'));
                            return $return;
                        }
                        
                        //会员信息数组 会员只限积分兑换 没有会员信息错误
                        if($arr_member_info) {  
                            
                            //查看是否符合允许兑换的会员等级
                            if(in_array($arr_member_info['member_lv'], explode(',', $arr_gift_info['gift']['member_lv_ids']))) {
                                $num = (int)$data['quantity'];
                                $num = $num ? $num : 1;
                                
                                //判断积分是否足够
                                if(isset($arr_gift_info['gift']['consume_score']) && (($arr_gift_info['gift']['consume_score']*$num+$totla_score_gift)>$arr_member_info['usage_point'])) {
                                    if($tmp_arr[0]) $data['quantity'] = $tmp_arr[0]['quantity'];
                                    $msg = $appgift->_('积分不足！！加入购物车失败!');
                                } else {
                                    //条件符合 
                                    return true;
                                }
                            } else {
                                 $msg = $appgift->_('加入购物车失败！您所在会员类型不能兑换');
                            }
                        } else {
                             $msg = $appgift->_('加入购物车失败！');
                        }
                    } else {
                        $msg = $appgift->_('加入购物车失败！赠品数量不足！');
                    }
                }
            } else {
                $msg = $appgift->_('赠品不存在！相关商品数据丢失！');
            }
        } else {
            $msg = $appgift->_('赠品只限会员积分兑换！！');
        }
        $return['end'] = array('status'=>false,  'msg'=>$msg);
        
        $return['end']['quantity'] = $data['quantity'];
        $return['status'] = $return['end'];
        
        return $return;
    }



    public function apply_to_disabled( $data,&$session,$flag ) {
        foreach( (array)$data as $_type => $_arr_by_type) {
            if( !isset($session[$_type]) ) continue;
            foreach( (array)$_arr_by_type as $_index => $_val ) {
                if( isset($session[$_type][$_val['product_id']]) && $session[$_type][$_val['product_id']]==='true' ) {
                    if( $flag ) {
                        $data[$_type][$_index]['disabled'] = 'true';
                    } else {
                        unset($data[$_type][$_index]);
                    }
                    $tmp[$_type][$_val['product_id']] = true;
                }
            }
        }
        
        foreach( (array)$session as $_key => $_val ) {
            foreach( (array) $_val as $_k => $_v ) {
                if( !$tmp[$_key][$_k] ) unset($session[$_key][$_k]);
            }
        }
        return $data;
    }
    
    /**
     * 计算需消费积分
     *
     * @param array $data 购物车数据
     * @ident string $ident 赠品标识
     * @return array
     */ 
    public function get_update_num( $data,$ident ) {
        $ident = $ident['ident'];
        foreach( (array)$data['cart'] as $row ) {
            if( $row['obj_ident']===$ident ) return array('buy_price'=>'积分兑换','consume_score'=>-($row['consume_score'] * $row['quantity']));
        }
    }

}
