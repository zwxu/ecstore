<?php
class commenterprise_comment_setting{

    function __construct(&$app){
        $this->app = $app;
    }

    function get_Html(){
        $render = $this->app->render();
        $model = app::get('b2c')->model('comment_goods_type');
        $row = $model->getList('*');
        foreach($row as &$val){
            $val['addon'] = unserialize($val['addon']);	
        }
        $render->pagedata['status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status')  : 'on';
        $render->pagedata['member_point'] = app::get('b2c')->getConf('member_point') ? app::get('b2c')->getConf('member_point')  : 0;
        $render->pagedata['point_status'] = app::get('b2c')->getConf('site.get_policy.method');
        $render->pagedata['point_type'] = $row;
        $model = app::get('b2c')->model('comment_goods_type');
        $row = $model->getList('type_id',array(),0,1,'type_id desc');
        $render->pagedata['last_id'] = $row[0]['type_id'];
        return $render->fetch('admin/member/discuss_setting.html');
    }

    function save_setting($aData){
        if(!$aData['point_type_name']) return ;
        app::get('b2c')->setConf('goods.point.status',$_POST['comment_point_status']);
        app::get('b2c')->setConf('member_point',$_POST['member_point']);
        $model = app::get('b2c')->model('comment_goods_type');
        $row = $model->getList('type_id',array(),0,1,'type_id desc');
        $last_id = $row[0]['type_id'];
        $model->delete(array());
        //
        $newdata=array();
        foreach ($aData['point_type_name'] as $key => $value) {
            if($aData['total_point'] == $key){
                $value['is_total_point'] = 'on';
            }else{
                $value['is_total_point'] = 'off';
            }
          
            if(isset($aData['point_type_desc'][$key])){
                $value['description'] = $aData['point_type_desc'][$key];
            }
            
            $newdata['point_type_name'][] = $value;
        }
        foreach($newdata['point_type_name'] as $key=>$val){
            if(!$val['name']){
                if($val['name'] != '0')continue;
            }
            $val['type_id'] = $key+1;
            $arr['is_total_point'] = $val['is_total_point'];
            $arr['description'] = $val['description'];
            $val['addon'] = serialize($arr);
            $model->insert($val);
        }
    }
}
?>