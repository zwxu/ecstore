<?php

 

class base_mdl_rpcnotify extends base_db_model{
    
    var $defaultOrder = array('notifytime','DESC');

    public function filter($filter){
        unset($filter['use_like']);
        return parent::filter($filter);
    }
    
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        $orderType = $orderType?$orderType:$this->defaultOrder;
        $sql = 'SELECT '.$cols.' FROM `'.$this->table_name(true).'` WHERE '.$this->filter($filter);
        if($orderType)$sql.=' ORDER BY '.(is_array($orderType)?implode($orderType,' '):$orderType);
        $data = $this->db->selectLimit($sql,$limit,$offset);
        $this->tidy_data($data, $cols);
        return $data;
    }
    
    
    public function modifier_status( $val ) {
        if( $val=='false' ) {
            return '<a href="javascript:;" onclick="_get_rpcnotify_num(this)" >'.app::get('base')->_('未读').'</a>';
        } else {
            return app::get('base')->_('已读');
        }
    }
}
