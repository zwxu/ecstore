<?php
class spike_auto_pay {
    function exec_auto(){
        $payTime = app::get('b2c')->getConf('site.spike.payed_time');
        if($payTime == '')    $payTime = 15;
        $payTime = $payTime * 60;//转化成秒
        $nowTime = time();
        $filter = array(
                    'status'=>'active',
                    'order_type'=>'spike',
                    'pay_status'=>0,
                    'createtime|sthan'=>($nowTime-$payTime)
                 );
        $orderObj = app::get('b2c')->model('orders');
        $applyObj = app::get('spike')->model('spikeapply');
        $orderItemObj = app::get('b2c')->model('order_items');
        $orders = $orderObj->getList('order_id,itemnum,act_id',$filter);
        
        foreach($orders as $k=>$v){
            if($this->docancel($v['order_id'])){
                $apply = $applyObj->dump(array('id'=>$v['act_id']),'gid,nums,remainnums');
                if($apply['nums'] != ''){
                    $goodsnum = $orderItemObj->dump(array('goods_id'=>$apply['gid']),'(nums-sendnum) as gnum');
                    $sql = "update sdb_spike_spikeapply set remainnums=".($apply['remainnums']+$goodsnum['gnum']).' where id='.$v['act_id'];
                    $applyObj->db->exec($sql);

                    
                    $gsql = "update sdb_b2c_goods set store_freeze=".($apply['remainnums']+$goodsnum['gnum']).' where goods_id='.$apply['gid'];
                    $applyObj->db->exec($gsql);
                }
                echo '订单取消成功<br>';
            }
        }
    }

    /**
     * 订单取消
     * @params string order id
     * @return null
     */
    public function docancel($order_id){
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_id,'',$message)){
           return false;
        }
        
        $sdf['order_id'] = $order_id;
        $sdf['opname'] = '定时脚本';
        $b2c_order_cancel = kernel::single("spike_order_cancel");
        if ($b2c_order_cancel->generate($sdf, $message)){
            //ajx crm
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id'] = $order_id;
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
         
            $orderObj = app::get('b2c')->model('orders');
            $order_info = $orderObj->dump(array('order_id'=>$order_id),'act_id,order_type,itemnum');
            switch($order_info['order_type']){
                case 'spike':
                    $buyMod = app::get('spike')->model('memberbuy');
                    $applyObj = app::get('spike')->model('spikeapply');
                    $apply = $applyObj->dump(array('id'=>$order_info['act_id']),'aid,gid,remainnums,nums');
                    if($apply){
                      $buyMod->update(array('effective'=>'false'),array('order_id'=>$order_id));
                    }
                    break;
            }
            //end

            return true;
        }
        else{
            return false;
        }
    }
}