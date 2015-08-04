<?php

 

class ectools_mdl_payments extends dbeav_model{
    
    var $has_many = array(
        'orders'=>'order_bills@ectools:contrast:payment_id^bill_id',
    );
    
    var $defaultOrder = array('t_payed','DESC');
    
    /**
     * 得到唯一的payment id
     * @params null
     * @return string payment id
     */
    public function gen_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $payment_id = time().str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->dump($payment_id, 'payment_id');
        }while($row);
        return $payment_id;
    }
    
    /**
     * 模板统一保存的方法
     * @params array - 需要保存的支付信息
     * @params boolean - 是否需要强制保存
     * @return boolean - 保存的成功与否的进程
     */
    public function save($data,$mustUpdate = null)
    {
        // 异常处理    
        if (!isset($data) || !$data || !is_array($data))
        {
            trigger_error(app::get('ectools')->_("支付单信息不能为空！"), E_USER_ERROR);exit;
        }
        
        $sdf = array();
       
        // 支付数据列表
        $background = true;//后台 todo

        $payment_data = $data;
        $sdf_payment = parent::dump($data['payment_id'],'*');

        if ($sdf_payment) 
        {
            if($sdf_payment['status'] == $data['status']
                || ($sdf_payment['status'] != 'progress' && $sdf_payment['status'] != 'ready')){
                return true;
            }    
            if($data['currency'] && $sdf_payment['currency'] != $data['currency']){
                return false;
            }
        }

        if($sdf_payment){
            $sdf = array_merge($sdf_payment, $data);
        }else{
            $sdf = $data;
            $sdf['status'] = $sdf['status'] ? $sdf['status'] : 'ready';
        }
        
        // 保存支付信息（可能是退款信息）
        $is_succ = parent::save($sdf);
        
        return $is_succ;
    }
    
    /**
     * 得到所有的支付账号
     * @param null
     * @return null
     */
    public function getAccount()
    {
        $query = 'SELECT DISTINCT bank, account FROM ' . $this->table_name(1) .' WHERE status="succ"';
        return $this->db->select($query);
    }
    
    /**
     * 得到订单相应的支付成功单据
     * @param string order id
     * @return null
     */
    public function get_payments_by_order_id($order_id=0)
    {
        if (!$order_id)
        {
            return array();
        }
        
        $rows = $this->db->select('SELECT payments.* 
                                        FROM '.$this->table_name(1).' AS payments 
                                        INNER JOIN ' . kernel::database()->prefix.$this->app->app_id . '_order_bills AS bills ON bills.bill_id=payments.payment_id 
                                        WHERE bills.rel_id=' . $order_id . " AND status='succ'");
        return $rows;
    }
    
    /**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                $columns[$k] = $v['label'];
            }
        }
        
        // 添加额外的
        $ext_columns = array('rel_id'=>$this->app->_('订单号'));
        
        return array_merge($columns, $ext_columns);
    }
	
	public function _filter($filter,$tableAlias=null,$baseWhere=null){
		if(!$filter)
			return parent::_filter($filter);

		if (array_key_exists('rel_id', $filter))
		{
			$obj_order_bills = $this->app->model('order_bills');
			$bill_filter = array(
				'rel_id|has'=>$filter['rel_id'],
				'bill_type'=>'payments',
			);
			$row_order_bills = $obj_order_bills->getList('bill_id',$bill_filter);
			$arr_member_id = array();
			if ($row_order_bills)
			{
				$arr_order_bills = array();
				foreach ($row_order_bills as $arr)
				{
					$arr_order_bills[] = $arr['bill_id'];
				}
				$filter['payment_id|in'] = $arr_order_bills;				
			}
			else
			{
				$filter['payment_id'] = 'a';
			}
			unset($filter['rel_id']);
		}

        $filter = parent::_filter($filter);
        return $filter;
    }
    
    /**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_member_id($row)
    {
        if (is_null($row) || empty($row))
        {
            return app::get('ectools')->_('未知会员或非会员');
        }
        
        $obj_pam_account = app::get('pam')->model('account');
        $arr_pam_account = $obj_pam_account->getList('login_name', array('account_id' => $row));
        
        if ($arr_pam_account[0])
            return $arr_pam_account[0]['login_name'];
        else
            return app::get('ectools')->_('未知会员或非会员');
    }
    
    /**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_op_id($row)
    {
        if (is_null($row) || empty($row))
        {
            return app::get('ectools')->_('未知操作员');
        }
        
        $obj_pam_account = app::get('pam')->model('account');
        $arr_pam_account = $obj_pam_account->getList('login_name', array('account_id' => $row));
        
        if ($arr_pam_account[0])
            return $arr_pam_account[0]['login_name'];
        else
            return app::get('ectools')->_('未知操作员');
    }
    
    /**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_pay_app_id($row)
    {
        $obj_payment_cfgs = $this->app->model('payment_cfgs');
        $arr_payment_cfgs = $obj_payment_cfgs->getPaymentInfo($row);
        
        if ($arr_payment_cfgs)
        {
            return $arr_payment_cfgs['app_name'];
        }
        else
            return 'app_name';
    }
	
	/**
	 * 支付货币值
	 */
	public function modifier_cur_money($row)
    {
		$currency = $this->app->model('currency');
		$filter = array('payment_id' => $this->pkvalue);
        $tmp = $this->getList('currency', $filter);		
		$arr_cur = $currency->getcur($tmp[0]['currency']);
		$row = $currency->formatNumber($row,false,false);
		
		return $arr_cur['cur_sign'] . $row;
    }
	
	/**
	 * 支付机器的ip
	 */
	public function modifier_ip($row)
	{
		if (is_null($row) || empty($row))
        {
            return '-';
        }
		
		return $row;
	}

    /*
    * @method : get_all_payments_by_order_id
    * @description : 根据订单id获取所有支付单信息
    * @params :
    *       $order_id : 订单id
    * @return : array
    * @author : zlj
    * @date : 2013-6-3 15:11:58
    */
    public function get_all_payments_by_order_id($order_id=0){
        if (!$order_id){
            return array();
        }

        $rows = $this->db->select('SELECT payments.* FROM ' . $this->table_name(1) . ' AS payments INNER JOIN ' . kernel::database()->prefix . $this->app->app_id . '_order_bills AS bills ON bills.bill_id=payments.payment_id WHERE bills.rel_id=' . $order_id);
        return $rows;
    }
    
    /**
     * 重写订单导出方法
     * @param array $data
     * @param array $filter
     * @param int $offset
     * @param int $exportType
     */
    public function fgetlist_csv( &$data,$filter,$offset,$exportType =1 ){
        $limit = 100;
        $cols = $this->_columns();
        if(!$data['title']){
            $this->title = array();
            foreach( $this->getTitle($cols) as $titlek => $aTitle ){
                $this->title[$titlek] = $aTitle;
            }
            // service for add title when export
            foreach( kernel::servicelist('export_add_title') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'addTitle') ) {
                        $services->addTitle($this->title);
                    }
                }
            }
            $data['title'] = '"'.implode('","',$this->title).'"';
        }

        if(!$list = $this->getList(implode(',',array_keys($cols)),$filter,$offset*$limit,$limit))return false;
        
        // $data['contents'] = array();
        foreach( $list as $line => $row ){
            // service for add data when export
            foreach( kernel::servicelist('export_add_data') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'addData') ) {
                        $services->addData($row);
                    }
                }
            }
            $rowVal = array();
            foreach( $row as $col => $val ){
                
                if( in_array( $cols[$col]['type'],array('time','last_modify') ) && $val ){
                   $val = date('Y-m-d H:i',$val)."\t";
                }
                if ($cols[$col]['type'] == 'longtext'){
                    if (strpos($val, "\n") !== false){
                        $val = str_replace("\n", " ", $val);
                    }
                }

                if(strlen($val) > 8){
                    $val .= "\t";
                }
                
                if( strpos( (string)$cols[$col]['type'], 'table:')===0 ){
                    $subobj = explode( '@',substr($cols[$col]['type'],6) );
                    if( !$subobj[1] )
                        $subobj[1] = $this->app->app_id;
                    $subobj = &app::get($subobj[1])->model( $subobj[0] );
                    $subVal = $subobj->dump( array( $subobj->schema['idColumn']=> $val ),$subobj->schema['textColumn'] );
                    $val = $subVal[$subobj->schema['textColumn']]?$subVal[$subobj->schema['textColumn']]:$val;
                }

                if( array_key_exists( $col, $this->title ) )
                    $rowVal[] = addslashes(  (is_array($cols[$col]['type'])?$cols[$col]['type'][$val]:$val ) );
            }
            $data['contents'][] = '"'.implode('","',$rowVal).'"';
        }
        return true;

    }    
    
    function getTitle(&$cols){
        $title = array();
        foreach( $cols as $col => $val ){
            if( !$val['deny_export'] )
            $title[$col] = $val['label'].'('.$col.')';
        }
        return $title;
    }
    
    public function fgetlistitems_csv( &$data,$filter,$offset,$exportType =1 ){
        $limit = 100;
        if(!$data['title']){
            $data['title'] = '"单号","订单ID","商品编号","商品名称","购买量","原始单价","实际单价","总金额","分类","抽成比率","运费抽成"';
        }
		$where = $this->_filter($filter);
		$where = str_replace('`sdb_ectools_payments`', 'p', $where);
        $sql = "
select p.payment_id,oi.order_id,g.bn,g.name,oi.nums,g.mktprice,oi.price,oi.amount,gc.cat_name,gc.profit_point 
from sdb_ectools_payments p 
left join sdb_ectools_order_bills ob on p.payment_id=ob.bill_id
left join  sdb_b2c_order_items oi on ob.rel_id=oi.order_id
left join sdb_b2c_orders o on oi.order_id=o.order_id  
left join sdb_b2c_goods g on oi.goods_id=g.goods_id 
left join sdb_b2c_goods_cat gc on g.cat_id=gc.cat_id 
where {$where}
        ";
        if(!$list = $this->db->selectLimit($sql,$limit,$offset*$limit))return false;
        // $data['contents'] = array();
        foreach( $list as $line => $row ){
            $rowVal = array();
            foreach( $row as $col => $val ){

                if(strlen($val) > 8 && eregi("^[0-9]+$",$val)){
                    $val .= "\r";
                }
                $rowVal[] = addslashes(  $val  );
            }
            $rowVal[] = app::get('b2c')->getConf('member.profit');
            $data['contents'][] = '"'.implode('","',$rowVal).'"';
        }
        return true;

    } 
}
