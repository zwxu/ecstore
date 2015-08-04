<?php


/**
 * 这个类用于获取分析图形的数据的基类
 * 
 * @version 0.1
 * @package ectools.lib.analysis
 */
class ectools_analysis_base 
{
	/**
	 * 获取统计图形数据
	 * @param array 过滤条件
	 * @return array data
	 */
    public function fetch_graph_data($params) 
    {
        $analysis_info = app::get('ectools')->model('analysis')->select()->columns('*')->where('service = ?', $params['service'])->instance()->fetch_row();
        if(empty($analysis_info))   return array('categories'=>array(), 'data'=>array());
        $obj = app::get('ectools')->model('analysis_logs')->select()->columns('target, flag, value, time')->where('analysis_id = ?', $analysis_info['id']);
        $obj->where('target = ?', $params['target']);
        $obj->where('time >= ?', strtotime(sprintf('%s 00:00:00', $params['time_from'])));
        $obj->where('time <= ?', strtotime(sprintf('%s 23:59:59', $params['time_to'])));
        if(isset($this->_params['type']))   $obj->where('type = ?', $params['type']);
        $rows = $obj->instance()->fetch_all();
        
        for($i=strtotime($params['time_from']); $i<=strtotime($params['time_to']); $i+=($analysis_info['interval'] == 'day')?86400:3600){
            $time_range[] = ($analysis_info['interval'] == 'day') ? date("Y-m-d", $i) : date("Y-m-d H", $i);
        }
        
        $logs_options = kernel::single($params['service'])->logs_options;
        $target = $logs_options[$params['target']];
        if(is_array($target['flag']) && count($target['flag'])){
            foreach($target['flag'] AS $k=>$v){
                foreach($time_range AS $date){
                    $data[$v][$date] = 0;
                }
            }
        }else{
            foreach($time_range AS $date){
                $data['全部'][$date] = 0;
            }
        }

        foreach($rows AS $row){
            $date = ($analysis_info['interval'] == 'day') ? date("Y-m-d", $row['time']) : date("Y-m-d H", $row['time']);
            $flag_name = $target['flag'][$row['flag']];
            if($flag_name){
                $data[$flag_name][$date] = $row['value'];
            }else{
                $data['全部'][$date] = $row['value'];
            }
        }        

        return array('categories'=>$time_range, 'data'=>$data);
    }//End Function

}//End Class
