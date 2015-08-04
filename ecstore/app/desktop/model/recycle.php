<?php

 
class desktop_mdl_recycle extends dbeav_model{

    function  save(&$data,$mustUpdate = null){
        $return = parent::save($data,$mustUpdate);
    }
    function modifier_app_key($app_key){
        $app = app::get('base')->model('apps');
        $rows = $app->getList('app_name',array('app_id'=>$app_key));
        return $rows[0]['app_name'];
    }
    function get_item_type(){
        $rows = $this->db->select('select distinct(item_type) from '.$this->table_name(true).' ');
        return $rows;
    }
    /**
     * 重写getList方法
     * @param string column
     * @param array filter
     * @param int offset
     * @param int limit
     * @param string order by
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $recycle_permission = $filter['recycle_permission'];
        unset($filter['recycle_permission']);
        if($recycle_permission && is_array($recycle_permission))
        {
            if(strpos($cols,'permission') === false) $cols.=',permission ';
        }
        $ids = array();
        $res = app::get('base')->model('apps')->getList('app_id',array('status'=>'active'));
        foreach ($res as $res_v) {
            $ids[] = $res_v['app_id'];
        }
        $filter['app_key|in'] = $ids;
        $data = parent::getList($cols, $filter, $offset, $limit, $orderType);
        if($recycle_permission && is_array($recycle_permission))
        {
            $aTmp = array();
            $menus = $this->app->model('menus');
            $recycle = $this->app->model('recycle');
            foreach($data as $k => $v)
            {
                $per_row = $menus->getList('menu_id',array('menu_type' =>'permission','permission'=>$v['permission']));
                if(!$per_row || in_array($v['permission'],$recycle_permission)) $aTmp[$k] = $v;
            }
            return $aTmp;
        }
        else
        {
            return $data;
        }
    }
}
