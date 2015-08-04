<?php

 
/**
 * aggregator基类
 * $ 2010-05-09 19:39 $
 */
class b2c_sales_basic_aggregator extends b2c_sales_basic_filter
{
    protected function _init_attribute() {
        if(is_null($this->_aAttribute)) {
            $aResult = array();
            foreach(kernel::servicelist($this->attribute_apps) as $object) {
                if(!is_object($object)) continue;
                $aResult = array_merge($aResult,$object->getItem());
            }
            $this->_aAttribute = $aResult;
        }
    }

    protected function _init_aggregator() {
        if(is_null($this->_aAggregator)) {
            $aResult = array();
            $this->_aAggregator = array_merge($aResult,$this->getItem());
        }
    }

    // 标准数据格式
    public function getDefaultStandardData() {
        $aTemp  = $this->getItem();
        return array(
                    'type'=> array(
                                'input'=> 'hidden',
                                'desc'=> null,
                                'default'=> $this->default,    // 默认值 如果有模板设置 按模板设定 如果有值 则按值的
                                'support'=>$aTemp[get_class($this)]['support'],
                    ),
                    'aggregator'=> array(
                                        'input'=> 'select', // select | hidden
                                        'vtype'=> null,     // 验证类型(保留)
                                        'desc'=> null,      // 描述 标准为空 如果为空的话 使用default的名称 只在input='hidden'时有效
                                        'options'=> array(
                                                        'any'=>array('name'=>app::get('b2c')->_('任意一条规则')),
                                                        'all'=>array('name'=>app::get('b2c')->_('所有规则')),
                                                    ),
                                        'default'=> 'all'    // 默认值 如果有模板设置 按模板设定 如果有值 则按值的
                                ),
                    'value'=> array(
                                    'input'=> 'select', // select | hidden
                                    'vtype'=> null,     // 验证类型(保留)
                                    'desc'=> null,      // 描述 标准为空 如果为空的话 使用default的名称 只在input='hidden'时有效
                                    'options'=> array(
                                                '0'=>array('name'=>app::get('b2c')->_('不符合')),
                                                '1'=>array('name'=>app::get('b2c')->_('符合')),
                                           ),
                                    'default'=> '1'    //
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

        /////////////////////////// aggregator //////////////////////////
        $aStandard['aggregator'] = $this->_makeStandardData($aStandard['aggregator'],$aTamplate['aggregator'],$aData['aggregator']);

        ////////////////////// aggregator value ///////////////////////
        $aStandard['value'] = $this->_makeStandardData($aStandard['value'],$aTamplate['value'],$aData['value']);

        return $aStandard;
    }

    /**
     * 生成后台模板(主要是条件的处理)
     *
     * @param array $aTemplate  // 促销规则模板
     * @param array $aData      //
     * @param string $vpath
     * @param int $level
     * @param int $position
     * @param boolean $is_auto
     */
    public function view($aTemplate,$aData,$vpath = 'conditions',$level = 0,$position = null,$is_auto = false) {
        // todo:有数据的就是走数据
        if(isset($aData['conditions']) && is_array($aData['conditions'])) {
            $aConditions = $aData['conditions'];
            $aConditions1 = $aTemplate['conditions'];
            $bFlag = true; // 有数据的
        }else if(isset($aTemplate['conditions']) && is_array($aTemplate['conditions'])) {
            $aConditions = $aTemplate['conditions'];
            $aConditions1 = array(); // 只有模板的话 那说明是没有值的
            $bFlag = false; // 没有数据,但有模板
        }else {// todo 只有聚合器 没有条件的 这里可以return false的
            $aConditions = array();
            $aConditions1 = array();
        }
        
		//页面makeConditions时有bug改成如此2010-11-30 20:07
        $i = count($aConditions) - 1;      // position 在同一层时 条件的所在的位置
        $level += 1; // level 一个新的集合器就是新的一层
        $html = '';  // 返回的html
        $remove = ''; // 删除
        if($is_auto) $remove= $this->create_remove();
        foreach($aConditions as $key=>$row) {
            $spath = $vpath.'[conditions]['.$i.']';
            if($bFlag) {
                $tTemplate = $aConditions1[$i];
                $tData = $row;
            } else {
                $tTemplate = $row;
                $tData = array();
            }
            //PanF  
            $tData['isfront'] = $aData['isfront'];
            $sTemp = kernel::single($row['type'])->view($tTemplate,$tData,$spath,$level,$i,$is_auto);
            if($sTemp) {
                $html .= $this->wrap_li($remove.$sTemp,$level,$i);
                $i--; // 只有返回的数据不是空 $position 才累加
            }
        }

        
        
        $aStandard = $this->getStandardData($aTemplate,$aData);
        return $this->_view($aStandard,$html,$vpath,$level,$position,$is_auto);
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

        // aggregator
        $sAggregator .= $this->_standard_view($aStandard['aggregator'],($vpath."[aggregator]"),$level,$position,$is_auto);
        if(empty($sAggregator)) return false; // 没有aggregator怎么能活呢

        // value
        $sAggregator .= $this->_standard_view($aStandard['value'],($vpath."[value]"),$level,$position,$is_auto);
        if(empty($sAggregator)) return false; // 没有value怎么能活呢

       

        if($is_auto) {
            $sAuto = $this->wrap_li($this->makeConditionOptions($aStandard['type'],$vpath,$level));// 如果自动配置的话 这个得有条件的处理
            $sAuto1 = $this->create_auto();
        }
        
        if( $this->create_to_order ) 
            $sAggregator .= '<span class="lnk" onclick="showConditions(this)">[添加一个条件]</span>';
        
        
        $html = $this->wrap_div($sAggregator).$this->wrap_ul($sAuto.$html);
        $html = (is_null($position))? $this->wrap_div($html).$sAuto1 : $html;

        
        return $html;
    }

    public function makeConditionOptions($type,$vpath,$level) {
        $this->create_to_order = true;
        $add_conditions = app::get('b2c')->_("选择需要添加的条件");
        $html = <<<EOF
                 <span style="display:none;">
                 <select onchange="makeConditions(this)" vpath="{$vpath}" vlevel="{$level}">
                     <option value="">{$add_conditions}</option>
                     {$this->_makeConditionOptions($type)}
                 </select>
                 </span>
EOF;
        return $html;
    }

    /**
     * 条件项
     *
     * @param array $type // 传入的是 aggregator standard data
     */
    private function _makeConditionOptions($type) {
        $html = '';
        // aggregator
        $html .= $this->_make_conditions_aggregator_options($type['support']['aggregator']);

        // item
        $html .= $this->_make_conditions_item_options($type['support']['item']);
        return $html;
    }

    private function _make_conditions_aggregator_options($aData) {
        if(empty($aData)) return '';
        $html = "<optgroup label='".app::get('b2c')->_('-----条件组合-----')."'>";
        foreach($this->_aAggregator as $key=> $row) {
            if($aData == 'all' || in_array($key,$aData)) $html .="<option value='".$key."'>".$row['name']."</option>";
        }
        return $html."</optgroup>";
    }

    private function _make_conditions_item_options($aData) {
        if(empty($aData)) $aData = array('all'=>app::get('b2c')->_('-----属性-----'));
        $html = '';
        foreach($aData as $key => $row) {
            $html .= "<optgroup label='".$row."'>".$this->_make_options($this->_aAttribute,$key)."</optgroup>";
        }
        return $html;
    }

    private function _make_options($aData,$filter) {
        $html = '';
        foreach($aData as $key => $row) {
            if($filter == 'all' || strpos($key,$filter) === 0) {
                $html .="<option value='".$key."'>".$row['name']."</option>";
            }
        }
        return $html;
    }
}
?>
