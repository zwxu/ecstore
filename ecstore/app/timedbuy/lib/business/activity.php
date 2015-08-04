<?php

class timedbuy_business_activity
{
        
    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }//En

    public function addBusinessActivity($data){       
        $businessActivity = $this->app->model('businessactivity');
		$cat_id = app::get('b2c')->model('goods')->getList('cat_id',array('goods_id'=>$data['gid']));
		$item['cat_id'] = $cat_id[0]['cat_id'];
        $item['id'] = $data['id'];
        $item['gid'] = $data['gid'];
        $item['aid'] = $data['aid'];
        $item['price'] = $data['price'];
        $item['nums'] = $data['nums'];
        $item['remainnums'] = $data['nums'];
        $item['presonlimit'] = $data['presonlimit'];
        $item['member_id'] = $data['member_id'];
		$item['store_id'] = $data['store_id'];
        $item['status'] = 1;
        $item['last_midifity'] = time();
        $item['discription'] = $data['discription'];
        
        return $businessActivity->save($item);     
    }
}