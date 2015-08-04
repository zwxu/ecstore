<?php

 
/**
 * items基类
 * $ 2010-05-08 19:38 $
 */
class b2c_sales_basic_item extends b2c_sales_basic_filter
{
    protected function _init_attribute() {
        $this->_aAttribute = $this->getItem(); // 只是自身的
    }

    public function getDefaultStandardData() {
        return array(
                 'type'=> array(
                                'input'=> 'hidden',
                                'vtype'=> null,
                                'desc'=> null,
                                'default'=> $this->default
                 ),
                'attribute'=> array(
                               'input'=>'hidden', // 写死的(一定是死的,不是死的就做死自己了)
                               'default'=>null,   // 这个由模板$aTemplate['attribute'](如0=>array('attribute'=>'brand_name',...)) 或 $aTemplate(如0=>'brand') 或 $aData['attribute'](如果有模板的话 不出错的情况 $aData['attribute'] 也一样) 定义
                               'desc' => null,      // 由$aTemplate['attribute_desc']设置 或 default的attribute['name'] 决定
                 ),
                'operator'=> array(
                               'input'=>'select', // 由$options 是array 和 string 决定
                               'options'=>null, // 由$aTemplate['operator'] 或 $aTemplate['attribute']['type'] 或 $aData['attribute']['type'] 决定
                               'default'=>null,   // 这个由 aTemplate['operator_default'] 或 $aData['operator']
                               'desc' => null,      // 由模板$aTemplate['attribute_desc']设置 或 default的attribute['name'] 决定 // input = 'hidden' 才用到
                 ),
                'value'=> array(
                               'input'=>'text',   // 由$aTemplate['attribute']['input'] 决定 没有使用默认 text
                               'default'=>null,   // 这个由 aTemplate['default'] 或 $aData['value'] 决定 有 string | array
                               'vtype'=>null,    // 由$aTemplate['vtype'] 设定 否则为空
                               'desc' => null,      // 这个一般没有
                               'options'=>null,   // 由attribute['options'] 决定 (除text|datetime) 其它的都可能使用到
                               'size' => null     // 输入框大小 aTemplate['size'] 没有 使用默认
                )
        );
    }


    /**
     * 标准数据格式(结合模板和数据)
     *
     * @param array $aTamplate
     * @param array $aData
     * @return array()
     */
    public function getStandardData($aTamplate = array(),$aData = array()) {
        $aStandard = $this->getDefaultStandardData();
        // 条件的话 没有标准格式没有attribite/default 返回就没有意义了
        if(empty($aTamplate) && empty($aData)) return false;

        return $this->_getStandardData($aStandard,$aTamplate,$aData);
    }

    /**
     * 标准数据格式(结合模板和数据)(实际处理 重载此方法就行了)
     *
     * @param array $aStandata
     * @param array $aTamplate
     * @param array $aData
     * @return array()
     */
    protected function _getStandardData($aStandard,$aTamplate,$aData) {
        /////////////////////////// type //////////////////////////
        $aStandard['type'] = $this->_makeStandardData($aStandard['type'],$aTamplate['type'],$aData['type']);

        /////////////////////////// attribute //////////////////////////
        $aStandard['attribute'] = $this->_makeStandardData($aStandard['attribute'],$aTamplate['attribute'],$aData['attribute']);
        $attr = $this->getAttribute($aStandard['attribute']['default']); // 这个决定了$aStandard['operator']['options']

        $aStandard['attribute']['desc'] = empty($aStandard['attribute']['desc'])? $attr['name'] : $aStandard['attribute']['desc'];
        $aOperator = $this->getOperators($attr['operator']);

        $aStandard['operator']['options'] = $aOperator;
        /////////////////////////// operator //////////////////////////
        $aStandard['operator'] = $this->_makeStandardData($aStandard['operator'],$aTamplate['operator'],$aData['operator']);

        ///////////////////////////  value   ///////////////////////
        $aStandard['value'] = $this->_makeStandardData($aStandard['value'],$aTamplate['value'],$aData['value']);
        // todo:模板有设置按模板 default standard都为text
        $aStandard['value']['input'] = isset($aTamplate['value']['input'])? $aStandard['value']['input'] : ((isset($attr['input']))? $attr['input'] : $aStandard['value']['input']);
        // todo:这个并不是做的很好, 可以再想想
        $aStandard['value']['options'] = isset($attr['options'])? $this->_makeOptions($attr['options']) : $aStandard['value']['options'];
        return $aStandard;
    }

    /**
     * 生成后台模板(条件的处理)
     *
     * @param array $aTemplate  // 促销规则模板
     * @param array $aData      //
     * @param string $vpath
     * @param int $level
     * @param int $position
     * @param boolean $is_auto // 这个在attribute中并没有用到
     */
    public function view($aTemplate,$aData,$vpath,$level,$position,$is_auto) {

         
        $aStandard = $this->getStandardData($aTemplate,$aData); 
        
          
      
       
        $sAttribute = '';
        // type
        
        //PanF    2013-07-03
        $aStandard['type']['isfront'] = $aData['isfront'];  

        $sAttribute .= $this->_standard_view($aStandard['type'],($vpath."[type]"),$level,$position); 
         
        if(empty($sAttribute)) return false; // 没有type怎么能活呢

        // attribute
         //PanF    2013-07-03
        $aStandard['attribute']['isfront'] = $aData['isfront'];  

        $sAttribute .= $this->_standard_view($aStandard['attribute'],($vpath."[attribute]"),$level,$position);
        if(empty($sAttribute)) return false; // 没有attribute怎么能活呢

         // operator
           //PanF    2013-07-03
        $aStandard['operator']['isfront'] = $aData['isfront'];  


        $sAttribute .= $this->_standard_view($aStandard['operator'],($vpath."[operator]"),$level,$position);
        if(empty($sAttribute)) return false; // 没有operator怎么能活呢
       
        // value
        if($aStandard['type']['default']) { //test  
            
            $tmp_item = kernel::single($aStandard['type']['default'])->getItem(); 
            $arr_temp_item = $tmp_item[$aStandard['attribute']['default']]['table'];

            
        }

             //PanF    2013-07-03
        $aStandard['value']['isfront'] = $aData['isfront'];   

        $sAttribute .= $this->_standard_view($aStandard['value'],($vpath."[value]"),$level,$position,$arr_temp_item);
        if(empty($sAttribute)) return false; // 没有value怎么能活呢
 
        return $sAttribute;
    }
}
?>
