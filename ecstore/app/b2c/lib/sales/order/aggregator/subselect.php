<?php

 
/**
 * order aggregator(商品促销规则 组合条件)
 * $ 2010-05-16 18:32 $
 */
class b2c_sales_order_aggregator_subselect extends b2c_sales_order_aggregator
{
    public function getItem() {
        // 其实一个aggregator 只有一条记录的哈
        return array(
                   'b2c_sales_order_aggregator_subselect' => array(
                                       'name'=>app::get('b2c')->_('商品子查询'),
                                       'object'=>'b2c_sales_order_aggregator_subselect',
                                       'support'=>array(
                                                     'item'=>array(
                                                                'goods'=>app::get('b2c')->_('-----商品属性-----'),
                                                                'subgoods'=>app::get('b2c')->_('-----商品扩展属性-----'),
                                                             )
                                                  ),
                                      )
               );
    }

    // 标准数据格式
    public function getDefaultStandardData() {
        $aTemp  = $this->getItem();
        $aStandard['attribute']['options'] = $this->getAttributes(array('subgoods'));
        $aStandard['operator']['options'] = $this->getOperators(array('equal','equal1'));
        return array(
                    'type'=> array(
                                'input'=> 'hidden',
                                'desc'=> null,
                                'default'=> $this->default,    // 默认值 如果有模板设置 按模板设定 如果有值 则按值的
                                'support'=>$aTemp[get_class($this)]['support'],
                    ),
                    'attribute'=> array(
                                        'input'=> 'select', // select | hidden
                                        'vtype'=> null,     // 验证类型(保留)
                                        'desc'=> null,      // 描述 标准为空 如果为空的话 使用default的名称 只在input='hidden'时有效
                                        'options'=> $aStandard['attribute']['options'],
                                        'default'=> null    // 默认值 如果有模板设置 按模板设定 如果有值 则按值的
                                ),
                    'operator'=> array(
                                        'input'=> 'select', // select | hidden
                                        'vtype'=> null,     // 验证类型(保留)
                                        'desc'=> null,      // 描述 标准为空 如果为空的话 使用default的名称 只在input='hidden'时有效
                                        'options'=> $aStandard['operator']['options'],
                                        'default'=> null    // 默认值 如果有模板设置 按模板设定 如果有值 则按值的
                                ),
                    'value'=> array(
                                    'input'=> 'text', // select | hidden
                                    'vtype'=> 'number',     // 验证类型(保留)
                                    'desc'=> null,    // 描述 标准为空 如果为空的话 使用default的名称 只在input='hidden'时有效
                                    'default'=> null  //
                             ),
                    'conditions'=> array()
              );
    }

    /**
     * 标准数据格式(结合模板和数据)
     *
     * @param array $aTamplate
     * @param array $aData
     */
    public function getStandardData($aTamplate = array(),$aData = array()) {
        $aStandard = $this->getDefaultStandardData();
        // 为空返回标准格式
        if(empty($aTamplate) && empty($aData)) return $aStandard;

        return $this->_getStandardData($aStandard,$aTamplate,$aData);
    }

    /**
     * 标准数据格式(结合模板和数据)(实际处理 重载此方法就行了)
     *
     * @param array $aStandata
     * @param array $aTamplate
     * @param array $aData
     */
    protected function _getStandardData($aStandard,$aTamplate,$aData) {
        /////////////////////////// type //////////////////////////
        $aStandard['type'] = $this->_makeStandardData($aStandard['type'],$aTamplate['type'],$aData['type']);

        /////////////////////////// attribute //////////////////////////
        $aStandard['attribute'] = $this->_makeStandardData($aStandard['attribute'],$aTamplate['attribute'],$aData['attribute']);
        $aStandard['attribute']['options'] = $this->getAttributes(array('subgoods'));
        /////////////////////////// operator //////////////////////////
        $aStandard['operator'] = $this->_makeStandardData($aStandard['operator'],$aTamplate['operator'],$aData['operator']);
        $aStandard['operator']['options'] = $this->getOperators(array('equal','equal1'));

        ////////////////////// aggregator value ///////////////////////
        $aStandard['value'] = $this->_makeStandardData($aStandard['value'],$aTamplate['value'],$aData['value']);

        return $aStandard;
    }

        /**
     * 生成后台模板(真正集合器的处理)
     *
     * @param array $aStandard
     * @param string $html
     * @param string $vpath
     * @param int $level
     * @param int $position
     * @param boolean $is_auto
     */
    protected function _view($aStandard,$html,$vpath,$level,$position,$is_auto) {
        $sAggregator = '';
        $sAuto = '';
        $sAuto1 = '';

        // type
        $sAggregator .= $this->_standard_view($aStandard['type'],($vpath."[type]"),$level,$position,$is_auto);
        if(empty($sAggregator)) return false; // 没有type怎么能活呢

        // attribute
        $sAggregator .= $this->_standard_view($aStandard['attribute'],($vpath."[attribute]"),$level,$position,$is_auto);
        if(empty($sAggregator)) return false; // 没有attribute怎么能活呢

        // operator
        $sAggregator .= $this->_standard_view($aStandard['operator'],($vpath."[operator]"),$level,$position,$is_auto);
        if(empty($sAggregator)) return false; // 没有attribute怎么能活呢

        // value
        $sAggregator .= $this->_standard_view($aStandard['value'],($vpath."[value]"),$level,$position,$is_auto);
        if(empty($sAggregator)) return false; // 没有value怎么能活呢
        
        $sAggregator .= '<span class="lnk" onclick="showConditions(this)">'.app::get('b2c')->_('[添加一个条件]').'</span>';

        if($is_auto) {
            $sAuto = $this->wrap_li($this->makeConditionOptions($aStandard['type'],$vpath,$level));// 如果自动配置的话 这个得有条件的处理
            $sAuto1 = $this->create_auto();
        }

        $html = $this->wrap_div($sAggregator).$this->wrap_ul($html.$sAuto);
        $html = (is_null($position))? $this->wrap_div($html).$sAuto1 : $html;
        return $html;
    }

    // 集合器的处理(subselect) 只处理goods的
    public function validate($cart_objects,$aCondition) {
        if (empty($aCondition['conditions']) || empty($aCondition['attribute']) || empty($aCondition['operator']) ) return false;
        // subselect attribute
        $aAttr = $this->getAttribute($aCondition['attribute']);
        $total = 0;

        // todo subselect下的condtions 都是必须满足的 以all的形式出现
        // value 设计相冲突了 不过value不般不可能为0
        $aCondition['aggregator'] = 'all';

        $oCond = kernel::single($this->default);
        foreach ($cart_objects['object']['goods'] as $object) {
            if ($oCond->validate($object, $aCondition)) {
                $total += (float) $this->getData($object, $aAttr['path']);
            }
        }
        // subselect operator
        $aOperator = $this->getOperator($aCondition['operator']);

        return kernel::single($aOperator['object'])->validate($aCondition['operator'], $aCondition['value'], $total);
    }
}
?>
