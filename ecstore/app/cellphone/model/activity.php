<?php
class cellphone_mdl_activity extends dbeav_model{

    var $has_many = array(
        'relation' => 'activity_rel:replace:act_id^act_id',
    );
    var $has_one = array(
    );
    var $subSdf = array(
        'default' => array(
            'relation'=>array('rel_id'),
        ),
        'delete' => array(
            'relation'=>array('*'),
        )
    );
    
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }
  
    function get_detail($act_id){
        $dumpData = $this->dump(array('act_id'=>intval($act_id)),'source,original_id');
        $dumpData['source'] = unserialize($dumpData['source']);
        if($dumpData['source'] && is_array($dumpData['source'])){
            $act_id = $dumpData['original_id']?$dumpData['original_id']:'-1';
            $time = time();
            $app = @app::get($dumpData['source']['app']);
            $activity = @$app->model($dumpData['source']['m1']);
            $actapply = @$app->model($dumpData['source']['m2']);
            if($app && $activity && $actapply){
                foreach((array)$activity->getList('start_time,end_time', array('act_id'=>$act_id,'act_open'=>'true','start_time|sthan'=>$time,'end_time|than'=>$time),0,-1) as $row){
                    $dumpData['start_time'] = $row['start_time'];
                    $dumpData['end_time'] = $row['end_time'];
                }
            }
        }
        if(!isset($dumpData['start_time']) || !isset($dumpData['end_time'])){
            $dumpData = array();
        }
        return $dumpData;
    }
    
    function save($sdf){
        if(!$sdf['act_id']) unset($sdf['act_id']);
        $relationship = $sdf['rel'];
        unset($sdf['rel']);
        $flag = parent::save($sdf);
        if(!empty($relationship) && is_array($relationship)){
            if($sdf['act_id'])$this->app->model('activity_rel')->delete(array('act_id'=>$sdf['act_id']));
            foreach((array)$relationship as $row){
                $aData = array(
                    'act_id' => $sdf['act_id'],
                    'rel_id' => $row,
                );
                if($flag){
                    $flag = $this->app->model('activity_rel')->save($aData);
                }
            }
        }
        return $flag;
    }
    
    public function _filter($filter) {
        $filter['disabled'] = 'false';
        return parent::_filter($filter);
    }
}
