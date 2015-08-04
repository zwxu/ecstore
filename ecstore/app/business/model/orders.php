<?php

 

class business_mdl_orders extends b2c_mdl_orders{
    var $has_tag = true;
    var $defaultOrder = array('createtime','DESC');
    var $has_many = array(
        'order_objects'=>'order_objects',
        'order_pmt'=>'order_pmt'
    );

    function __construct($app) {
        $this->app = app::get('b2c');
        $this->db = kernel::database();
        
        $this->schema = $this->get_schema();
        $this->metaColumn = $this->schema['metaColumn'];
        $this->idColumn = $this->schema['idColumn'];
        $this->textColumn = $this->schema['textColumn'];
        $this->skipModifiedMark = ($this->schema['ignore_cache']===true) ? true : false;
        if(  !is_array( $this->idColumn ) && array_key_exists( 'extra',$this->schema['columns'][$this->idColumn] )  ){
            $this->idColumnExtra = $this->schema['columns'][$this->idColumn]['extra'];
        }
        $this->use_meta();
    }

    /**
     * 通过会员的编号得到orders标准数据格式
     * @params string member id
     * @params string page number
     * @params array order status
     * @return array sdf 数据
     */
    public function fetchByShop($member_id,$store_id,$nPage,$order_status=array(),$arr_order=null,$arrayorser=null,$limit=10)
    {
        #$limit = $this->app->getConf("selllog.display.listnum");
        if (!$limit) 
            $limit = 10;
        $limitStart = $nPage * $limit;
        if (!$order_status)
            if(isset($store_id))
                $filter['store_id'] = $store_id;
        else
        {
            if(isset($store_id))
                $filter['store_id'] = $store_id;
            if (isset($order_status['pay_status']))
                $filter['pay_status'] = $order_status['pay_status'];
            if (isset($order_status['ship_status']))
                $filter['ship_status'] = $order_status['ship_status'];
            if (isset($order_status['status']))
                $filter['status'] = $order_status['status'];
        }

        //根据订单状态搜索订单 --start 
        if(isset($arr_order)&&!empty($arr_order)){

           foreach($arr_order as $key=>$v){
               $filter[$key] = $v;
           }

        }//--end
        
        //根据搜索条件搜索订单 --start
        if(isset($arrayorser)&&!empty($arrayorser)){
            $temp=array();
            foreach($arrayorser as $item){
                $temp[]=$item['order_id'];
            }
            $filter['order_id|in']=$temp; 
        }//--end

        //echo '<pre>';print_r($filter);exit;
        $sdf_orders = $this->getList('*', $filter, $limitStart, $limit, 'createtime DESC');
        //echo '<pre>';print_r($sdf_orders);exit;
        // 生成分页组建
        $countRd = $this->count($filter);
        $total = ceil($countRd/$limit);
        $current = $nPage;
        $token = '';
        $arrPager = array(
            'current' => $current,
            'total' => $total,
            'token' => $token,
        );
        
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        foreach ($sdf_orders as &$arr_order)
        {
            $arr_order = $this->dump($arr_order['order_id'], '*', $subsdf);
        }
        $arrdata['data'] = $sdf_orders;
        $arrdata['pager'] = $arrPager;
        
        return $arrdata;
    }

}
