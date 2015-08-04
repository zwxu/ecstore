<?php
class b2c_analysis_restore extends base_shell_prototype
{
    public function analysis_day() 
    {
        $base_time = 1381939200;
        $base_day = getdate($base_time);
        $data = $this->fetch_by_interval('day');
        for($day = 152; $day < $base_day['yday']; $day++){
            $run_day = $base_time-86400*($base_day['yday']-$day);
            foreach(kernel::servicelist('ectools_analyse_day') as $service){
                if(!$service instanceof ectools_analysis_interface) continue;
                $service_name = get_class($service);
                $analysis_id = $data[$service_name]['id'];
                $rows = $service->get_logs($run_day);
                if($rows){
                    foreach($rows AS $row){
                        $logs = array();
                        $logs['analysis_id'] = $analysis_id;
                        $logs['types'] = $row['type'];
                        $logs['target'] = $row['target'];
                        $logs['flag'] = $row['flag'];
                        $logs['value'] = $row['value'];
                        $logs['time'] = $run_day;
                        app::get('ectools')->model('analysis_logs')->insert($logs);
                    }
                }
            }
        }
    }
    
    public function fetch_by_interval($interval) 
    {
        $rows = app::get('ectools')->model('analysis')->getList('*', array('interval'=>$interval));
        foreach($rows AS $row){
            $data[$row['service']] = $row;
        }
        return $data;
    }
}