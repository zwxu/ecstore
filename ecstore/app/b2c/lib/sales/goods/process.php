<?php

 
/**
 * 商品促销规则预过滤处理
 * $ 2010-05-11 13:27 $
 */
class b2c_sales_goods_process extends b2c_sales_basic_prefilter
{
    protected $default_aggregator = "b2c_sales_goods_aggregator_combine"; // 默认处理的聚合器(abstract::getTemplate方法使用)
    /**
     * 生成过滤的sql条件
     *
     * @param array $conditions // array(
     *                                'type'=>'b2c_sales_goods_aggregator_combine'
     *                                'aggregator' => 'all', // 'all'|'any' [and连接条件 或 or连接条件]
     *                                'vlaue'      => '1', // 0|1   不满足以下条件 | 满足以下条件  (//不满足? 暂无很好的处理方法 可以用子查询实现 效率太低了 1暂时写死)
     *                                'conditions' => array(
     *                                                  '0' => array(
     *                                                           'attribute' => 'bn',  // 商品的属性
     *                                                           'operator'  => '=',   // 操作
     *                                                           'value'     => 'xxx', // 值 string | array
     *                                                   ),
     *                                                   '1' => array(
     *                                                             'aggregator'=>'any',
     *                                                             'vlaue' => 1,
     *                                                             'conditions' => array(
     *                                                                                0 => array(...),
     *                                                                                1 => array(...),
     *                                                                                 ...
     *                                                                             )
     *                                                    ),
     *                                                    ....
     *                                                 )
     *                                 )
     * @return string
     * @access private
     */
    public function filter($aConditions) {
        $oCond = kernel::single('b2c_sales_goods_aggregator_combine');
        $sWhere = $oCond->filter($aConditions);
        $default_where = ' goods_type="normal" AND ';
		$end_where = $default_where . (empty($sWhere)? ' 1 = 1' : $sWhere);
		#echo $end_where;exit;
		$sale_apply_service = kernel::service('sale_apply_service');
		if(is_object($sale_apply_service) && method_exists($sale_apply_service,'filter'))
		{
			$extends_where = $sale_apply_service->filter($aConditions,$end_where);
			return $extends_where;
		}
		else
		{
			return $end_where;
		}
        
    }

    /**
     * 清空指定rule_id的goods promotion
     *
     * @param int $rule_id
     * @return boolean
     * @access public
     */
    public function clear($rule_id) {
        if(empty($rule_id) || !is_int($rule_id)) return false;
        $sSql = "DELETE FROM sdb_b2c_goods_promotion_ref WHERE rule_id='".$rule_id."'";
        if($this->db->exec($sSql)) {
            $sSql = "UPDATE sdb_b2c_sales_rule_goods SET apply_time = 0 WHERE rule_id='".$rule_id."'";
            $this->db->exec($sSql);
            return true;
        }
        return false;
    }

    /**
     * 清空所有的goods promotion
     *
     * @return boolean
     * @access public
     */
    public function clearAll($aRes = array()) {
        if(is_array($aRes)) {
            $rule_ids = implode(',', $aRes);
            $sql = sprintf( 'DELETE FROM sdb_b2c_goods_promotion_ref WHERE rule_id in (%s)', $rule_ids );
            if( $this->db->exec( $sql ) ) {
                $sql = sprintf( 'UPDATE sdb_b2c_sales_rule_goods SET apply_time = 0 WHERE rule_id in (%s)', $rule_ids );
                return $this->db->exec($sql);
            }
            return false;
        } else if (empty($aRes)) {
            //echo "all";
        } else {
            return false;
        }
        //exit;
        $sSql = "TRUNCATE TABLE sdb_b2c_goods_promotion_ref";
        if($this->db->exec($sSql)) {
            $sSql = "UPDATE sdb_b2c_sales_rule_goods SET apply_time = 0 WHERE 1";
            $this->db->exec($sSql);
            return true;
        }
        return false;
    }

    /**
     * 预处理一条goods promotion
     *
     * @param int $rule_id
     * @return boolean
     * @access public
     * notice: 如果应用一条新的规则先 clear() 如果应用所有的规则 先clearAll()
     */
    public function apply($rule_id) {
        if(empty($rule_id) && !is_int($rule_id)) return false;
        $sSql = "SELECT * FROM sdb_b2c_sales_rule_goods WHERE rule_id='".intval($rule_id)."'";
        $aResult = $this->db->selectrow($sSql);
        if(empty($aResult)) return false;
        return $this->_apply($aResult);
    }

    /**
     * 预处理规则(测试用例中直接使用_apply)
     * 现在是应用于所有的商品,是否只应用disabled='false'
     *
     * @param array $aData // format as dbscheme/goods_promotion_ref
     * @return boolean
     * @access private
     */
    public function _apply($aData) { // todo public 测试用例要直接调用这个方法
        $conditions = is_array($aData['conditions']) ? $aData['conditions'] : @unserialize($aData['conditions']);
        // todo 如果条件为空返回 false  如果可以设置为空(全场商品应用的规则) 再进行修改(可以不用filter处理了)
        if(empty($conditions)) return false;
        // name可能会要 `name`, '".$aData['name']."',
        $sSql = "INSERT INTO sdb_b2c_goods_promotion_ref(
                   `goods_id`,`rule_id`,`description`,`from_time`,`to_time`,`sort_order`,`stop_rules_processing`,`action_solution`,`free_shipping`,`member_lv_ids`,`status`
                 )
                 SELECT
                   goods_id,'".$aData['rule_id']."','".$aData['description']."','".$aData['from_time']."','".$aData['to_time']."','".$aData['sort_order']."','".$aData['stop_rules_processing']."','".$aData['action_solution']."','".$aData['free_shipping']."','".$aData['member_lv_ids']."', 'true'
                 FROM sdb_b2c_goods WHERE ".$this->filter($conditions);
        //kxgsy addtime:2010-5-28
        $this->db->exec("DELETE FROM `sdb_b2c_goods_promotion_ref` WHERE `rule_id`='". $aData['rule_id'] ."'");

        if($this->db->exec($sSql)) {
            $sSql = "UPDATE sdb_b2c_sales_rule_goods SET status = 'true',apply_time = ".time()." WHERE rule_id='".$aData['rule_id']."'";
            $this->db->exec($sSql);
           # $this->save_sale_goods_info($conditions); //保存到数据库后再存一份到KV
            return true;
        }
        return false;
    }

    /**
     * 匹配本规则的商品数
     * 一个很好玩的方法 传入相关数据得到此条件可以匹配到的商品数量
     * 可以用来_apply之前查看能否匹配的数量 没有就可以不做_apply了 only notice
     *
     * @param array $aData // format as _apply
     * @return int
     */
    public function test($aData) {
        $conditions = is_array($aData['conditions']) ? $aData['conditions'] : @unserialize($aData['conditions']);

        // todo 如果条件为空返回 0  如果可以设置为空(全场商品应用的规则) 再进行修改(可以不用filter处理了)
        if(empty($conditions)) return 0;
#echo $this->filter($conditions);exit;
        $sSql = "SELECT count(*) AS num FROM sdb_b2c_goods WHERE ".$this->filter($conditions);
        $aResult = $this->db->selectrow($sSql);
        return $aResult['num'];
    }

    /**
     * 获取可应用到的商品数量
     *
     * @param int $rule_id
     * @return int
     */
    public function getApplyNum($rule_id){
        if(empty($rule_id) && !is_int($rule_id)) return false;
        // todo 通过ID取得goods promotion相关信息 mdl.sales_goods_rule.php
        $sSql = "SELECT * FROM sdb_b2c_sales_rule_goods WHERE rule_id='".$rule_id."'";
        $aResult = $this->db->selectrow($sSql); #
        if(empty($aResult)) return 0;
        return $this->test($aResult);
    }

    public function makeTemplate($aTemplate = array(), $aData = array(),$vpath = 'conditions',$is_auto = false) {
        
        $aTemplate['type'] = $this->default_aggregator;
        if(!isset($aTemplate['conditions'])) { // 第一次自定义的载入 如果没有conditions 也得补上一个
            $aTemplate['conditions'] = array();
        }

        return parent::makeTemplate($aTemplate, $aData, $vpath,$is_auto);
    }

    /**
     * 获取模板列表信息
     *
     */
    public function getTemplateList() {
        $aResult = array();
        foreach(kernel::servicelist('b2c_promotion_tpl_goods_apps') as $object) {
            $aResult[get_class($object)] = array('name'=>$object->tpl_name,'type'=>$object->type);
        }
        return $aResult;
    }

    public function getTemplate($tpl_name,$aData = array()) {
        
        $oTC = kernel::single($tpl_name);
        
        // todo 这里可以改成service来进行处理的 2010-05-16 13:00
        switch($oTC->tpl_type) {
            case 'html':
                return $oTC->getConfig($aData);
                break;
            case 'config':
            case 'auto':
                $flag = ($oTC->tpl_type == 'auto');
                return $this->makeTemplate($oTC->getConfig(),$aData,'conditions',$flag);
                break;
        }
        return false;
    }

    public function makeCondition($aData){
        $oSGAC = kernel::single($this->default_aggregator);
        $aAttribute = $oSGAC->getAttributes();
        if(array_key_exists($aData['condition'],$aAttribute)) {
             $html = kernel::single($aAttribute[$aData['condition']]['object'])->view(array('type'=>$aAttribute[$aData['condition']]['object'],'attribute'=>$aData['condition']),array(),$aData['path'],$aData['level'],$aData['position'],true);
        } else { // item
             $html = kernel::single($aData['condition'])->view(array('type'=>$aData['condition']),array(),$aData['path'],$aData['level'],$aData['position'],true);
        }
        return $oSGAC->create_remove().$html;
    }

    /**
    *商品促销信息保存到KV 减少前台数据库操作 
    **/
    public function save_sale_goods_info($conditions)
    {
        if(empty($conditions)) return false;
        $sql = "SELECT goods_id FROM sdb_b2c_goods WHERE ".$this->filter($conditions);
        $aGoods = $this->db->select($sql);
        if(empty($aGoods)) return false;
        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $goods_promotion_ref = $this->app->model('goods_promotion_ref');
        foreach ((array)$aGoods as $g_k => $g_v) {
            $aResult = $goods_promotion_ref->getList('*', array('goods_id'=>$g_v['goods_id']),0,-1,$order);
            base_kvstore::instance('b2c_sale_goods_info')->store('b2c_sale_goods_info_'.$g_v['goods_id'],$aResult);
            unset($aResult);
        }
    }
    
}

