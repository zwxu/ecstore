<?php

 

class b2c_mdl_delivery extends dbeav_model{
    var $has_many = array(
        'delivery_items'=>'delivery_items',
        'orders'=>'order_delivery:contrast:delivery_id^dly_id',
    );

    var $defaultOrder = array('t_begin','DESC');

	public function insert(&$data)
	{
		$info_object = kernel::service('sensitive_information');
		if(is_object($info_object)) $info_object->opinfo($data,'b2c_mdl_delivery',__FUNCTION__);
		return parent::insert($data);
    }
	
    function save(&$sdf,$mustUpdate = null){
        if(!isset($sdf['orders'])){
            $sdf['orders'] = array(
                                array(
                                    'order_id' => $sdf['order_id'],
                                    'items' => $sdf['delivery_items'],
                                )
                            );
        }
        $tmpvar = $sdf['orders'];
        foreach($tmpvar as $k => $row){
            $sdf['orders'][$k]['dlytype'] = 'delivery';
            $sdf['orders'][$k]['dly_id'] = $sdf['delivery_id'];
        }
        unset($tmpvar);
        if(parent::save($sdf)){
            //一张发货单多个订单
            /*$oOrder = &$this->app->model('orders');
            foreach($sdf['orders'] as $order){
                if($sdf['order_id']){
                    $sdf_order = $oOrder->dump($order['order_id'],'*',array('order_items'=>'*'));
                    if($sdf_order['ship_status'] == 1){
                        continue;
                    }
                    //todo 订单是否完全退货 
                    $data['ship_status'] = 1;
                    
                    $data['order_id'] = $sdf['order_id'];
                    $filter['order_id'] = $sdf['order_id'];
                    $orders = &$this->app->model('orders');
                    $orders->update($data, $filter);
                }
            }*/
            return true;
        }
        return false;
    }

    function gen_id(){
        $sign = '1'.date("Ymd");
        /*$sqlString = 'SELECT MAX(delivery_id) AS maxno FROM sdb_b2c_delivery WHERE delivery_id LIKE \''.$sign.'%\'';
        $aRet = $this->db->selectrow($sqlString);
        if(is_null($aRet['maxno'])) $aRet['maxno'] = 0;
        $maxno = substr($aRet['maxno'], -6) + 1;
        if ($maxno==1000000){
            $maxno = 1;
        }*/
        while(true)
        {
            $microtime = utils::microtime();
            mt_srand($microtime);
            $randval = substr(mt_rand(), 0, -3) . rand(100, 999);
            
            $aRet = $this->db->selectrow( "SELECT COUNT(*) as c FROM sdb_b2c_delivery WHERE delivery_id='" . ($sign.$randval) . "'" );
            if( !$aRet['c'] )
                break;
        }
		return $sign.$randval;
        //return $sign.substr("00000".$maxno, -6);
    }
    
    /**
     * 得到最新的发货单
     * @params int 最新的数量，条数
     * @return array 数据数组
     */
    public function getLatestDelivery($number)
    {
        return $this->getList('*', array(), 0, $number, 't_begin DESC');
    }
    
    public function modifier_member_id($row)
    {
        $obj_members = $this->app->model('members');
        $arr_member = $obj_members->dump($row, '*', array(':account@pam'=>array('*')));
        
        return $arr_member['pam_account']['login_name'] ? $arr_member['pam_account']['login_name'] : app::get('b2c')->_('顾客');
    }
    
    public function modifier_money($row)
    {
        $app_ectools = app::get('ectools');
        $row = $app_ectools->model('currency')->changer_odr($row,null,false,false,$this->app->getConf('system.money.decimals'),$this->app->getConf('system.money.operation.carryset'));
        
        return $row;
    }
	
	public function modifier_delivery($row)
    {
        $obj_dlytype = $this->app->model('dlytype');
		$arr_dlytype = $obj_dlytype->dump($row, 'dt_name');
        
        return $arr_dlytype['dt_name'] ? $arr_dlytype['dt_name'] : '-';
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
		$arr_delivery = parent::getList($cols, $filter, $offset, $limit, $orderType);
		$info_object = kernel::service('sensitive_information');
		if(is_object($info_object)) $info_object->opinfo($arr_delivery,'b2c_mdl_delivery',__FUNCTION__);
		return $arr_delivery;
	}
}
