<?php
/**
* 该类主要是用来记录后台管理员的操作日志
*/
class operatorlog_logs{

    public function inlogs($memo, $key, $type='normal') {
        $obj = new desktop_user();
        $data['username'] = ($obj->get_login_name())?($obj->get_login_name()):'system_core';
        $data['realname'] = $obj->get_name()?$obj->get_name():'system_core';
        $data['dateline'] = time();
        $data['operate_type'] = $type;
        $data['operate_key'] = $key;
        $data['memo'] = $memo;
//        $data['operate_ip'] = base_request::get_remote_addr();
        app::get('operatorlog')->model('logs')->insert($data);
    }//End Function


    public function exportlog($model) {
        if($model=='orders' || $model=='goods' || $model=='members'){
            $export_name = array('orders'=>'订单','goods'=>'商品','members'=>'会员');
            $memo = '导出'.$export_name[$model].'操作';
            $this->inlogs($memo, $memo, $model);
        }
    }//End Function

    public function importlog($model,$filename) {
        if($model=='goods'){
            $export_name = array('goods'=>'商品');
            $memo = '通过'.$filename.'文件,'.'批量导入'.$export_name[$model];
            $this->inlogs($memo, '导入'.$export_name[$model], $model);
        }
    }//End Function
    
    

}//End Class
