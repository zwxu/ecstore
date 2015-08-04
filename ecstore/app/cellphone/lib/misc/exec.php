<?php
  
class cellphone_misc_exec{

    static public function delete_expire_data() 
    {
        $app_list = self::get_actapp();
        $sql_join = array();
        $time = time();
        foreach((array)$app_list as $key => $value){
            $objModel = @app::get($key)->model($value['m1']);
            if($objModel)
            $sql_join[] = "(select {$objModel->idColumn} as act_id,'{$key}' as app from ".kernel::database()->prefix."{$key}_".$value['m1']." where end_time<={$time})";
        }
        $objActivity = app::get('cellphone')->model('activity');
        
        if(!empty($sql_join)){
            $sql = " update {$objActivity->table_name(1)} as a join (".implode(' union all ',$sql_join).") as b on a.original_id=b.act_id and instr(a.source,'{$key}')>0 set a.disabled='true'";
        }else{
            $sql = " update {$objActivity->table_name(1)} as a set a.disabled='true' where a.disabled='false'";
        }
        kernel::database()->exec($sql);
    }
    
    static function get_actapp(){
        $app_list = array();
        $activity_cat = kernel::service('business_activity_cat');
        if($activity_cat){
            foreach((array)$activity_cat->loadActivityCat() as $row){
                if($row['app'])
                switch($row['app']){
                case 'timedbuy':
                    $app_list['timedbuy'] = array(
                        'm1' => 'activity',
                        'm2' => 'businessactivity',
                    );
                    break;
                case 'scorebuy':
                    $app_list['scorebuy'] = array(
                        'm1' => 'activity',
                        'm2' => 'scoreapply',
                    );
                    break;
                case 'groupbuy':
                    $app_list['groupbuy'] = array(
                        'm1' => 'activity',
                        'm2' => 'groupapply',
                    );
                    break;
                case 'spike':
                    $app_list['spike'] = array(
                        'm1' => 'activity',
                        'm2' => 'spikeapply',
                    );
                    break;
                case 'package':
                    $app_list['package'] = array(
                        'm1' => 'activity',
                        'm2' => 'attendactivity',
                    );
                    break;
                }
            }
        }
        return $app_list;
    }
    
    static function get_change(&$var){
        $templet = array(
            'presonlimit' => 'personlimit',
            'amount' => 'last_price',
            'nums' => 'store',
            'id' => 'act_id',
        );
        foreach((array)$var as $k=>$v){
            if(array_key_exists($k,$templet)){
                unset($var[$k]);
                $k = $templet[$k];
                $var[$k] = $v;
            }
            if(is_array($v)){
                self::get_change($var[$k]);
            }
        }
    }
}