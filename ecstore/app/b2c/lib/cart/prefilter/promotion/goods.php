<?php


/**
 * 商品促销预过滤
 * $ 2010-04-29 11:52 $
 */
class b2c_cart_prefilter_promotion_goods implements b2c_interface_cart_prefilter {
    private $app;
    private $_app_status;
    private $_arr_solution_object;
    private $product_aConfig = array();
    public function __construct(&$app){
        $this->app = $app;
    }

    public function filter(&$aResult,$aConfig) {


        // 没有商品数据
        if(empty($aResult['object']['goods'])) return false;


        if(!isset($aConfig['promotion']['goods'])) {//购物车的时候
            $aGoodsId = array();

            foreach($aResult['object']['goods'] as $row) {
                if(empty($row['obj_items']['products']['0']['goods_id'])) continue;
                $gid = $row['obj_items']['products']['0']['goods_id'];

                //验证是否参与促销 false：不参与
                foreach( kernel::servicelist("b2c_promotion_use_check") as $object ) {
                    if( !$object->check_use_promotion( $gid ) ) continue 2;
                }

                $aGoodsId[] = $gid;
            }
            $aConfig = $this->_init_rule(array_unique($aGoodsId),array('current_time'=>time()));
        } else {
            $aConfig = $aConfig['promotion']['goods'];
        }

        $this->_filter($aResult,$aConfig);
    }

    public function order() {
        return 'sdb_b2c_goods_promotion_ref.sort_order ASC,sdb_b2c_goods_promotion_ref.ref_id DESC';
    }

    public function get_goods_sales( $arrGoods,$aConfig = null ) {
        if(isset($arrGoods['goods_ids']) ){
            $gid = (is_array($arrGoods['goods_ids']))?$arrGoods['goods_ids']:array($arrGoods['goods_ids']);
        }else{
            $gid = (is_array($arrGoods['goods_id']))?$arrGoods['goods_id']:array($arrGoods['goods_id']);
        }
        if(empty($aConfig))$aConfig = $this->_init_rule(array($gid),array('member_lv'=>'false'));

		foreach ($gid as $goods_id){
            $aConfig = $aConfig[$gid];
            if( $aConfig ) {
                foreach( $aConfig as $row ) {
                    $s_template = $row['s_template'];
                    $app = substr($s_template,0,strpos($s_template,'_'));
                    if( $app ) {

                        //app是否安装
                        if( !$this->_app_status[$app] )
                            $this->_app_status[$app] = app::get($app)->is_actived();
                        if( $this->_app_status[$app] ) {

                            //优惠方案实例化
                            if( !$this->_arr_solution_object[$s_template] )
                                $this->_arr_solution_object[$s_template] = kernel::single($s_template);

                            $o = $this->_arr_solution_object[$s_template];

                             //优惠方案状态
                            if( !method_exists($o,'get_status') ) continue;
                            if( !$o->get_status() ) continue;

                            if( !method_exists($o,'get_solution_after') ) continue;
                            $solution = unserialize($row['action_solution']);
                            $solution = $solution[$s_template];
                            $arr = $o->get_solution_after( $solution,$row,$arrGoods );
                            foreach( $arr as $key => $val ) {
                                $return[$key] = $val;
                            }

                            //排他推出
                            if( $row['stop_rules_processing']=='true' ) break;
                        }
                    }
                }
            }
        }//end foreach
        return $return;
    }

    public function _init_rule_public($gid,$filter = array()) {
        return $this->_init_rule($gid,$filter);
    }

    /**
     * 初始化商品过滤规
     *
     * @param array $aGoodsId // array(xxx,xxx,xxx);
     */
    private function _init_rule($aGoodsId,$filter = array()) {
        if(empty($aGoodsId)) return false;
        $filter['goods_id'] = $aGoodsId;
        $arrMemberInfo = kernel::single("b2c_cart_objects")->get_current_member();

        if( !$filter['member_lv'] ) $filter['member_lv'] = $arrMemberInfo['member_lv'] ? $arrMemberInfo['member_lv'] : -1;

        $sSql = "SELECT sdb_b2c_goods_promotion_ref.*,sdb_b2c_sales_rule_goods.name,sdb_b2c_sales_rule_goods.s_template
					FROM sdb_b2c_sales_rule_goods
					JOIN sdb_b2c_goods_promotion_ref ON sdb_b2c_goods_promotion_ref.rule_id = sdb_b2c_sales_rule_goods.rule_id
					WHERE ".$this->_filter_sql($filter)."
					ORDER BY ".$this->order();

        $aResult = $this->app->model('cart')->db->select($sSql);
        if(empty($aResult)) return false;
        //是否允许同一商品有多个预过滤规则
        return utils::array_change_key($aResult,'goods_id', 1);
    }

    /**
     * sql过滤的where条件
     */
    private function _filter_sql($aFilter) {
        $aWhere[] = "sdb_b2c_goods_promotion_ref.status = 'true'"; // 开启状态


        if(isset($aFilter['goods_id'])) {
            $aWhere[] = " sdb_b2c_goods_promotion_ref.goods_id IN (".implode(',',$aFilter['goods_id']).")";
        }

        if (isset($aFilter['member_lv']) && $aFilter['member_lv']!=='false'){
            $aWhere[] = ' (find_in_set(\''. $aFilter['member_lv'] .'\', sdb_b2c_goods_promotion_ref.member_lv_ids))';
            unset($aFilter['member_lv']);
        }

        if (isset($aFilter['current_time'])){
            $aWhere[] = sprintf(' (%s >= sdb_b2c_goods_promotion_ref.from_time or sdb_b2c_goods_promotion_ref.from_time=0)',
                               $aFilter['current_time']);
            $aWhere[] = sprintf(' (%s <= sdb_b2c_goods_promotion_ref.to_time or sdb_b2c_goods_promotion_ref.to_time=0)', $aFilter['current_time']);
            unset($aFilter['current_time']);
        }
        return implode(' AND ',$aWhere);
    }

    private function _filter(&$aResult,$aConfig) {

        if(empty($aConfig)) return false; // 不需要过滤

        foreach($aResult['object']['goods'] as &$row) {
            $iGoodsId = $row['obj_items']['products']['0']['goods_id'];
            $tmp = $aConfig[$iGoodsId];
            if($tmp) $this->_filter_product($aResult, $row,$tmp);


        }

        $aConfig = null;
        //old 只显示符合当前商品的促销规则
        //$aResult['promotion']['goods'] = $aConfig;
    }

    // 单商品
    //单商品存在多维数组嘛？
    private function _filter_product(&$aData, &$aResult, &$aConfig) {
        if(isset($aConfig['goods_id'])) $aConfig[] = $aConfig;


        foreach($aConfig as $key=>$rule) {

            $action_solution = is_array($rule['action_solution']) ? $rule['action_solution'] : unserialize($rule['action_solution']);

            //原始价格用于下面记录优惠
            $this->__price = $aResult['obj_items']['products'][0]['price']['buy_price'];

            //商品促销中。同种方案在同一商品上 适用 排他原则 addtime 2011-2-23 16:03
            //我最想要的效果是：最后应用的规则是最终生效的规则
            if( $this->__stop_rules_processing_goods[$rule['s_template']][$rule['goods_id']] ) {
                $tmp_config = $this->__stop_rules_processing_goods[$rule['s_template']][$rule['goods_id']];
                $action_solution = $tmp_config['action_solution'];
                $rule = $tmp_config['rule'];
				continue;
            }
            if( $rule['stop_rules_processing']=='false' && kernel::single($rule['s_template'])->stop_rule_with_same_solution ) {
                $this->__stop_rules_processing_goods[$rule['s_template']][$rule['goods_id']]['action_solution'] = $action_solution;
                $this->__stop_rules_processing_goods[$rule['s_template']][$rule['goods_id']]['rule'] = $rule;
            }


            $this->_action($aResult, $action_solution,$aResult,$rule,$aData,$aConfig);
             // 不再执行下去 互斥
            if($rule['stop_rules_processing'] == 'true') break;
        }

    }

    // 执行优惠
    private function _action(&$aResult,$action_solution,&$aResult,$rule,&$aData,&$aConfig){


        //exit;
        if(!$action_solution) return false;
        foreach($action_solution as $key=>$row) {
            try{
                // 执行指定优惠方案
                $o = kernel::single($key);
                if(method_exists($o, 'get_status')) {
                    if(!$o->get_status()) return false;
                }
                $o->rule_id = $rule['rule_id'];
                $o->apply($aResult,$row);
            }catch (Exception $e){//没有相关的优惠方法
                return false; // 出现错误返回false
            }
            $this->add_cart_promotion_goods($key,$aConfig,$aResult,$rule,$aData);
            return $key;
        }
    }

    private function add_cart_promotion_goods($temp_solution_name,&$aConfig,&$aResult,$rule,&$aData) {

        //优惠执行成功时返回解决方案适用的lib
        if($temp_solution_name)  {
            $aConfig[$key]['used'] = true; // 这个优惠执行过

            //只显示符合当前商品的促销规则

            $oDefault = kernel::single($temp_solution_name);

            if(isset($aData['promotion']['goods'][$rule['rule_id']])) {
                if($oDefault->score_add) return true;
                $aData['promotion']['goods'][$rule['rule_id']]['discount_amount'] += $aResult['subtotal'] - $aResult['quantity']*$aResult['obj_items']['products'][0]['price']['buy_price'];
                if( empty($rule['description']) )
                    $aData['promotion']['goods'][$rule['rule_id']]['desc'] = $aResult['obj_items']['products'][0]['new_name'] .'、'. $aData['promotion']['goods'][$rule['rule_id']]['desc'];
            } else {
                $aData['promotion']['goods'][$rule['rule_id']] = array(
                    'name' => $rule['name'],
                    'rule_id'   =>  $rule['rule_id'],
                    'discount_amount' => 0,
                    'desc'  => (empty($rule['description']) ? ($aResult['obj_items']['products'][0]['new_name'] . $oDefault->getString()) : $rule['description'] ),
                    'solution' => $oDefault->getString(),
                );
                if($oDefault->score_add) return true;
                $aData['promotion']['goods'][$rule['rule_id']]['discount_amount'] = $aResult['subtotal'] - $aResult['quantity']*$aResult['obj_items']['products'][0]['price']['buy_price'];
            }

            //记录商品享受的促销 商超
            $obj_key = $aResult['obj_items']['products'][0]['product_id'];
            $aData['promotion_solution'][$obj_key]['goods_id'] = $aResult['obj_items']['products'][0]['goods_id'];
            $aData['promotion_solution'][$obj_key]['goods'][] = array(
                                                            'rule_id' => $rule['rule_id'],
                                                            'amount'  => ($this->__price-$aResult['obj_items']['products'][0]['price']['buy_price'])
                                                        );
            #print_r($aData['promotion_solution'][$obj_key]);echo "<HR>";
            /**
            $aData['promotion']['goods'][$aResult['obj_ident']] = array(
                                                    //'desc_pre' => $oDefault->desc_pre,
                                                    //'desc_post'=> $oDefault->desc_post,
                                                    //'amount'   => $action_solution[$temp_solution_name]['total_amount'],
                                                    //'desc'       => $oDefault->getString($action_solution[$temp_solution_name]),
                                                    'desc'       => $rule['description'],
                                                    'goods_name' => $aResult['obj_items']['products'][0]['new_name'],
                                                    'rule_id' => $rule['rule_id'],
                                                    'discount_amount' => &$aResult['discount_amount_prefilter'],
                                                );
            //*/

        }
    }
}

