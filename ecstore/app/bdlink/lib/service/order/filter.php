<?php
class bdlink_service_order_filter{
    function extend_filter(&$filter){

        if($filter['refer_id'] || $filter['refer_url'] || $filter['refer_time'] || $filter['c_refer_id'] || $filter['c_refer_url'] || $filter['c_refer_time'])
        {
            $bdlink = kernel::single('bdlink_link');
            $bd_filter['target_type'] = 'order';
            $bd_filter['refer_id|has'] = $filter['refer_id'];
            $bd_filter['refer_url|has'] = $filter['refer_url'];
            $bd_filter['refer_time'] = $filter['refer_time'];
            $bd_filter['c_refer_id|has'] = $filter['c_refer_id'];
            $bd_filter['c_refer_url|has'] = $filter['c_refer_url'];
            $bd_filter['c_refer_time'] = $filter['c_refer_time'];
            unset($filter['refer_id']);
            unset($filter['refer_url']);
            unset($filter['refer_time']);
            unset($filter['c_refer_id']);
            unset($filter['c_refer_url']);
            unset($filter['c_refer_time']);
            foreach($bd_filter as $k=>$v){
                if(empty($v)){
                    unset($bd_filter[$k]);
                }
            }
            $row = $bdlink->getList('target_id',$bd_filter);
            $data = array();
            foreach((array)$row as $v){
                $data[] = $v['target_id'];
            }
            if($data){
                $filter['order_id'] = $data;
            }
            else
            {
                 $filter['order_id'] = -1;
            }
        }

    }
}
