<?php

 
class b2c_mdl_entity_goods extends dbeav_model{

    public function save(&$sdf)
    {
        return parent::save($sdf);
    }
    

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        if ($filter)
            return parent::getList($cols, $filter, $offset, $limit, $orderby);
        else
            return parent::getList($cols, null, $offset, $limit, $orderby);
    }

}