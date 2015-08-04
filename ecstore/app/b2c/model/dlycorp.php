<?php

 

class b2c_mdl_dlycorp extends dbeav_model{
    var $has_many = array(
    );

    function save(&$sdf,$mustUpdate = null){
        return parent::save($sdf);
    }
    
    /**
     * 重写getlist方法，重写排序方式
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        if ($orderType)
            $orderType .= ',ordernum ASC';
        else
            $orderType = 'ordernum ASC';
            
        return parent::getList($cols, $filter, $offset, $limit, $orderType);
    }
}
