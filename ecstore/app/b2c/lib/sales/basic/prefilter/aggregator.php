<?php

 
/**
 * aggregator基类
 * $ 2010-05-09 19:39 $
 */
class b2c_sales_basic_prefilter_aggregator extends b2c_sales_basic_aggregator
{
     public function filter($aConditions){
        $aConditions['aggregator'] = ($aConditions['aggregator'] === 'all')? ' AND ' : ' OR ';
        $aResult = array();
        if(!is_array($aConditions['conditions'])) return false; // 如果下面的条件为空 或 不是数组 则返回false 2010-05-17 18:04
        foreach($aConditions['conditions'] as $key => $row) {
             $oCond = kernel::single($row['type']);
             $sTemp = $oCond->filter($row);
             if($sTemp) $aResult[] = $sTemp;// 只取有返回结果的
        }
        if(empty($aResult)) return false;
        return $this->_filter($aResult,$aConditions['aggregator'],$aConditions['value']);
    }

    public function _filter($aResult,$sSlice,$bIsNot) {
        if($bIsNot) {
            return '('.implode(" {$sSlice} ",$aResult).')';
        } else {//not in (不符合)
            if(isset($this->pkey) && isset($this->table)) {
                return ' '.$this->pkey.' NOT IN (SELECT `'.$this->pkey.'` FROM '.$this->table.' WHERE '.implode(" {$sSlice} ",$aResult).') ';
            }
        }
        return false;
    }
    
    
    
    
    
    
    
    
    
    /**
     * 标准数据格式(结合模板和数据)
     *
     * @param array $aTamplate
     * @param array $aData
     * 预过滤操作符无法选择、去掉了。。。当初为啥加上的。。。。。
     */
    public function getStandardData($aTamplate = array(),$aData = array()) {
        $aStandard = $this->getDefaultStandardData();
        if( is_array( $aStandard ) ) {
            foreach( $aStandard as $key => &$row ) {
                #$row['input'] = 'hidden';
            }
        }
        // 为空返回标准格式
        if(empty($aTamplate) && empty($aData)) return $aStandard;

        return $this->_getStandardData($aStandard,$aTamplate,$aData);
    }
}

