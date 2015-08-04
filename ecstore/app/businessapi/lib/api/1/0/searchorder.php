<?php



/**
 * b2c aftersales interactor with center
 */
class businessapi_api_1_0_searchorder
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
     * 搜索订单
     * @param mixed sdf结构
     * @param object handle object
     * @return mixed 返回订单结果
     */
	 public function search_order(&$sdf,&$obj){
     
	    $status=array('TRADE_ACTIVE'=>'active','TRADE_CLOSED'=>'dead','TRADE_FINISHED'=>'finish');
        //有order_id查询单条，没有查询所有
	    if(!empty($sdf['order_id'])){
            $filter['order_id'] = $sdf['order_id'];
		  }else{

            if(!empty($sdf['store_id'])){
			         $filter['store_id']=$sdf['store_id'];
		        }         
				if(!empty($sdf['start_time'])&&!empty($sdf['end_time'])){
				    if(strtotime($sdf['start_time'])>strtotime($sdf['end_time'])){
					    $obj->send_user_error(app::get('business')->_('起始时间不能大于结束时间！'));
					}
				}
				if(!empty($sdf['start_time'])){
			        $filter['last_modified|bthan']=strtotime($sdf['start_time']);
			    }
			    if(!empty($sdf['end_time'])){
			        $filter['last_modified|sthan']=strtotime($sdf['end_time']);
			    }
				
			    if(!empty($sdf['status'])){
			        $filter['status']=$status[$sdf['status']];
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
		}

		if(!empty($sdf['columns'])){
			$arr_cols=explode('|',$sdf['columns']);
		}
     
		$mdl_order = app::get('business')->model('orders');

		if(!empty($sdf['order_id'])){

		    $subsdf = array('order_pmt'=>array('*'));
            $order = $mdl_order->dump($sdf['order_id'], '*', $subsdf);
			if(empty($order)){
			    $obj->send_user_error(app::get('business')->_('没有找到相应的订单'));
			}else{

				$arr_page['limit']=1;
				$arr_page['cPage']=1;
				$arr_page['counts']=1;
			    $sdf_orders[]=$order;
			}

		}else{
			  $rows=$mdl_order->count($filter);
			  $nPage=ceil($rows/$counts);
			  if($nPage< $page){
			      $page=$nPage;	
			  }
			  $arr_page['limit']=intval($counts);
              $arr_page['cPage']=intval($page);
			  $arr_page['counts']=intval($rows);
		$sdf_orders=$mdl_order->getList('order_id',$filter,($page-1)*$counts,$counts);	
		   
        $subsdf = array('order_pmt'=>array('*'));
        foreach ($sdf_orders as &$arr_order)
        {
            $arr_order = $mdl_order->dump($arr_order['order_id'], '*', $subsdf);
        }
		  
		}
		$order_status = array('active'=>'TRADE_ACTIVE','dead'=>'TRADE_CLOSED','finish'=>'TRADE_FINISHED');
        $pay_status = array(0=>'PAY_NO',1=>'PAY_FINISH',2=>'PAY_TO_MEDIUM',3=>'PAY_PART',4=>'REFUND_PART',5=>'REFUND_ALL');
        $ship_status = array(0=>'SHIP_NO',1=>'SHIP_FINISH',2=>'SHIP_PART',3=>'RESHIP_PART',4=>'RESHIP_ALL');
       
		//判断是否查到订单
		if(empty($sdf_orders)){
		    $obj->send_user_error(app::get('business')->_('没有找到相应的订单！'));  
		}else{
			
			foreach($sdf_orders as $key=>&$orders){

				$mdl_member=$this->app_b2c->model('members');
				$member=$mdl_member->get_member_info($orders['member_id']);
				$member_info=$mdl_member->getList('name,addr,mobile,email,zip',array('member_id'=>$orders['member_id']),0,1);
                
				if($orders['consignee']['area'])
				{
					$tmp1 = explode(':',$orders['consignee']['area']);
					$area = explode('/',$tmp1[1]);
				}
				    if(!empty($orders['order_pmt'])){
						foreach($orders['order_pmt'] as &$order_pmt){
							$promotion_details[]=array(
								'promotion_name'=>$order_pmt['pmt_describe'],
								'promotion_fee'=>$order_pmt['pmt_amount'],
								'pmt_id'=>$orders['order_id'],
						);
                    }
		      }
			       //重组返回数据
					$returndata[$key] = array(
						'tid'=>$orders['order_id'],
						'store_id'=>$orders['store_id'],
						'created'=>intval($orders['createtime']),
						'modified'=>intval($orders['last_modified']),
						'confirm_time'=>$orders['confirm_time']!=null?intval($orders['confirm_time']):null,
						'status'=>$order_status[$orders['status']],
						'pay_status'=>$pay_status[$orders['pay_status']],
						'ship_status'=>$ship_status[$orders['ship_status']],
						'has_invoice'=>$orders['is_tax'],
						'invoice_title'=>$orders['tax_title'],
						'invoice_fee'=>$orders['cost_tax'],
						'total_goods_fee'=>(is_null($orders['cost_item'])?0:$orders['cost_item']),
						'total_trade_fee'=>(is_null($orders['cur_amount'])?0:$orders['cur_amount']),
						'discount_fee'=>(is_null($orders['discount'])?0:$orders['discount']),
						'goods_discount_fee'=>$orders['pmt_goods'],
						'order_discount_fee'=>$orders['pmt_order'],
						'promotion_details'=>$promotion_details,
						'payed_fee'=>(is_null($orders['payed'])?0:$orders['payed']),
						'currency'=>$orders['currency'],
						'currency_rate'=>$orders['cur_rate'],
						'total_currency_fee'=>(is_null($orders['total_amount'])?0:$orders['total_amount']),
						'buyer_obtain_point_fee'=>$orders['score_g'],
						'point_fee'=>$orders['score_u'],
						'total_weight'=>(is_null($orders['weight'])?0:$orders['weight']),
						'shipping_tid'=>$orders['shipping']['shipping_id'],
						'shipping_type'=>$orders['shipping']['shipping_name'],
						'shipping_fee'=>(is_null($orders['shipping']['cost_shipping'])?0:$orders['shipping']['cost_shipping']),
						'shipping_time'=>$orders['consignee']['r_time'],
						'is_cod'=>($orders['is_delivery ']=='Y')?"true":"false",
						'is_protect'=>$orders['shipping']['is_protect'],
						'protect_fee'=>$orders['shipping']['cost_protect'],
						'receiver_name'=>$orders['consignee']['name'],
						'receiver_email'=>$orders['consignee']['email'],
						'receiver_state'=>$area[0],
						'receiver_city'=>$area[1],
						'receiver_district'=>$area[2],
						'receiver_address'=>$orders['consignee']['addr'],
						'receiver_zip'=>$orders['consignee']['zip'],
						'receiver_mobile'=>$orders['consignee']['mobile'],
						'receiver_phone'=>$orders['consignee']['telphone'],
						'payment_type'=>$orders['payinfo']['pay_app_id'],
						'pay_cost'=>$orders['payinfo']['cost_payment'],
						'pay_memo'=>$orders['memo'],
						'orders_number'=>$orders['itemnum'],
						'buyer_uname'=>$member['uname'],
						'buyer_name'=>$member_info[0]['name'],
						'buyer_address'=>$member_info[0]['addr'],
						'buyer_mobile'=>$member_info[0]['mobile'],
						'buyer_email'=>$member_info[0]['email'],
						'buyer_zip'=>$member_info[0]['zip'],
					);
					
					$payment=$this->get_payment($orders['order_id']);
                    $returndata[$key]['payment']=$payment[0];

					$obj_order_items = $this->app_b2c->model('order_items');
					$tmp = $obj_order_items->getList('*', array('order_id'=>$orders['order_id']));
					if ($tmp)
						$order_items = $tmp;
					else
						$order_items = array();
					$returndata[$key]['orders']=$order_items;  

					if(!empty($arr_cols)){
					
						foreach($returndata[$key] as $dk=>$dv){
							if(in_array($dk,$arr_cols)){
								$data[$key][$dk]=$dv;
							}
						}
					}else{
						$data[$key]=$returndata[$key];
					}	
			}   
		}
         $return['page']=$arr_page;
		 $return['info']=$data;
		 echo "执行成功";
         return $return;
	    
	 }
	 private function get_payment($order_id){
	 
	     $sql="select payment.payment_id,payment.cur_money from sdb_ectools_payments as payment join sdb_ectools_order_bills as bills on payment.payment_id=bills.bill_id and bills.bill_type='payments' where payment.status='succ' and bills.rel_id=".$order_id;

		 if(!empty($order_id))
			 $data=kernel::database()->select($sql);

		 return $data;
	 }
}

