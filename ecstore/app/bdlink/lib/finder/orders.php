<?php

 
class bdlink_finder_orders{
    public $pagelimit = 10;
    
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    var $column_refer_id = '订单首次来源ID';
    var $column_refer_id_order = COLUMN_IN_TAIL;
    function column_refer_id($row){
        return $this->refer($row['order_id'],'refer_id');
    }
    
    var $column_refer_url = '订单首次来源URL';
    var $column_refer_url_order = COLUMN_IN_TAIL;
    function column_refer_url($row){
        return $this->refer($row['order_id'],'refer_url');
    }
    
    var $column_refer_time = '订单首次来源时间';
    var $column_refer_time_order = COLUMN_IN_TAIL;
    function column_refer_time($row){
        return $this->refer($row['order_id'],'refer_time')? date('Y-m-d',$this->refer($row['order_id'],'refer_time')):'-';
    }
    
    var $column_c_refer_id = '订单本次来源ID';
    var $column_c_refer_id_order = COLUMN_IN_TAIL;
    function column_c_refer_id($row){
        return $this->refer($row['order_id'],'c_refer_id');
    }
    
    var $column_c_refer_url = '订单本次来源URL';
    var $column_c_refer_url_order = COLUMN_IN_TAIL;
    function column_c_refer_url($row){
        return $this->refer($row['order_id'],'c_refer_url');
    }
    
    var $column_c_refer_time = '订单本次来源时间';
    var $column_c_refer_time_order = COLUMN_IN_TAIL;
    function column_c_refer_time($row){
        return $this->refer($row['order_id'],'c_refer_time')? date('Y-m-d',$this->refer($row['order_id'],'c_refer_time')):'-';
    }
    
    private function refer($order_id=null,$show=null){
        if(!$order_id || !$show) return ;
        $app = app::get('b2c');
        $services = kernel::servicelist('b2c_save_post_om');
        foreach($services as $service){
            $aData[] = $service->get_arr($order_id,'order');
        }
        
        return $aData[0][$show];
    }
}