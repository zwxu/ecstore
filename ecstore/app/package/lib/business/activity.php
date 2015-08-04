<?php
class package_business_activity{
    public function __construct($app) {
        $this->app = $app;
    }

    public function addattendactivity(&$data){
        $attendactivity = $this->app->model('attendactivity');
        $item = array();
        $item['id'] = $data['id'];
        if(empty($item['id'])) unset($item['id']);
        $item['name'] = $data['name'];
        $item['gid'] = $data['gid'];
        $item['aid'] = $data['aid'];
        $item['member_id'] = $data['member_id'];
        $item['store_id'] = $data['store_id'];
        $item['amount'] = $data['amount'];
        $item['store'] = $data['store'];
        $item['presonlimit'] = $data['presonlimit'];
        $item['weight'] = $data['weight'];
        $item['score'] = $data['score'];
        $item['status'] = 1;
        $item['intro'] = $data['intro'];
        $item['image'] = $data['image'];
        $item['freight_bear'] = $data['freight_bear'];
        $item['last_midifity'] = time();
        $rs = $attendactivity->save($item);
        if($rs){
            $objImgAttach = app::get('image')->model('image_attach');
            $objImgAttach->delete(array('target_type'=>'package','target_id'=>$item['id']));
            if(!empty($data['goods']['images'])){
                $date = time();
                foreach($data['goods']['images'] as $k=>$v){
                    $indert_data = array('target_type'=>'package','target_id'=>$item['id'],'image_id'=>$v,'last_modified'=>$date);
                    $objImgAttach->insert($indert_data);
                }
            }
        }
        $item['gdlytype'] = $data['gdlytype'];
        $data = $item;
        return $rs;
    }
}