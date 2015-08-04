<?php



/**
 * b2c aftersales interactor with center
 */
class businessapi_api_1_0_searchrefund
{
    /**
     * app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = app::get('business');
		$this->app_b2c=app::get('b2c');

     }
    /**
     * 查询退款单
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回退款单数据
     */
	 public function search_refund(&$sdf,&$obj){

	     if(!empty($sdf['columns'])){
		     $arr_cols=explode('|',$sdf['columns']);
					 
		 }
		 //起始时间不能大于结束时间
		 if(!empty($sdf['start_time'])&&!empty($sdf['end_time'])){
		     if(strtotime($sdf['start_time'])>strtotime($sdf['end_time'])){
		        $obj->send_user_error(app::get('business')->_('起始时间不能大于结束时间！'));
		     }
		 }
		 if(!empty($sdf['start_time'])){
		       $filter['start_time']=strtotime($sdf['start_time']);
		 }
		 if(!empty($sdf['end_time'])){
		       $filter['end_time']=strtotime($sdf['end_time']);
		 }
		 if(!empty($sdf['page'])&&$sdf['page']>0){
		     $page=$sdf['page'];
		 }else{
		     $page=1;
		 }
		 if(!empty($sdf['counts'])&&$sdf['counts']>0){
		     $counts=$sdf['counts'];
		 }else{
		     $counts=20;
		 }
		 if(!empty($sdf['status'])){
		     $filter['status']=$sdf['status'];
		 }
         if(!empty($sdf['refund_id'])){
		     $filter['refund_id']=$sdf['refund_id'];
		 }
         $arr_page['limit']=intval($counts);

		 //查询退款单
	     if(empty($sdf['store_id'])||!empty($sdf['refund_id'])){
			 
			 $arr_refund=$this->get_refunds($filter);
			 if(empty($arr_refund)){
			     $obj->send_user_error(app::get('business')->_('无此退款信息！'));
			 }
             foreach($arr_refund as $key=>&$value){
				     $payments=$this->get_payment_id($value['order_id']);
					 $value['payment_id']=$value['refund_id'];
					 unset($value['order_id']);
					 $value['original_payment_id']=$payments[0]['payment_id'];
			 }
			 if(!empty($sdf['refund_id'])){
			     $arr_page['limit']=1;
		         $arr_page['cPage']=1;
			     $arr_page['counts']=1;
			 }

	     }else{
	      		     
			 $mdl_order=$this->app_b2c->model('orders');
             $refund_orders=$mdl_order->getList('order_id',array('store_id'=>$sdf['store_id'],'pay_status|in'=>array('4','5')));

			 foreach($refund_orders as $order){
				 
			     $refunds=$this->get_refunds_by_order_id($order['order_id'],$filter);
				 if(!empty($refunds)){
					 foreach($refunds as $k=>&$v){
						
						 $v['store_id']=$sdf['store_id'];
						 $payments=$this->get_payment_id($v['order_id']);
						 $v['payment_id']=$v['refund_id'];
						 unset($v['order_id']);
						 $v['original_payment_id']=$payments[0]['payment_id'];
						 $arr_refund[]=$v;
					 }
				 }
			 }
			
	   }

	    if(empty($arr_refund)){
		     $obj->send_user_error(app::get('business')->_('无退款信息！'));
		}
       //分页处理
	    $rows=count($arr_refund);
		$cPage=ceil($rows/$counts);
		if($page>$cPage){
		    $page=$cPage;
		}
            
		    $arr_page['cPage']=intval($page);
            $arr_page['counts']=intval($rows);
		

	    foreach($arr_refund as $key=>$refund){
			     if($key>=($page-1)*$counts&&$key<$page*$counts){
				     $arr_refunds[]=$refund;					 
				 }
			 }

       //返回字段处理 
	   foreach($arr_refunds as $key=>&$value){
		    if(!empty($value['t_begin']))
				$value['t_begin']=intval($value['t_begin']);
    		if(!empty($value['t_payed']))
				$value['t_payed']=intval($value['t_payed']);
			if(!empty($value['t_confirm']))
				$value['t_confirm']=intval($value['t_confirm']);
		   if(!empty($arr_cols)){
			   foreach($value as $dk=>$dv){
				  if(in_array($dk,$arr_cols)){
						$data[$dk]=$dv;
				  }
			   }

			   $value=$data;

           }

		  
	   }
	   $return['page']=$arr_page;
	   $return['info']=$arr_refunds;
	   echo "执行成功";
       return $return;
   }
   //返回退款单
   private function get_refunds_by_order_id($order_id,$filter=array()){
       if (!$order_id){
            return array();
        }
        $sql='SELECT refunds.refund_id,refunds.cur_money,refunds.member_id,refunds.account,refunds.bank,refunds.pay_account,refunds.currency,refunds.paycost,refunds.pay_type,refunds.status,refunds.pay_name,refunds.pay_ver,refunds.op_id,refunds.refund_bn,refunds.pay_app_id,refunds.t_begin,refunds.t_payed,refunds.t_confirm,refunds.memo,refunds.trade_no,bills.rel_id as order_id  FROM ' .kernel::database()->prefix. 'ectools_refunds AS refunds INNER JOIN ' . kernel::database()->prefix . 'ectools_order_bills AS bills ON bills.bill_id=refunds.refund_id WHERE bills.rel_id=' . $order_id.' and refunds.refund_type="1" and bills.bill_type="refunds"';
        if(!empty($filter['start_time']))
			$sql.=" and refunds.t_payed >= ".$filter['start_time'];
		if(!empty($filter['end_time']))
			$sql.=" and refunds.t_payed <= ".$filter['end_time'];
        if(!empty($filter['status']))
			$sql.=" and refunds.status='".$filter['status']."'";
        $data = kernel::database()->select($sql);
	    
        return $data;
   }
   //返回退款单
   private function get_refunds($filter=array()){
            
       $sql="select refunds.refund_id,refunds.cur_money,refunds.member_id,refunds.account,refunds.bank,refunds.pay_account,refunds.currency,refunds.paycost,refunds.pay_type,refunds.status,refunds.pay_name,refunds.pay_ver,refunds.op_id,refunds.refund_bn,refunds.pay_app_id,refunds.t_begin,refunds.t_payed,refunds.t_confirm,refunds.memo,refunds.trade_no,orders.order_id,orders.store_id
             from sdb_ectools_order_bills as bills
             join sdb_ectools_refunds as refunds on bills.bill_id=refund_id and refunds.refund_type='1'
             join sdb_b2c_orders as orders on bills.rel_id=orders.order_id
             where bills.bill_type='refunds' ";
       if(!empty($filter['refund_id'])){
           $sql.=" and bills.bill_id=".$filter['refund_id'];
	   }else{
		   if(!empty($filter['start_time']))
					 $sql.=" and refunds.t_payed >= ".$filter['start_time'];
		   if(!empty($filter['end_time']))
					 $sql.=" and refunds.t_payed <= ".$filter['end_time'];
		   if(!empty($filter['status']))
					 $sql.=" and refunds.status='".$filter['status']."'";
	   }
       $data=kernel::database()->select($sql);
       return $data;
       	
   }
	//返回收款单
	 private function get_payment_id($order_id){
	     $sql="select payments.payment_id from sdb_ectools_order_bills as bills join sdb_ectools_payments as payments on bills.bill_id=payments.payment_id and payments.status='succ'
	          where bills.bill_type='payments' and bills.rel_id=".$order_id;
	     $data=kernel::database()->select($sql);
	     return $data;	
	 }
	
}

