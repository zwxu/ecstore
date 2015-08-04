<?php

class scorebuy_business_activity
{
        
    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }//En

    //保存积分换购活动申请
    public function addBusinessActivity($data){       
        $businessActivity = app::get('scorebuy')->model('scoreapply');
        $item['gid'] = $data['gid'];
        $item['cat_id'] = $data['cat_id'];
        $item['aid'] = $data['aid'];
        $item['price'] = $data['price'];
        $item['score'] = $data['score'];
        $item['isMemLv'] = $data['isMemLv'];
        $item['last_price'] = isset($data['last_price'])?$data['last_price']:$data['price'];
        $item['nums'] = $data['nums'];
        $item['remainnums'] = $data['nums'];
        $item['personlimit'] = $data['personlimit'];
        $item['member_id'] = $data['member_id'];
        $item['store_id'] = $data['store_id'];
        $item['status'] = 1;
        $item['last_midifity'] = time();
        $item['image_codeid'] = $data['image_codeid'];
        
        if(isset($data['id'])){
            $item['id'] = $data['id'];
            if($item['isMemLv'] == 0){
                $memLvScoreObj = app::get('scorebuy')->model('memberlvscore');
                $memLvScoreObj->delete(array('aid'=>$item['id'],'gid'=>$item['gid']));
            }
            $result = $businessActivity->save($item);
        }else{
            $result = $businessActivity->insert($item);
        }
        return $result;
    }

    //保存会员积分和价格
    public function addMemLvScore($data){
        $businessActivity = app::get('scorebuy')->model('memberlvscore');
        $item = array();
        $item['aid'] = $data['aid'];
        $item['gid'] = $data['gid'];
        $item['level_id'] = $data['level_id'];
        $item['score'] = $data['score'];
        $item['price'] = $data['price'];
        $item['last_price'] = isset($data['last_price'])?$data['last_price']:$data['price'];

        $result = $businessActivity->dump(array('aid'=>$item['aid'],'gid'=>$item['gid'],'level_id'=>$item['level_id']),'*');

        if($result || !empty($result)){
            $item['id'] = $result['id'];
        }

        return $businessActivity->save($item);
    }

    public function checkPersonLimit($num,$member_id,$aid,$msg){
        $applyObj = app::get('scorebuy')->model('scoreapply');
        $apply = $applyObj->dump(array('id'=>$aid),'personlimit');
        $canBuyNum = $apply['personlimit'];
        if($canBuyNum != ''){
            $orderObj = app::get('b2c')->model('orders');
            $Ofilter = array(
                            'member_id'=>$member_id,
                            'act_id'=>$aid,
                            'order_type'=>'score',
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