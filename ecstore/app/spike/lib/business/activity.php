<?php
class spike_business_activity
{
        
    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }//En

    public function addBusinessActivity($data){       
        $businessActivity = $this->app->model('spikeapply');
        $item['id'] = $data['id'];
        $item['gid'] = $data['gid'];
        $item['cat_id'] = $data['cat_id'];
        $item['aid'] = $data['aid'];
        $item['price'] = $data['price'];
        $item['last_price'] = isset($data['last_price'])?$data['last_price']:0;
        $item['nums'] = $data['nums'];
        $item['remainnums'] = $data['nums'];
        $item['personlimit'] = $data['personlimit'];
        $item['member_id'] = $data['member_id'];
        $item['store_id'] = $data['store_id'];
        $item['status'] = 1;
        $item['last_midifity'] = time();
        $item['act_desc'] = $data['act_desc'];
        $item['image_codeid'] = $data['image_codeid'];
        
        return $businessActivity->save($item);     
    }

    public function checkPersonLimit($num,$member_id,$aid,$msg){
        $applyObj = app::get('spike')->model('spikeapply');
        $apply = $applyObj->dump(array('id'=>$aid),'personlimit');
        $canBuyNum = $apply['personlimit'];
        if($canBuyNum != ''){
            $orderObj = app::get('b2c')->model('orders');
            $Ofilter = array(
                            'member_id'=>$member_id,
                            'act_id'=>$aid,
                            'order_type'=>'spike',
                            'status'=>array('active','finish')
                        );
            $orders = $orderObj->getList('SUM(itemnum)',$Ofilter);
            if($orders[0]['SUM(itemnum)']){
                $num += $orders[0]['SUM(itemnum)'];
            }
            if($num > $canBuyNum){
                $msg = '您已经超过你可以购买的数量';
                return false;
            }
        }
        
        return true;
    }
}