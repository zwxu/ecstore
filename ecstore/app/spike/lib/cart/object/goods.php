<?php 

/**
 * @package default
 * @author kxgsy163@163.com
 */
class spike_cart_object_goods extends b2c_cart_object_goods
{
    function __construct(&$app) {
        $this->app = app::get('b2c');

        $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        $this->member_ident = kernel::single("base_session")->sess_id();

        $this->oCartObjects = $this->app->model('cart_objects');

        $this->o_goods = $this->app->model('goods');
        $this->o_products = $this->app->model('products');

        if( !empty($this->arr_member_info) ) {
            $oMemeberLV = $this->app->model('member_lv');
            $aMLV = $oMemeberLV->dump(array('member_lv_id'=>$this->arr_member_info['member_lv']));
            $this->discout = (empty($aMLV['dis_count']) || $aMLV['dis_count'] > 1 || $aMLV['dis_count'] <= 0)? 1 : $aMLV['dis_count'];
        }

        $this->db = kernel::database();

        $this->omath = kernel::single('ectools_math');
    }

    function check($gid,$pid,$quantity=0,&$msg){
        if( !$gid ) {
            $msg = '商品ID丢失！';
            return false;
        }
        $arr = kernel::single('spike_cart_process_goods')->checkgoods($gid);
        if(!$arr){
            return true;
        }
        
        if( !$this->arr_member_info || !$this->arr_member_info['member_id'] ) {
            $msg = '只限会员秒杀！！！';
            #return false;
            $jump_to_url = app::get('site')->router()->gen_url( array('app'=>'b2c','ctl'=>'site_passport','act'=>'login','full'=>'true') );
            if($_POST['mini_cart']){
                echo json_encode( array('url'=>$jump_to_url) );exit;
            } else {
                kernel::single('base_controller')->splash( 'success',$jump_to_url );exit;
            }
        }
        $filter = array('member_id'=>$this->arr_member_info['member_id'],'member_ident'=>$this->member_ident);
        $arr_cart_objects = app::get('b2c')->model('cart_objects')->getList( '*',$filter );
        foreach( (array)$arr_cart_objects as $cart_objects ) {
            if($cart_objects['params']['goods_id']==$gid && $cart_objects['params']['product_id']!=$pid ) {
                $quantity += $cart_objects['quantity'];
            } 
        }
        
        $memberbuy = app::get('spike')->model('memberbuy');
        $buys = $memberbuy->getList('*',array('member_id'=>$this->arr_member_info['member_id'],'gid'=>$gid,'aid'=>$arr['aid'],'effective'=>'true'));
        $num=0;
        foreach($buys as $key=>$value){
            $num = $num + $value['nums'];
        }

        if( $arr['personlimit'] != '' && $arr['personlimit']<$num+$quantity ) {
            $msg = '累计购买数量超出每人限购数量！' ;
            return false;
        }
        if( $arr['remainnums'] != '' && $arr['remainnums']<$quantity ) {
            $msg = '已超出限购库存！';
            return false;
        }
        return true;

    }


    public function get_type() {
        return 'goods';
    }

    public function get_part_type() {
        return array('adjunct');
    }

    /**
     * 得到失败应该返回的url - app 数组
     * @param array
     * @return array
     */
    public function get_fail_url($data=array())
    {
        return array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index');
    }

    /**
     * 处理加入购物车商品的数据
     * @param mixed array 传入的参数
     * @param string 消息
     * @return mixed array 处理后的数据
     */
    public function get_data($params=array(),&$msg='')
    {
        if (!$params) return array();

        if(!$params['goods'] && !$params['coupon'] ) {
            $params['goods']['goods_id'] = (int)$params[1];
            if( $params[3] ) {
                $params['goods']['product_id'] = ($params[2]=='false' ? 0 : $params[2]);
                $params['goods']['num'] = (int)$params[3];
            } else {
                $arr = $this->app->model('products')->getList( '*',array('goods_id'=>(int)$params[1]) );
                if( is_array($arr) && count($arr)==1 ) {
                    reset( $arr );
                    $arr = current( $arr );
                    $params['goods']['product_id'] = (int)$arr['product_id'];
                    $params['goods']['num'] = (int)$params[2];
                } else {
                    $msg = app::get('b2c')->_('参数错误！加入购物车失败！');
                    return false;
                }
            }
            unset($params[1]);
            unset($params[2]);
            unset($params[3]);

            //未完待续 针对列表页。不知又没  配件
            //$params['goods']['adjunt'] = $daata[4];
        } else if( isset($params['goods']) ) {
                if( !$params['goods']['product_id'] ) {
                    $arr = $this->app->model('products')->getList( '*',array('goods_id'=>(int)$params['goods']['goods_id']) );
                    if( is_array($arr) && count($arr)==1 ) {
                        reset( $arr );
                        $arr = current( $arr );
                        $params['goods']['product_id'] = (int)$arr['product_id'];
                    }elseif(is_array($params['goods']['adjunct'][0]) && $params[3]) {
                        $params['goods']['product_id'] = ($params[2]=='false' ? 0 : (int)$params[2]);
                        $params['goods']['num'] = (int)$params[3];
                        $arr = $this->app->model('products')->getList( 'goods_id',array('product_id'=>(int)$params['goods']['product_id']),0,1 );
                        if(!empty($arr[0]['goods_id']))
                            $params['goods']['goods_id'] = (int)$arr[0]['goods_id'];
                    }else{
                        $msg = app::get('b2c')->_('参数错误！加入购物车失败！');
                        return false;
                    }
                }
            }

        return $params;
    }

    /**
     * 校验加入购物车数据是否符合要求-各种类型的数据的特殊性校验
     * @param array 加入购物车数据
     * @param string message 引用值
     * @return boolean true or false
     */
    public function check_object($arr_data,&$msg='')
    {
        if(!isset($arr_data[0]) || empty($arr_data[0])) {
            $msg = app::get('b2c')->_('购物车数据不存在！');
            return false;
        }
        return true;
    }

    /**
     * 检查库存
     * @param array 加入购物车的商品结构
     * @param array 现有购物车的数量
     * @param string message
     * @return boolean true or false
     */
    public function check_store($arr_data, $arr_carts, &$msg='',$_check_adjunct=true)
    {
        if(empty($arr_data) || !$arr_data['goods'] || !$arr_data['goods']['goods_id'] || !$arr_data['goods']['product_id']) trigger_error(app::get('b2c')->_("购物车操作失败"),E_USER_ERROR);
        if(intval($arr_data['goods']['num'])<1){
            $msg = '加入购物车数量错误！';
            return false;
        }
        $goods_id = $arr_data['goods']['goods_id'];
        $product_id = $arr_data['goods']['product_id'];
        /** 得到购物车中的所有数据 **/
        /*
        if ($arr_carts && $arr_carts[$product_id]['quantity'])
        {
            $aData['quantity'] = $this->omath->number_plus(array($arr_carts[$product_id]['quantity'],$aData['quantity']));
        }
         */

        $aData['quantity'] = $this->omath->number_plus($arr_data['goods']['num']);

        /** end **/
        $return_status = $this->_check_products_with_add($goods_id, $product_id, $aData['quantity']);
        if ($return_status['status'] == 'false'){
            $msg = $return_status['msg'];
            return false;
        }

        if($_check_adjunct) {
            $result = null;
            $flag = $this->_check_adjunct($aData, $goods_id,$result);
            if( !$flag ) {
                return $this->get_error_msg( '配件验证失败！' );
            } else {
                foreach( kernel::servicelist('spike_addtocart_check') as $object ) {
                    if( !is_object($object) ) continue;
                    $flag = $object->check( $goods_id,$arr_product['product_id'],($result[$arr_product['product_id']]?$result[$arr_product['product_id']]:$aData['quantity']),$msg );
                   // if( !$flag ) return $this->get_error_msg( $msg );
                    if(!$flag) return false;
                }
                //*/
            }
            return true;
        } else {
            return  true;
        }
    }

    /**
     *@todo
     *加入购物车时进行判断，返回组织好的已有的购物车信息，
     *此处是为改限时抢购加入购物车数量判断错误时改的，不知是否会影响其他地方，暂时修改
     *@author lujy
     */

    public function generate_cart_object_products($arr, &$goods_info){
        $goods_info[$arr['params']['product_id']] = $arr;
    }

    /**
     * 添加购物车项(goods)
     * @param array 需要保存的数据
     * @param string message
     * @return boolean
     */
    public function add_object($aSave, &$msg='', $append=true)
    {
        $aData = $aSave['goods'];
        // 商品在购物车中的标识
        $objIdent = $this->_generateIdent($aData);

        $aSave = array(
                   'obj_ident'    => $objIdent,
                   'member_ident' => $this->member_ident,
                   'obj_type'     => 'goods',
                   'params'       => $this->_generateParams($aData),
                   'quantity'     => floatval(($aData['num']?$aData['num']:1))
                 );



        //service 验证当前商品是否可加入购物车
        $arr_cart_goods = array();
        foreach( $this->getAll() as $val ) {
            if( $val['obj_type']=='goods' )
                $arr_cart_goods[] = $val;
        }
        $flag = $this->cart_add_service( $arr_cart_goods,$aSave,$msg );
        if( !$flag ) return false;


        if(true) {
            $this->no_database_cart_object[$aSave['obj_ident']] = $aSave;
            return $aSave['obj_ident']; //后台
        }

        // 追加|更新
        if($append) {
            // 如果存在相同商品 则追加
            $filter = array(
                        'obj_ident' => $aSave['obj_ident'],
                        'member_ident' => $this->member_ident,
            );

            if ($aData = $this->oCartObjects->getList('*', $filter, 0, -1, -1)){
                reset( $aData );
                $aData = current( $aData );
                $aSave['quantity'] += $aData['quantity'];
                if( is_array($aData['params']['adjunct']) ) {
                    foreach($aData['params']['adjunct'] as $g_id => $row) {
                        if(!isset($aSave['params']['adjunct'][$g_id])) {
                            $aSave['params']['adjunct'][$g_id] = $row;
                        } elseif ( isset($aSave['params']['adjunct'][$g_id]['adjunct']) && is_array($aSave['params']['adjunct'][$g_id]['adjunct']) ) {
                            foreach($aData['params']['adjunct'][$g_id]['adjunct'] as $p_id => $s_v) {
                                $aSave['params']['adjunct'][$g_id]['adjunct'][$p_id] += $s_v;
                            }
                        }
                    }
                }
            }
        }

        $is_save = $this->oCartObjects->save($aSave);
        if (!$is_save){
            $msg = app::get('b2c')->_('购物车保存失败！');
            return false;
        }
        return $aSave['obj_ident'];
    }

    /**
     * 校验订单货品的库存
     * @param string goods_id
     * @param string product_id
     * @param string quantity
     * @return boolean true or false
     */
    private function _check_products_with_add($goods_id, $product_id, $quantity=0)
    {
        if (!$goods_id || !$product_id || !$quantity) return array('status'=>'true','msg'=>'');;

        $oSG = $this->o_goods;
        if( !isset($this->check_goods_info[$goods_id]) )
            $this->check_goods_info[$goods_id] = $oSG->getList('goods_id, store,nostore_sell, marketable', array('goods_id'=> "$goods_id"));

        $aResult = $this->check_goods_info[$goods_id];

        $aGoods = $aResult[0];

        if($aGoods['marketable']=='false') return $this->get_error_msg( '商品未上架' );  //未上架

        //规格商品
        if(empty($product_id)) return array('status'=>'false','msg'=>'货品id为空！');;

        #if( !isset($this->check_products_info[$product_id]) )
            $this->check_products_info[$product_id] = $this->o_products->getList('product_id,goods_id, store, freez, marketable', array('product_id'=>"$product_id"));

        $aResult = $this->check_products_info[$product_id];
        if(!$aResult[0]) return $this->get_error_msg( '数据读取错误！货品' );
        $arr_product = $aResult[0];

        if($arr_product['marketable']=='false') return $this->get_error_msg( '该规格商品未上架！' );   //未上架
        $arr_product['store'] = ( $aGoods['nostore_sell'] ? $this->__max_goods_store : ( empty($arr_product['store']) ? ($arr_product['store']===0 ? 0 : $this->__max_goods_store) : $arr_product['store'] -$arr_product['freez']) );

        if ( !$aGoods['nostore_sell'] ) {
            if(empty($arr_product['store'])){
                if(isset($arr_product['store']) && $arr_product['store']!=='' ) return $this->get_error_msg( '该商品已无库存！' ); //库存0
            // 检测是否够库存
            } else if($quantity>$arr_product['store']) return $this->get_error_msg( '购买数量超出库存' );
        }
    }

    /*
     * 返回商品数量相关信息 包含 商品、配件
     * 用于其他app验证 : giftpackage
     */
    public function get_all_goods_real_store()
    {
        return $this->_products_store_array;
    }


    /**
     * 添加购物车项(goods)
     *
     * @param array $aData // array(
     *                          'goods_id'=>'xxxx',   // 商品编号
     *                          'product_id'=>'xxxx', // 货品编号
     *                          'adjunct'=>'xxxx',    // 配件信息
     *                          'num'=>'xxxx',   // 购买数量
     *                        )
     * @param boolean $append // 是否是追加
     * @return array //
     */
    private function _add($aSave,$append = true) {

        // 追加|更新
        if($append) {
            // 如果存在相同商品 则追加
            $filter = array(
                        'obj_ident' => $aSave['obj_ident'],
                        'member_ident' => $this->member_ident,
            );

            if ($aData = $this->oCartObjects->getList('*', $filter, 0, -1, -1)){
                reset( $aData );
                $aData = current( $aData );
                $aSave['quantity'] += $aData['quantity'];
                if( is_array($aData['params']['adjunct']) ) {
                    foreach($aData['params']['adjunct'] as $g_id => $row) {
                        if(!isset($aSave['params']['adjunct'][$g_id])) {
                            $aSave['params']['adjunct'][$g_id] = $row;
                        } elseif ( isset($aSave['params']['adjunct'][$g_id]['adjunct']) && is_array($aSave['params']['adjunct'][$g_id]['adjunct']) ) {
                            foreach($aData['params']['adjunct'][$g_id]['adjunct'] as $p_id => $s_v) {
                                $aSave['params']['adjunct'][$g_id]['adjunct'][$p_id] += $s_v;
                            }

                            #foreach($aSave['params']['adjunct'][$g_id]['adjunct'] as $p_id => &$s_v) {
                            #    $s_v += $aData['params']['adjunct'][$g_id]['adjunct'][$p_id];
                            #}
                        }
                    }
                }
            }
        }

        if( true!==($return=$this->_check($aSave)) ) {
            if( $append )
                return $return;
            else return false;
        }

        $this->oCartObjects->save($aSave);
        return $aSave;
    }


    public function no_database($status=false, $arr_goods=array(), $member_ident='') {
        $this->no_database = $status;
        $this->member_ident = $member_ident;
        return $this->set_cart_object($arr_goods);
    }

    public function get_cart_status() {
        return $this->no_database;
    }


    public function set_cart_object($aData = array()) {
        if(empty($aData) || !is_array($aData)) return false;
        foreach($aData as $key => $row) {
            if(!is_array($row))continue;
            foreach($row as $val) {
               $falg = kernel::single('b2c_mdl_cart_objects')->add_object($val, $key);
               if( !$falg ) {
                   $info = $this->app->model('products')->getList('*',array('product_id'=>$val['goods']['product_id']));
                   reset( $info );
                   $info = current($info);
                   return array('cart_status'=>'false','cart_error_html'=>$info['name'].$info['spec_info'] .' 商品数量不足或未上架 !');
               }
            }
        }
        return array('cart_status'=>'true');
        if(isset($aData['coupon']) && !empty($aData['coupon'])) {
            if(is_array($aData['coupon'])) {
                foreach($aData['coupon'] as $row) {
                    kernel::single("b2c_cart_object_coupon")->add(array($row));
                }
            } else {
                kernel::single("b2c_cart_object_coupon")->add($row);
            }
        }
    }


    /**
     * 购物车数据抛出
     * time:2010-11-30 17:51
     **/
    private function cart_add_service( $arr_cart_goods,$aSave=array(),&$error_msg ) {
        foreach( kernel::servicelist("b2c_cart_objects_goods.add") as $object ) {
            if( method_exists($object,'check') ) {
                $flag = $object->check( $arr_cart_goods,$aSave,$error_msg );
                if( !$flag ) {
                    $error_msg = $this->get_error_msg( $error_msg );
                    return false;
                }
            }
        }
        return true;
    }



    public function update($sIdent,$quantity) {
        #if(!floatval($quantity['quantity'])) {
            #$flag = $this->delete($sIdent);
            #return $flag;
        #}

        $aSave = array(
           'obj_ident'    => $sIdent,
           'member_ident' => $this->member_ident,
           'obj_type'     => 'goods',
         );

        $filter = array(
                    'obj_ident' => $sIdent,
                    'member_ident' => $this->member_ident,
                    'obj_type' => 'goods',
                    );
        $arr_cart_object_data = $this->oCartObjects->getList('*', $filter, 0, -1, -1);
        $arr_cart_object_data = $arr_cart_object_data[0];

        if(floatval($quantity['quantity'])) $aSave['quantity'] = floatval($quantity['quantity']);
        else $aSave['quantity'] = $arr_cart_object_data['quantity'];

        unset($quantity['quantity']);
        if( is_array($quantity) && !empty($arr_cart_object_data) && is_array($arr_cart_object_data) ) {
            $aSave['params'] = $arr_cart_object_data['params'];
            if($quantity['adjunct']) {
                foreach($quantity['adjunct'] as $group_id => $row) {
                    unset($arr_cart_object_data);
                    if( !isset($aSave['params']['adjunct'][$group_id]['adjunct']) || !is_array($aSave['params']['adjunct'][$group_id]['adjunct']) )continue;
                    foreach($aSave['params']['adjunct'][$group_id]['adjunct'] as $a_id => $a_num) {
                        if(!isset($row[$a_id]['quantity'])) {
                            #unset($aSave['params']['adjunct'][$group_id][$a_id]);
                            #unset($aSave['params']['adjunct'][$group_id]['adjunct'][$a_id]);
                            continue;
                        }
                        $aSave['params']['adjunct'][$group_id]['adjunct'][$a_id] = floatval($row[$a_id]['quantity']);

                    }
                }
            } else {
                //配件为一个时。删除
                #$aSave['params']['adjunct'] = '';
            }
        }

        return $this->_add($aSave,false);
    }

    /**
     * @param string 唯一标识ident
     * @param string type 删除什么部分
     * @param string quantity 数量
     * @param string message
     * @return true or false
     */
    public function remove_object_part($sIdent,$type='adjunct',$quantity,&$msg='') {
        $aSave = array(
           'obj_ident'    => $sIdent,
           'member_ident' => $this->member_ident,
           'obj_type'     => 'goods',
         );

        $filter = array(
                    'obj_ident' => $sIdent,
                    'member_ident' => $this->member_ident,
                    'obj_type' => 'goods',
                    );
        $arr_cart_object_data = $this->oCartObjects->getList('*', $filter, 0, -1, -1);
        $arr_cart_object_data = $arr_cart_object_data[0];

        if(floatval($quantity['quantity'])) $aSave['quantity'] = floatval($quantity['quantity']);
        unset($quantity['quantity']);

        if( is_array($quantity) && !empty($arr_cart_object_data) && is_array($arr_cart_object_data) ) {
            $aSave['params'] = $arr_cart_object_data['params'];
            if($quantity) {
                // 删除指定的quantity.
                foreach($quantity as $group_id => $row) {
                    unset($arr_cart_object_data);
                    if( !isset($aSave['params']['adjunct'][$group_id]['adjunct']) || !is_array($aSave['params']['adjunct'][$group_id]['adjunct']) )continue;
                    foreach($aSave['params']['adjunct'][$group_id]['adjunct'] as $a_id => $a_num) {
                        if(isset($row[$a_id]['quantity'])) {
                            #unset($aSave['params']['adjunct'][$group_id][$a_id]);
                            unset($aSave['params']['adjunct'][$group_id]['adjunct'][$a_id]);
                            continue;
                        }
                        //$aSave['params']['adjunct'][$group_id]['adjunct'][$a_id] = floatval($row[$a_id]['quantity']);
                    }
                    if (!$aSave['params']['adjunct'][$group_id]['adjunct']) $aSave['params']['adjunct'] = array();
                }
            } else {
                //配件为一个时。删除
                $aSave['params']['adjunct'] = '';
            }
        }
        return $this->_add($aSave,false);
    }

    /**
     * 指定的购物车商品项
     *
     * @param string $sIdent
     * @param boolean $rich        // 是否只取cart_objects中的数据 还是完整的sdf数据
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

    // 购物车里的所有商品项
    public function getAll($rich = false) {
        if(true)  {
            $aResult = $this->no_database_cart_object;
        } else {
            $aResult= $this->oCartObjects->getList('*',array(
                                               'obj_type' => 'goods',
                                               'member_ident'=> $this->member_ident,
                                            ));
        }


        if(empty($aResult)) {
            ob_start();
            $this->oCartObjects->_setCookie();
            ob_end_clean();
            return array();
        }
        $this->cart_add_service( $aResult,array(),$error_msg );
        if(!$rich) return $aResult;

        return $this->_get($aResult);
    }

    // 删除购物车中指定商品项
    public function delete($sIdent = null) {
        if(empty($sIdent)) return $this->deleteAll();
        return $this->oCartObjects->delete(array('member_ident'=>$this->member_ident, 'obj_ident'=>$sIdent, 'obj_type'=>'goods'));
    }

    // 清空购物车中商品项数据
    public function deleteAll() {
        return $this->oCartObjects->delete(array('member_ident'=>$this->member_ident, 'obj_type'=>'goods'));
    }

    // 统计购物车中商品项数据
    public function count(&$aData) {
        // 购物车中不存在goods商品
        if(empty($aData['object']['goods'])) return false;
        $aData['goods_min_buy'] = array();
        $aResult = array(
                      'subtotal_weight'=>0,
                      'subtotal'=>0,
                      'subtotal_price'=>0,
                      'subtotal_consume_score'=>0,
                      'subtotal_gain_score'=>0,
                      'discount_amount_prefilter'=>0,
                      'discount_amount_order'=>0,
                      'discount_amount'=>0,
                      'items_quantity'=>0,
                      'items_count'=>0,
                   );

        foreach($aData['object']['goods'] as &$row) {
            $this->_count($row);
            $aResult['subtotal_consume_score'] += $row['subtotal_consume_score'];
            $aResult['subtotal_gain_score'] += $this->omath->number_plus( array($row['subtotal_gain_score'],$row['sales_score_order']) );

            $aResult['subtotal'] += $row['subtotal'];
            $aResult['subtotal_price'] += $row['subtotal_price'];
            //if(!(isset($aData['is_free_shipping']) && $aData['is_free_shipping'])) { // 全场免运费
                $aResult['subtotal_weight'] += $row['subtotal_weight'];
            //}
            $aResult['discount_amount_prefilter'] += $row['discount_amount_prefilter'];
            #if( $row['subtotal'] < ($row['discount_amount_prefilter'] + $row['discount_amount_order']) )
            #    $row['discount_amount_order'] = $row['subtotal'] - $row['discount_amount_prefilter'];

            $aResult['discount_amount_order'] += $row['discount_amount_order'];
            $aResult['discount_amount'] += $row['discount_amount_cart'] ;
            $aResult['items_quantity'] += $row['quantity'] + $row['item_quantity_count'];
            $aResult['items_count']++;
            $aData['goods_min_buy'][$row['min_buy']['goods_id']]['info'] = $row['min_buy'];
            $aData['goods_min_buy'][$row['min_buy']['goods_id']]['real_quantity'] += $row['quantity'];
            if( $row['error_html'] ) $aResult['error_html'] = $row['error_html'];
            if($row['quantity'] > $row['store']['real']) {
                 $aData['cart_status'] = 'false';
                 $aData['cart_error_html'] = app::get('b2c')->_('库存错误！');
            }
        }


        foreach ($aData['goods_min_buy'] as $aGoodsMinBuy) {
            if($aGoodsMinBuy['info']['min_buy'] > $aGoodsMinBuy['real_quantity']) {
                $aData['cart_status'] = 'false';
                $aData['cart_error_html'] = app::get('b2c')->_('商品： '). $aGoodsMinBuy['info']['name'] .app::get('b2c')->_('数量未达起订量！起订量为：'). $aGoodsMinBuy['info']['min_buy'];
                break;
            }
        }

        return $aResult;
    }

    /**
     * 单件
     *
     * @param array $aData
     */
    private function _count(&$aData) {
        // 重新统计时将以下值 置为0
        $aData['subtotal_consume_score'] = 0;
        $aData['subtotal_gain_score'] = 0;
        $aData['subtotal'] = 0;  //会员价总额
        $aData['subtotal_prefilter_after'] = 0;  //会员价总额
        $aData['subtotal_price'] = 0; //商品原始价格
        $aData['subtotal_weight'] = 0;
        $aData['discount_amount'] = 0;
        $aData['discount_amount_prefilter'] = 0; //预过滤优惠
        $aData['item_quantity_count'] = 0;

        foreach($aData['obj_items']['products'] as $key=>&$row) {
            $temp = array('goods_id'=>$row['goods_id'], 'min_buy'=>$row['min_buy'], 'name'=>$row['name']);
            $aData['min_buy'] = $temp;  //起订量
            if($key != 0) break;
            $aResult = $this->_count_product($row);

            $aData['obj_items']['products'][$key]['subtotal'] =  $this->omath->number_multiple( array($aResult['subtotal'],$aData['quantity']) );

            $aData['subtotal_consume_score'] += $aResult['subtotal_consume_score'];
            $aData['subtotal_gain_score'] += $this->omath->number_plus( array($aData['sales_score'],$aResult['subtotal_gain_score']) );
            $aData['subtotal'] +=  $this->omath->number_multiple( array($aResult['subtotal'],$aData['quantity']) );
            $aData['subtotal_price'] +=  $this->omath->number_multiple( array($aResult['subtotal_price'],$aData['quantity']) );
            $aData['subtotal_weight'] += $aResult['subtotal_weight'];
            $aData['discount_amount_prefilter'] += $this->omath->number_minus( array($aResult['subtotal'],$aResult['subtotal_current']) );

            //商品促销之后的商品总价
            $aData['subtotal_prefilter_after'] = $this->omath->number_multiple( array($aResult['subtotal_current'],$aData['quantity']) );
        }


        // 数量
        $aData['subtotal_consume_score'] = $this->omath->number_multiple( array($aData['subtotal_consume_score'],$aData['quantity']) );
        $aData['subtotal_gain_score'] = $this->omath->number_multiple( array($aData['subtotal_gain_score'],$aData['quantity']) );
        //$aData['subtotal'] = $aData['quantity'];
        #if(!(isset($aData['is_free_shipping']) && $aData['is_free_shipping'])) { // 对指定的商品免运费
            $aData['subtotal_weight'] = $this->omath->number_multiple( array($aData['subtotal_weight'],$aData['quantity']) );
        #}


        if( isset($aData['adjunct']) && is_array($aData['adjunct']) && !empty($aData['adjunct']) ) {
            foreach($aData['adjunct'] as $vkey => &$_adjunct_row) {
                $this->getScroe($_adjunct_row);
                $aData['adjunct'][$vkey]['subtotal'] = $this->omath->number_multiple( array($_adjunct_row['price']['buy_price'],$_adjunct_row['quantity']) );
                $aData['subtotal'] += $this->omath->number_multiple( array($_adjunct_row['price']['buy_price'],$_adjunct_row['quantity']) );
                $aData['adjunct'][$vkey]['subtotal_price'] = $this->omath->number_multiple( array($_adjunct_row['price']['price'],$_adjunct_row['quantity']) );
                $aData['subtotal_price'] += $this->omath->number_multiple( array($aData['adjunct'][$vkey]['subtotal_price'],$_adjunct_row['quantity']) );
                $aData['subtotal_gain_score'] += $this->omath->number_multiple( array($_adjunct_row['gain_score'],$_adjunct_row['quantity']) );
                $aData['subtotal_weight'] += $this->omath->number_multiple( array($_adjunct_row['weight'],$_adjunct_row['quantity']) );
                $aData['item_quantity_count'] +=$_adjunct_row['quantity'];
            }
        }

        //暂时写死
        if( isset($aData['gift']) && is_array($aData['gift']) ) {
            foreach( $aData['gift'] as $_gift ) {
                #$aData['item_quantity_count'] +=$_gift['quantity'];
            }
        }


        $aData['discount_amount_prefilter'] = $this->omath->number_multiple( array($aData['discount_amount_prefilter'],$aData['quantity']) );
    }

    private function _count_product(&$row){
        #$this->getScroe($row);
        $aResult = array(
                      'subtotal_weight'=>0,
                      'subtotal'=>0,
                      'subtotal_price'=>0,
                      'subtotal_consume_score'=>0,
                      'subtotal_gain_score'=>0,
                      'subtotal_current'=>0,
               );
        $aResult['subtotal_weight'] += $this->omath->number_multiple( array($row['weight'],$row['quantity']) );
        $aResult['subtotal'] = $this->omath->number_plus( array($aResult['subtotal'],$row['price']['member_lv_price']) );// * $row['quantity']; // 按商品价格
        $aResult['subtotal_price'] = $this->omath->number_plus( array($aResult['subtotal_price'],$row['price']['price']) );// * $row['quantity']; // 按商品价格
        $aResult['subtotal_consume_score'] += $this->omath->number_multiple( array($row['consume_score'],$row['quantity']) );
        $aResult['subtotal_gain_score'] = $row['gain_score']; //* $row['quantity'];

        $aResult['subtotal_current'] = $this->omath->number_plus( array($aResult['subtotal_current'],$row['price']['buy_price']) ); // 按实际购买价格
        return $aResult;
    }

    // todo 商品添加到购物车中的数据检测在这里处理
    // 商品的上下架 库存
    private function _check($aData, $_check_adjunct=true) {
        if(empty($aData)) return array('status'=>'false','msg'=>'购物车操作失败');

        // 验证商品的正确性
        $obj_ident = $aData['obj_ident'];
        if(empty($obj_ident) || is_array($obj_ident)) return $this->get_error_msg( '参数错误' );

        //商品 是否下架 是否删除
        $oSG = $this->o_goods;
        $arr_goods_info = $this->getIdFromIdent($obj_ident);
        $goods_id = $arr_goods_info['goods_id'];
        $product_id = $arr_goods_info['product_id'];
        if( !isset($this->check_goods_info[$goods_id]) )
            $this->check_goods_info[$goods_id] = $oSG->getList('goods_id, store,nostore_sell, marketable', array('goods_id'=> "$goods_id"));

        $aResult = $this->check_goods_info[$goods_id];

        $aGoods = $aResult[0];

        if($aGoods['marketable']=='false') return $this->get_error_msg( '商品未上架' );  //未上架

        //规格商品
        $params = is_array($aData['params']) ? $aData['params'] : @unserialize($aData['params']);

        if($params['product_id']) {
            $product_id = $params['product_id'];
        }
        if(empty($product_id)) return array('status'=>'false','msg'=>'货品id为空！');

        #if( !isset($this->check_products_info[$product_id]) )
            $this->check_products_info[$product_id] = $this->o_products->getList('product_id,goods_id, store, freez, marketable', array('product_id'=>"$product_id"));

        $aResult = $this->check_products_info[$product_id];


        if(!$aResult[0]) return $this->get_error_msg( '数据读取错误！货品' );
        $arr_product = $aResult[0];

        if($arr_product['marketable']=='false') return $this->get_error_msg( '该规格商品未上架！' );   //未上架
        $arr_product['store'] = ( $aGoods['nostore_sell'] ? $this->__max_goods_store : ( empty($arr_product['store']) ? ($arr_product['store']===0 ? 0 : $this->__max_goods_store) : $arr_product['store'] -$arr_product['freez']) );

        if ( !$aGoods['nostore_sell'] ) {
            if(empty($arr_product['store'])){
                if(isset($arr_product['store']) && $arr_product['store']!=='' ) return $this->get_error_msg( '该商品已无库存！' ); //库存0
            // 检测是否够库存
            } else if($aData['quantity']>$arr_product['store']) return $this->get_error_msg( '购买数量超出库存' );

        }

        if($_check_adjunct) {
            $result = null;
            $flag = $this->_check_adjunct($aData, $goods_id,$result);
            if( !$flag ) {
                return $this->get_error_msg( '配件验证失败！' );
            } else {
                foreach( kernel::servicelist('spike_addtocart_check') as $object ) {
                    if( !is_object($object) ) continue;
                    $flag = $object->check( $goods_id,$arr_product['product_id'],($result[$arr_product['product_id']]?$result[$arr_product['product_id']]:$aData['quantity']),$msg );
                    if( !$flag ) return $this->get_error_msg( $msg );
                }
                //*/
            }
            return true;
        } else {
            return  true;
        }
    }


    //验证库存、是否上架商品
    protected function _check_goods( &$aData, $arr_goods_id ) {
        if( empty($arr_goods_id) ) return ;

        $arr = $this->o_goods->getList('goods_id, store,nostore_sell, marketable', array('goods_id'=> $arr_goods_id));

        foreach($arr as $row) {
            $this->check_goods_info[$row['goods_id']] = $row;
            $key = array_search( $row['goods_id'], $arr_goods_id );
            if( $row['marketable']=='false' ) unset($aData[$key]);
            if( $row['nostore_sell'] )
                $this->nostore_sell[$row['goods_id']] = true;
        }
    }


    //验证库存、是否上架货品
    protected function _check_products( &$aData, $arr_products_id, $cur_type='' ) {
        if( empty($arr_products_id) ) return ;

        $arr = $this->o_products->getList('product_id,goods_id, store,freez,marketable', array('product_id'=>$arr_products_id));

        foreach($arr as $row) {
            $key = array_search( $row['product_id'], $arr_products_id );
            if( $row['marketable']=='false' ) unset($aData[$key]);
            if( !$this->nostore_sell[$row['goods_id']] ) {
                foreach( kernel::servicelist('spike_addtocart_check') as $object ) {
                    if( !is_object($object) ) continue;
                    if(!method_exists($object, 'get_type')) {
                        $obj_type = "goods";
                    }
                    else {
                        $obj_type = $object->get_type();
                    }
                    if($obj_type != $cur_type) continue;
                    $flag = $object->check( $row['goods_id'],$row['product_id'],$aData[$key]['quantity'],$msg );
                    if( !$flag ) $aData[$key]['error_html'] = $msg;
                }

                if( empty($row['store']) || $row['store']==0 || 0>=$row['store']-$row['freez'] ){
                    if( $row['store']!==null && $row['store']!=='' ) unset($aData[$key]); //库存0
                    else $row['store'] = $this->__max_goods_store;
                } else if($aData[$key]['quantity']>$row['store']-$row['freez']) {
                    $aData[$key]['quantity'] = $row['store'] - $row['freez'];
                    $arr_save = array('obj_ident'=>$aData[$key]['obj_ident'],'quantity'=>$row['store']-$row['freez']);
                    $this->app->model('cart_objects')->save( $arr_save );
                }
                //原始删除unset   bug：商品数量为2 加入购物车   后台修改商品数量为1 购物车中消失。然而该商品无法加入 |||||改为修改数量
            }
        }
    }

    private function _check_adjunct( $aData=array(), $goods_id, &$result ) {
            //获取商品配件

            $arr_goods_info = $this->_get_adjuncts($goods_id);

            $arr_cart_object = $this->oCartObjects->getList('*',array(
                                                       'obj_type' => 'goods',
                                                       'member_ident'=> $this->member_ident,
                                                    ));
            $tmp_products_store = array();

            if( !$arr_cart_object ) {
                $arr_cart_object[] = $aData;
            }

            foreach( $arr_cart_object as $arr ) {
                if( $aData['obj_ident']==$arr['obj_ident'] ) $arr = $aData;
                $tmp_products_store[$arr['params']['product_id']] += $arr['quantity'];
                if( isset($arr['params']['adjunct']) && !empty($arr['params']['adjunct']) && is_array($arr['params']['adjunct']) ) {
                    foreach( $arr['params']['adjunct'] as $adjuncts ) {
                        if( isset($adjuncts['adjunct']) && !empty($adjuncts['adjunct']) && is_array($adjuncts['adjunct']) ) {
                            foreach( $adjuncts['adjunct'] as $p_id => $quantity ) {
                                $tmp_products_store[$p_id] += $quantity;
                            }
                        }
                    }
                }
            }

            $arr_p_id = array($aData['params']['product_id']);
            foreach((array)$aData['params']['adjunct'] as $row) {
                if( !isset($row['adjunct']) || !is_array($row['adjunct']) ) continue;
                foreach( $row['adjunct'] as $p_id => $quantity ) {
                    if( !$arr_goods_info ) continue;

                    if( false===array_search($p_id, (array)$arr_goods_info['value'][$row['group_id']]['items']['product_id']) ) {
                        unset( $row['adjunct'][$p_id] );
                        continue;
                    }

                    //配件限购数量不填时  不限制
                    $diff_num = $arr_goods_info['value'][$row['group_id']]['max_num'] ? $arr_goods_info['value'][$row['group_id']]['max_num'] : $this->__max_goods_store;
                    if( $quantity<$arr_goods_info['value'][$row['group_id']]['min_num'] ) return false;
                    if( $quantity>$diff_num ) return false;
                    $arr_p_id[] = $p_id;
                }
            }

            if( $arr_p_id ) {
                $arr_g_id = array();
                $arr_tmp_store = $this->o_products->getList('product_id,goods_id, store, marketable', array('product_id'=>$arr_p_id) );

                foreach( $arr_tmp_store as $row ) {
                    if( empty($row['goods_id']) ) return false;
                    /** 验证货品是否下架 **/
                    if ( $row['marketable'] == 'false' ) return false;
                    $this->check_products_info[$row['product_id']] = $row;

                    $arr_tmp_goods = $this->o_goods->getList('goods_id, store,nostore_sell, marketable', array('goods_id'=> $row['goods_id']));
                    reset( $arr_tmp_goods );
                    $arr_tmp_goods = current( $arr_tmp_goods );
                    /** 验证商品是否下架 **/
                    if ( $arr_tmp_goods['marketable'] == 'false' ) return false;
                    $this->check_goods_info[$row['goods_id']] = $arr_tmp_goods;
                    if( empty($arr_tmp_goods['nostore_sell']) ) {
                        if( $this->check_products_info[$row['product_id']]['store']<$tmp_products_store[$row['product_id']] && $arr_tmp_goods['store']!==null ) return false;
                    }
                }
                $result = $tmp_products_store;
            }
        return true;
    }

    private function _generateIdent($aData) {

        $adjunct = array();
        if($aData['adjunct']) {
            if(is_array($aData['adjunct'])) {
                foreach($aData['adjunct'] as $val) {
                    if(is_array($val)) {
                        foreach($val as $ap_id => $a_quantity) {
                            $adjunct[$ap_id] = $a_quantity;
                        }
                    } else {
                        $adjunct[] = $val;
                    }
                }
            } else {
                $adjunct[] = $aData['adjunct'];
            }
        } else {
            $adjunct[] = 'na';
        }
        $stradj = array();
        foreach($adjunct as  $key => $val) {
            $adj[] = $key.'('.$val.')';
        }


        return "goods_".$aData['goods_id']."_".$aData['product_id'];# .'_'. ( $this->arr_member_info['member_id'] ? $this->arr_member_info['member_id'] : 0 );//."_".implode('_', $adj);
    }



    private function getIdFromIdent($ident='') {
        if(!$ident) return false;
        $temp = explode('_', $ident);
        return array('goods_id'=>$temp[1], 'product_id'=>$temp[2]);
    }


    /**
     * Enter description here...
     *
     * @param array $aData // as add
     * @return array
     */
    private function _generateParams($aData) {
        $adj_items = array();
        if($aData['adjunct'] && $aData['adjunct'] != 'na'){
            if(is_array($aData['adjunct'])) {
                foreach($aData['adjunct'] as $group_id => $adjunct) {
                    $adj_items[$group_id] = array('group_id'=>$group_id, 'adjunct'=>$adjunct);
                }
            }

        }
        return  array(
                    'goods_id' => $aData['goods_id'],
                    'product_id' => $aData['product_id'],
                    'adjunct' => $adj_items,
                    'extends_params' => $aData['extends_params'],
                );
    }

    /**
     *
     *
     * @param array $aData // dbscheme/cart_objects * N
     * @return array
     */
    private function _get($aData) {
        $aInfo = $this->_get_basic($aData);
        $aProductId = $aInfo['productid'];
        $aAdjunctId  = $aInfo['adjunctid'];
        $products_store = $tmp_products_store = array();

        $aProducts = $this->_get_products($aProductId);

        $arr_goods = $arr_products = array();
        foreach( $aData as $key => $row ) {
            //商品不存在时删除购物车内信息
            if(empty($aProducts[$row['obj_items']['products'][0]])) {
                unset($aData[$key]);continue;
            }
            $arr_goods[$key] = $row['params']['goods_id'];
            $arr_products[$key] = $row['params']['product_id'];
        }
        $this->_check_goods($aData, $arr_goods);
        $this->_check_products($aData, $arr_products, $this->get_type());

        $arr_products = array();
        $oGoods = $this->app->model('goods');
        foreach($aData as $key => &$row) {

            // obj_items 第一个是货品信息
            $arr_product_info = $aProducts[$row['obj_items']['products'][0]];

            $goods_id = $row['params']['goods_id'];
            $store_id = $oGoods->getList('store_id,freight_bear',array('goods_id'=>$goods_id));
            $aData[$key]['store_id'] = $store_id[0]['store_id'];
            $aData[$key]['freight_bear'] = $store_id[0]['freight_bear'];

            $aData[$key]['obj_items']['products'][0] = $arr_product_info;
            if( !$arr_product_info['floatstore'] ) $row['quantity'] = (int)$row['quantity'];
            $product_id = $row['obj_items']['products'][0]['product_id'];

            if(isset($tmp_products_store[$product_id])) {
                $tmp_products_store[$product_id]['less'] += $row['quantity'];
                $tmp_products_store[$product_id]['quantity'] += $row['quantity'];
            } else {
                $tmp_store = array(
                    'quantity' => $row['quantity'],
                    'store'    => $row['obj_items']['products'][0]['store'],
                    'product_id' => $row['obj_items']['products'][0]['product_id'],
                    'obj_ident' => $row['obj_ident'],
                    'less'      => $row['quantity'],
                    'name'      =>  $row['obj_items']['products'][0]['new_name'],
                );
                $tmp_products_store[$product_id] = $tmp_store;
            }

            // 有配件将配置加入到['obj_items']['products']中
            $tmp_adjunct_name = array();
            $row['adjunct'] = array();

            if(isset($row['params']['adjunct']) && !empty($row['params']['adjunct'])) {
                foreach($row['params']['adjunct'] as &$adjunct) {

                    if(is_array($adjunct['adjunct'])) {
                        foreach($adjunct['adjunct'] as $key => $quantity) {
                            $tmp_adjunct_arr = null;
                            if(isset($arr_products[$key]) && !empty($arr_products[$key])) {
                                $tmp_adjunct_arr = $arr_products[$key];
                            } else {
                                 $tmp_tt = $this->get_product_adjunct($key, $adjunct, $quantity, $tmp_adjunct_name, $row, $tmp_products_store);
                                 if(empty($tmp_tt))  unset($$adjunct['adjunct'][$key]);
                                 $tmp_adjunct_arr = $tmp_tt;
                                 $tmp_tt = null;
                            }
                            if($tmp_adjunct_arr) {
                                $tmp_adjunct_arr['store'] = &$products_store[$key][$product_id][$adjunct['group_id']]['store'];
                                $tmp_adjunct_arr['group_id'] = $adjunct['group_id'];

                                $row['adjunct'][] = $tmp_adjunct_arr;
                            }
                        }
                    }
                }
            //$row['store'] = &$products_store[$row['obj_ident']]['store'];
            }
            $row['store'] = &$products_store[$product_id]['store'];
            if( empty($row['adjunct']) ) unset($row['adjunct']);
        }


        $this->get_products_real_store($tmp_products_store, $products_store);

        //所有商品数量数组
        $this->_products_store_array = $products_store;

        return $aData;
    }



    public function get_meta($group_id=null, $goods_id=0) {
        if( $group_id===null || empty($goods_id) ) return false;  // 配件信息（购物车中） 配件分组id

        $arr_adjunct = $this->app->model('goods')->getCartAdjunct( $params,$goods_id );
        if( !is_int($group_id) ) {
            foreach( (array)$arr_adjunct as $row ) {
                $row['setting']['items'] = array();
                $row['setting']['items']['product_id'] = $row['product_id'];

                $a['value'][] = $row['setting'];
                $a['pk'] = $goods_id;
            }
            $return = array( $a );
        } else {
            $arr_adjunct[$group_id]['setting']['items']['product_id'] = $arr_adjunct[$group_id]['product_id'];
            $return = $arr_adjunct[$group_id]['setting'];
        }
        return $return;

        #echo "<pre>";print_r($return);exit;

        //////////////////////////////////////////////////////////////////////////
        // 遗弃
        ///////////////////////////////////////////////////////////////////////////
        //取得配件
        $arr = app::get('dbeav')->model('meta_register')->getList('mr_id, col_type', array('pk_name'=>'goods_id', 'col_name'=>'adjunct'));
        if( empty($arr) || !isset($arr[0]['col_type']) || empty($arr[0]['col_type']) || !isset($arr[0]['mr_id']) || empty($arr[0]['mr_id']) ) return false;

        $arr_meta_data = app::get('dbeav')->model('meta_value_'.$arr[0]['col_type'])->select($arr[0]['mr_id'], array($goods_id));
        $arr_adjunct_to_goods = is_array($arr_meta_data[$goods_id]['adjunct']) ? $arr_meta_data[$goods_id]['adjunct'] : unserialize($arr_meta_data[$goods_id]['adjunct']);
        $arr = $arr_meta_data = null;

        #echo "<pre>";print_r($arr_adjunct_to_goods);exit;

        if( !is_int($group_id) ) {
            $a = $arr_adjunct_to_goods;
            $arr_adjunct_to_goods = array();
            $arr_adjunct_to_goods[0]['value'] = $a;
            $arr_adjunct_to_goods[0]['pk'] = $goods_id;
            #echo "<pre>";print_r($arr_adjunct_to_goods);exit;
            return $arr_adjunct_to_goods;
        } else {
            #echo "<pre>";print_r($arr_adjunct_to_goods[$group_id]);exit;
            return $arr_adjunct_to_goods[$group_id];
        }
    }

    private function get_products_real_store(&$tmp, &$products_store) {
         foreach($tmp as $val) {

            if(isset($val['adjunct_to_goods']) && is_array($val['adjunct_to_goods'])) {
                foreach($val['adjunct_to_goods'] as $p_id => $arr_val) {
                    foreach($arr_val as $g_id => $s_v) {

                        $t_t_store = $val['store'] - $val['less'] + $s_v['quantity'];
                        $products_store[$val['product_id']][$p_id][$g_id]['store'] = array(
                                                                                            'real' => ($t_t_store>$s_v['adjunct']['max_num']) ? ($s_v['adjunct']['max_num']?$s_v['adjunct']['max_num']:$val['store']) : ($t_t_store),
                                                                                            'less' => $val['less'],
                                                                                            'store' => $val['store'],
                                                                                            'name' => $val['name'],
                                                                                        );
                    }
                }
            }

            $products_store[$val['product_id']]['store'] = array(
                                                    'real' => $val['store'] - $val['less'] + $val['quantity'],
                                                    'less' => $val['less'],
                                                    'store' => $val['store'],
                                                    'name' => $val['name'],
                                                );
        }

    }

    private function get_product_adjunct($pid=0, $adjunct=array(), $quantity=0, &$tmp_adjunct_name=array(), &$row, &$tmp_products_store) {

        if( empty($pid) || empty($adjunct) || empty($quantity) ) return false;

        $info = $adjunct['info'];

        if( empty($info) || !isset($adjunct['group_id']) ) return false;

        $group_id = $adjunct['group_id'];

        $tmp = $this->_get_products(array($pid));
        if(empty($tmp)) return false;
        if( !isset($tmp[$pid]) ) return false;


        $tmp = $tmp[$pid];

        if( $info['set_price']=='minus' ) { //minus为减钱 discount为打折
            $tmp['price']['price'] -= $info['price'];
            $tmp['price']['buy_price'] -= $info['price'];
            if( $tmp['price']['price']<0 ) $tmp['price']['price'] = 0;
            if( $tmp['price']['buy_price']<0 ) $tmp['price']['buy_price']=0;
        } else {
            if( $info['price'] ) {
                $tmp['price']['price'] *= $info['price'];
                $tmp['price']['buy_price'] *= $info['price'];
            }
        }
        $tmp['quantity'] = $quantity;
        $tmp_adjunct_name[] = $tmp['new_name'];


        $tmp_store = array(
                        'product_id' => $pid,
                        'store' => $tmp['store'],
                        'less'  => $tmp_products_store[$pid]['less'] + $quantity,
                        'quantity' => &$tmp_products_store[$pid]['quantity'],
                        'goods_quantity' => $row['quantity'],
                        'name' => $tmp['new_name'],
                        'adjunct' => array('max_num' => $info['max_num']),
                    );
        $tmp_adjunct_to_goods = array(
                                        'goods_quantity' => $row['quantity'],
                                        'quantity' => $quantity,
                                        'product_id' => $row['obj_items']['products'][0]['product_id'],
                                        'store'    => $row['obj_items']['products'][0]['store'],
                                        'obj_ident' => $row['obj_ident'],
                                        'name' => &$row['obj_items']['products'][0]['new_name'] ,
                                        'adjunct' => array('max_num' => $info['max_num']),
                                    );
        if($tmp_products_store[$pid]['adjunct_to_goods']) {
            $tmp_store['adjunct_to_goods'] = $tmp_products_store[$pid]['adjunct_to_goods'];
        }

        $tmp_store['adjunct_to_goods'][$row['obj_items']['products'][0]['product_id']][$group_id] = $tmp_adjunct_to_goods;


        $tmp_products_store[$pid] = $tmp_store;


        return $tmp;
    }

    private function _get_adjuncts($goods_id, $all=false) {
        if(empty($goods_id)) return false;

        if( is_array($goods_id) ) {
            foreach( $goods_id as $val ) {
                $aInfo = array_merge( (array)$aInfo,(array)$this->get_meta( true,$val ) );
            }
        } else {
            $aInfo = $this->get_meta( true,$goods_id );
        }
        if(empty($aInfo)) return ;
        foreach($aInfo as $key => $row) {
            if( !is_array($row) )continue;
            $aInfo[$key]['value'] = is_array($row['value']) ? $row['value'] : @unserialize($row['value']);
        }


        return $all ? $aInfo : $aInfo[0];
    }



    private function _get_basic(&$aData) {

        $aResult = array();
        $arr_adjunct_info_goods = $aProductId = array();

        foreach( $aData as $row ) {
            $arr_goods_id[] = $row['params']['goods_id'];
        }

        $arr = $this->_get_adjuncts($arr_goods_id, true);

        if( is_array($arr) ) {
            foreach( $arr as $row ) {
                $arr_adjunct_info_goods[$row['pk']] = $row;
            }
        }

        foreach($aData as $row) {
            if( !$this->_check( $row ) ) continue;
            if($row['params']['product_id']) {
                $aProductId[] = $row['params']['product_id'];
            }

            $adjunct_info_goods = $arr_adjunct_info_goods[$row['params']['goods_id']];
            if($row['params']['adjunct']){
                foreach($row['params']['adjunct'] as &$_adjunct){
                    if(!$_adjunct['adjunct'])continue;
                    $_adjunct['info'] = $adjunct_info_goods['value'][$_adjunct['group_id']];
                    if(is_array($_adjunct['adjunct'])) {
                        foreach($_adjunct['adjunct'] as $key => $val) {

                            $true = false;
                            foreach( (array)$adjunct_info_goods['value'] as $tmp_adjunct_value ) {
                                if( false!==array_search($key,(array)$tmp_adjunct_value['items']['product_id']) ) {
                                    $true = true;break;
                                }
                            }
                            if( $true ) {
                                $aDjunct[$row['obj_ident']][$key] = $key;
                            } else {
                                unset($_adjunct['adjunct'][$key] );
                            }
                        }
                    } else {
                        //$aDjunct[$row['obj_ident']][$_adjunct['adjunct']] = $_adjunct['adjunct'];
                    }
                }
            }

            $aResult[] = array(
                            'obj_ident' => $row['obj_ident'],
                            'obj_type' => 'goods',
                            'obj_items' => array(
                                              'products' => array($row['params']['product_id']),
                                           ),
                            'quantity' => $row['quantity'],
                            'params' => $row['params'],
                            'subtotal_consume_score' => 0,
                            'subtotal_gain_score' => 0,
                            'subtotal' => 0,
                            'subtotal_price' => 0,
                            'subtotal_weight' => 0,
                            'discount_amount' => 0,
                            'adjunct' => $row['params']['adjunct'],
                        );
        }
        // 将整理好的数据格式用引用带出
        $aData = $aResult;
        return array('adjunctid'=>$aDjunct, 'productid'=>array_unique($aProductId));
        //return array_unique($aProductId);
    }

    function _get_products($aProductId) {
        $imageDefault = app::get('image')->getConf('image.set');
        $json = kernel::single('b2c_cart_json');
        $router = app::get('site')->router();
        if(empty($aProductId)) return array();
        //防sql注入处理
        foreach( $aProductId as $k=>$id ){
            $aProductId[$k] = (int)$id;
          }
        $aProductId = array_unique($aProductId);
        ///////////////// 货品信息 ///////////////////////
        $sSql = "SELECT
                     p.product_id,p.goods_id,p.bn,g.score_setting,g.score as gain_score,p.cost,p.name, p.store, p.marketable, g.params, g.package_scale, g.package_unit, g.package_use, p.freez,
                     g.goods_type, g.nostore_sell, g.min_buy,g.type_id,g.cat_id,g.image_default_id,p.spec_info,p.spec_desc,p.price,p.weight,p.mktprice as p_mktprice,g.mktprice as g_mktprice,g.act_type,
                     t.setting, t.floatstore
                 FROM  sdb_b2c_products AS p
                 LEFT JOIN  sdb_b2c_goods AS g    ON p.goods_id = g.goods_id
                 LEFT JOIN sdb_b2c_goods_type AS t ON g.type_id  = t.type_id
                 WHERE product_id IN (".implode(',',$aProductId).")";

       $aProduct = $this->oCartObjects->db->select($sSql);

       ////////// 设置了的会员价 //////////////////////////
       $sSql = "SELECT p.product_id,p.price
                FROM sdb_b2c_goods_lv_price AS p
                LEFT JOIN sdb_b2c_member_lv AS lv ON p.level_id = lv.member_lv_id
                WHERE p.level_id=".(intval($this->arr_member_info['member_lv']))." AND p.product_id IN (".implode(',',$aProductId).")";

       $aPrice = $this->oCartObjects->db->select($sSql);
       $tmp = array();
       foreach($aPrice as $val) {
           $tmp[$val['product_id']] = $val;
       }
       $aPrice = $tmp;
       $tmp = null;
       $aPrice = empty($aPrice)? array() : utils::array_change_key($aPrice,'product_id');

       //////////// 获取会员折扣 //////////////////////////
       //empty($this->arr_member_info)
       if( empty($this->arr_member_info) ) { // 非登录用户
           $discout = 1;
       } else {// 登录用户
           $discout = $this->discout;
       }

       //////////// 整理数据 /////////////////////////////
       $aResult = array();
       foreach($aProduct as $row) {
           //$products_store[$row['product_id']]['store'] = $row['store'];
           if($row['marketable']=='false') {  //商品下架购物车中消失处理！
               unset($row);continue;
           }

           //修改秒杀价格
           if($row['act_type'] == 'spike'){
                $groupObj = app::get('spike')->model('spikeapply');
                $aid = $groupObj->getOnActIdByGoodsId($row['goods_id']);

                if($aid){
                    $actInfo = $groupObj->loadActInfoById($aid);
                }
                $row['price'] = $actInfo['last_price'];
           }


           //商品不存在时购物车里也同时删除
           $key = array_search($row['product_id'], $aProductId);
           if($key===false) $arrDelGoods[] = $aProductId[$key];
           //商品不存在时购物车里也同时删除

           if($row['score_setting'] == 'percent'){
                $point_money_value = $this->app->getConf('site.point_money_value');
                if($point_money_value == ''){
                    $point_money_value = 1;
                }
                $row['gain_score'] = $row['price'] * ($row['gain_score']/100) * $point_money_value;
           }

           $aResult[$row['product_id']] = array(
                    'bn' => $row['bn'],
                    'price' => array(
                                'price' => $row['price'],
                                'cost' => $row['cost'],
                                'member_lv_price' => empty($aPrice[$row['product_id']]) ? $this->omath->number_multiple(array($row['price'],$discout) ) : $aPrice[$row['product_id']]['price'],
                                //'buy_price' => empty($aPrice[$row['product_id']])? ($row['price'] * $discout) : $aPrice[$row['product_id']]['price'] * $discout,
                                'buy_price' => empty($aPrice[$row['product_id']]) ? $this->omath->number_multiple( array($row['price'],$discout) ) : $aPrice[$row['product_id']]['price'],
                                'mktprice' => empty($row['p_mktprice'])?$row['g_mktprice']:$row['p_mktprice']//市场价 
                              ),
                    'product_id' => $row['product_id'],
                    'goods_id' => $row['goods_id'],
                    'goods_type' => $row['goods_type'],
                    'name'=> $row['name'],
                    'consume_score' => 0,
                    'gain_score' => intval($row['gain_score']),
                    'type_setting' => is_array($row['setting']) ? $row['setting'] : @unserialize($row['setting']),
                    'type_id' => $row['type_id'],
                    'cat_id' => $row['cat_id'],
                    'min_buy' => $row['min_buy'],
                    'spec_info' => $row['spec_info'],
                    'spec_desc' => is_array($row['spec_desc']) ? $row['spec_desc'] : @unserialize($row['spec_desc']),
                    'weight' => $row['weight'],
                    'quantity' => 1,
                    'params' => is_array($row['params']) ? $row['params'] : @unserialize($row['params']),
                    'floatstore' => $row['floatstore'] ? $row['floatstore'] : 0,
                    'store'=> ( $row['nostore_sell'] ? $this->__max_goods_store : ( empty($row['store']) ? (((int)$row['store']===0 && $row['store']!==null && $row['store']!=='')? 0 : $this->__max_goods_store) : $row['store'] -$row['freez']) ),
                    'package_scale' => $row['package_scale'],
                    'package_unit' => $row['package_unit'],
                    'package_use' => $row['package_use'],
                    'default_image' => array(
                                        'thumbnail' => $row['image_default_id'] ? $row['image_default_id'] : $imageDefault['M']['default_image'],
                                      ),
           );
           //组合JSON格式让JS显示
           $aResult[$row['product_id']]['json_price']['price'] = $json->get_cur_order($aResult[$row['product_id']]['price']['price']);
           $aResult[$row['product_id']]['json_price']['cost'] = $json->get_cur_order($aResult[$row['product_id']]['price']['cost']);
           $aResult[$row['product_id']]['json_price']['member_lv_price'] = $json->get_cur_order($aResult[$row['product_id']]['price']['member_lv_price']);
           $aResult[$row['product_id']]['json_price']['buy_price'] = $json->get_cur_order($aResult[$row['product_id']]['price']['buy_price']);
           $aResult[$row['product_id']]['url'] = $router->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$aResult[$row['product_id']]['goods_id']));
           $aResult[$row['product_id']]['thumbnail'] = base_storager::image_path( $aResult[$row['product_id']]['default_image']['thumbnail'],'s');
           $tmp = $aResult[$row['product_id']];
           $this->getScroe($tmp);
           $aResult[$row['product_id']] = $tmp;

           if($row['package_use']) {
               if($row['package_scale']) {
                   $aResult[$row['product_id']]['quantity'] = $row['package_scale'];
                   foreach($aResult[$row['product_id']]['price'] as &$s_v_price) {
                        $s_v_price *= $row['package_scale'];
                   }
               }
           }
           $tmp = $aResult[$row['product_id']]['spec_info'];
           $aResult[$row['product_id']]['new_name'] = $row['name'] . ( $tmp ? ' ('. $tmp .')' : '' );
       }

       //商品不存在时购物车里也同时删除
       if(!empty($arrDelGoods)) {
           foreach ($aResult as $key => &$val) {
               if(in_array($val['goods_id'], $arrDelGoods)) {
                   unset($aResult[$key]);
              }
           }
      }

       //商品不存在时购物车里也同时删除
       return $aResult;
    }




    /**
     * 积分
     *
     * @param unknown_type $aData
     */
    private function getScroe(&$aData=array()) {
        $arr = $aData;
        //addtime 2010-11-29 20:57
        foreach( kernel::servicelist("b2c_cart_goods_get_score") as $object ) {
            if( method_exists($object,'getScroe') )
                return $aData['gain_score'] = $object->getScroe($arr);
        }
        $this->__get_score( $aData );
    }

    public function __get_score( &$aData ) {
        //获取商店积分规则
        if(!isset($this->site_score_policy) && empty($this->site_score_policy)) {
            $this->site_score_policy = $this->app->getConf('site.get_policy.method');
        }


        if($this->site_score_policy==1) { //不使用积分
            $gain_score = 0;
        } else if ($this->site_score_policy==2) {
            if(!isset($this->site_score_rate) && empty($this->site_score_rate)) {
                $this->site_score_rate = $this->app->getConf('site.get_rate.method');
            }
            $gain_score = $this->omath->number_multiple( array($aData['price']['buy_price'],$this->site_score_rate) );

        } else if ($this->site_score_policy==3) {
            $gain_score = $aData['gain_score'];
        }
        $aData['gain_score'] = $gain_score;
    }




    public function set_cookie($var='', $val=array()) {
        if(empty($var))return false;
        if(empty($val) || !is_array($val)) return false;
        kernel::single("base_session")->start();
        $_SESSION[$this->md5m($var)] = $val;

        return true;
    }


    private function code($val='', $flag=false) {
        if($flag) {
            return base64_encode(@serialize($val));
        } else {
            return @unserialize(base64_decode($val));
        }
    }


    public function get_cookie($var='') {
        if(empty($var))return false;
        kernel::single("base_session")->start();
        $arr_data = $_SESSION[$this->md5m($var)];
        return (empty($arr_data) ? array() : $arr_data);
    }

    public function del_cookie($var='') {
        if(empty($var))return false;
        kernel::single("base_session")->start();
        $_SESSION[$this->md5m($var)] = null;
    }

    private function md5m($var='') {
        return $var;
        return md5(md5($var).'_shopex_goods');
    }



    public function apply_to_disabled( $data,&$session,$flag) {
        foreach( (array)$data as $_key => $_val ) {
            if( !isset( $session[$_val['obj_ident']] ) ) continue;
            if( $session[$_val['obj_ident']]==='true' ) {
                $data[$_key]['disabled'] = 'true';
                $tmp[$_val['obj_ident']] = true;
            } else {
                foreach( (array)$session[$_val['obj_ident']] as $_type => $_sess_by_type ) {
                    if( !isset($_val[$_type]) ) continue;
                    foreach( (array)$_val[$_type] as $_item_key => $_item_val ) {
                        if( isset($_sess_by_type[$_item_val['product_id']]) && $_sess_by_type[$_item_val['product_id']]==='true' ) {
                            if( $flag ) {
                                $data[$_key][$_type][$_item_key]['disabled'] = 'true';
                            } else {
                                unset($data[$_key][$_type][$_item_key]);
                            }
                            $tmp[$_val['obj_ident']][$_type][$_item_val['product_id']] = true;
                        }
                    }
                }
            }
        }

        $this->unset_session( $session,$tmp );
        return $data;
    }


    private function unset_session( &$session,$tmp ) {
        if( !is_array($session) ) return ;
        foreach( $session as $_key => &$_val ) {
            if( !isset($tmp[$_key]) ) {
                unset($session[$_key]);
            } else {
                if( is_array($tmp[$_key]) ) {
                    $this->unset_session( $_val,$tmp[$_key] );
                }
            }
        }
    }



    public function get_update_num( $data,$ident ) {
        $o_currency = kernel::single('ectools_mdl_currency');

        $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
        $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');

        foreach( (array)$data as $row ) {
            if( $row['obj_ident']!==$ident['ident'] ) continue;
            if( $ident['index'] ) {
                foreach( (array)$ident['index'] as $key ) {
                    $row = $row[$key];
                }
                foreach( (array)$row as $val ) {
                    if( $ident['id']==$val['product_id'] ) {
                        return array('buy_price'=>($o_currency->changer_odr($val['subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset)),'consume_score'=>(float)($val['gain_score']*$val['quantity']));
                    }
                }
            } else {
                return array('buy_price'=>$o_currency->changer_odr($row['obj_items']['products'][0]['price']['buy_price']*$row['quantity'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset), 'consume_score'=>(float)($row['obj_items']['products'][0]['gain_score']*$row['quantity']));
            }
        }
    }

    /**
     * return 错误信息 如：商品起订量等。
     */
    public function get_error_html( $data,$ident ) {
        foreach( (array)$data as $row ) {
            if( $row['obj_ident']!==$ident['ident'] ) continue;
            if( $row['min_buy']['min_buy']>$row['quantity'] ) return '(提示:该商品不足起订量!起订量为：'.$row['min_buy']['min_buy'].')';
        }
    }

    private function get_error_msg( $msg ) {
        return array('status'=>'false','msg'=>$msg);
    }
}