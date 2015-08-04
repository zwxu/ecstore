<?php

 
/**
 * 订单促销规则处理 service
 * $ 2010-05-11 13:28 $
 */
class b2c_sales_order_process extends b2c_sales_basic_postfilter
{
    protected $default_aggregator = "b2c_sales_order_aggregator_combine";  // 默认处理的聚合器(conditions)(abstract::getTemplate方法使用)
    protected $default_item_aggregator = "b2c_sales_order_aggregator_item"; // 默认处理的聚合器(action_conditions)

    /**
     * 获取模板列表信息
     *
     */
    public function getTemplateList() {
        $aResult = array();
        foreach(kernel::servicelist('b2c_promotion_tpl_order_apps') as $object) {
            $aResult[get_class($object)] = array('name'=>$object->tpl_name,'type'=>$object->type);
        }
        return $aResult;
    }

    public function getTemplate($tpl_name,$aData = array()) {
        $oTC = kernel::single($tpl_name);

        if(isset($oTC->whole) && $oTC->whole) return $oTC->getConfig($aData);
 
        $aConfig = $oTC->getConfig();
        
        $aResult['conditions'] = $this->getConditionTemplate($aConfig['conditions'],$aData['conditions'],$tpl_name);
        #$aResult['conditions'] = $this->getConditionTemplate($aConfig['conditions'],$aData,$tpl_name);
        $aResult['action_conditions'] = $this->getActionConditionTemplate($aConfig['action_conditions'],$aData['action_conditions'],$tpl_name);

        return $aResult;
    }

    public function getConditionTemplate($aConfig,$aData,$tpl_name='') {
        if(empty($aConfig['type'])) $aConfig['type'] = 'config';
        switch($aConfig['type']) {
            case 'html':
                $oTC = kernel::single($tpl_name);
                if($tpl_name || method_exists($oTC,'getTemplate')) {
                    return $oTC->getTemplate($aData,'conditions');
                } else {
                    return $aConfig['info'];
                }
                break;
            case 'config':
            case 'auto':
                $flag = ($aConfig['type'] == 'auto');
                return $this->makeTemplate($aConfig['info'],$aData,'conditions',$flag);
                break;
        }
        return false;
    }

    public function getActionConditionTemplate($aConfig,$aData,$tpl_name='') {
        if(empty($aConfig['type'])) $aConfig['type'] = 'config';

        switch($aConfig['type']) {
            case 'html':
                $oTC = kernel::single($tpl_name);
                if($tpl_name || method_exists($oTC,'getTemplate')) {
                    return $oTC->getTemplate($aData,'action_conditions');
                } else {
                    return $aConfig['info'];
                }
                break;
            case 'config':
            case 'auto':
                $flag = ($aConfig['type'] == 'auto');
                return $this->makeTemplate($aConfig['info'],$aData,'action_conditions',$flag);
                break;
        }
        return false;
    }

    public function makeTemplate($aTemplate = array(), $aData = array(),$vpath = 'conditions',$is_auto = false) {
        if($vpath == 'conditions') {
            $aTemplate['type'] = $this->default_aggregator;
        } else {
            $aTemplate['type'] = $this->default_item_aggregator;
        }
        if(!isset($aTemplate['conditions'])) { // 第一次自定义的载入 如果没有conditions 也得补上一个
            $aTemplate['conditions'] = array();
        }
        return kernel::single($aTemplate['type'])->view($aTemplate,$aData,$vpath,0,null,$is_auto);
    }

    public function makeCondition($aData){
        $oSOAC = kernel::single($this->default_aggregator);
        $aAttribute = $oSOAC->getAttributes();
        if(array_key_exists($aData['condition'],$aAttribute)) {
             $html = kernel::single($aAttribute[$aData['condition']]['object'])->view(array('type'=>$aAttribute[$aData['condition']]['object'],'attribute'=>$aData['condition']),array(),$aData['path'],$aData['level'],$aData['position'],true);
        } else { // item
             $html = kernel::single($aData['condition'])->view(array('type'=>$aData['condition']),array(),$aData['path'],$aData['level'],$aData['position'],true);
        }
        return $oSOAC->create_remove().$html;
    }
}
?>
