<?php
class cellphone_mdl_actunit extends dbeav_model{

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();
        $this->app_list = cellphone_misc_exec::get_actapp();
    }
    
    public function count($filter=null){
        $count = 0;
        $time = time();
        $current = array();
        if(isset($filter['unit_id']) && !empty($filter['unit_id'])){
            foreach((array)$filter['unit_id'] as $row){
                list($act_id, $app) = explode('_', $row);
                $current[$app] = $act_id?intval($act_id):-1;
            }
        }
        foreach((array)$this->app_list as $key => $value){
            if(!empty($current) && !array_key_exists($key,$current))continue;
            $sql = "select count(act_id) from ".kernel::database()->prefix."{$key}_".$value['m1']." where act_open='true' and start_time<={$time} and end_time>{$time} ".
            ((isset($filter['act_name']) && !empty($filter['act_name']))?" and name like '%{$filter['act_name']}%'":"").
            (!empty($current[$key])?" and act_id =".$current[$key]:"");
            $query = mysql_query($sql);
            $count += mysql_result($query, 0);
        }
        return $count;
    }

    public function getlist($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $sql = array();
        $time = time();
        $current = array();
        if(isset($filter['unit_id']) && !empty($filter['unit_id'])){
            foreach((array)$filter['unit_id'] as $row){
                list($act_id, $app) = explode('_', $row);
                $current[$app] = $act_id?intval($act_id):-1;
            }
        }
        foreach((array)$this->app_list as $key => $value){
            if(!empty($current) && !array_key_exists($key,$current))continue;
            $sql[] = "(select concat(convert(act_id,char),'_{$key}') as unit_id,act_id,name as act_name,start_time,end_time,
'{$key}' as app,'{$value['m1']}' as m1,'{$value['m2']}' as m2 from ".kernel::database()->prefix."{$key}_".$value['m1']." where act_open='true' 
and start_time<={$time} and end_time>{$time} ".
((isset($filter['act_name']) && !empty($filter['act_name']))?" and name like '%{$filter['act_name']}%'":"").
(!empty($current[$key])?" and act_id =".$current[$key]:"")." )";
        }
        if(empty($sql)) return array();
        $sql = implode(' union ', $sql);
        $sql .= " limit {$offset}";
        if($limit && $limit!=-1)$sql .= ",{$limit}";
        else $sql .= ",".$this->count($filter);
        $data = $this->app->model('activity')->db->select($sql);
        $this->tidy_data($data, $cols);
        return $data;
    }

    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        $where = array(1);
        return implode($where,' AND ');
    }

    public function get_schema(){
        $schema = array (
            'columns' => array (
                'unit_id' => array (
                    'type' => 'varchar(255)',
                    'width' => 130,
                    'pkey'=>'true',
                    'editable' => false,
                ),
                'act_id' => array (
                    'type' => 'number',
                    'label' => app::get('b2c')->_('活动ID'),
                    'width' => 130,
                    'editable' => false,
                ),
                'act_name' => array (
                    'type' => 'varchar(255)',
                    'label' => app::get('b2c')->_('活动名称'),
                    'width' => 130,
                    'editable' => false,
                    'is_title' => true,
                    'in_list' => true,
                    'default_in_list' => true,
                    'searchtype' => 'has',
                    'filtertype' => 'custom',
                    'filterdefault' => true,
                ),
                'app' => array (
                    'type' => 'varchar(255)',
                    'label' => app::get('b2c')->_('活动app'),
                    'width' => 130,
                    'editable' => false,
                    'in_list' => true,
                    'default_in_list' => true,
                ),
                'm1' => array (
                    'type' => 'varchar(255)',
                    'label' => app::get('b2c')->_('活动表名'),
                    'width' => 130,
                    'editable' => false,
                ),
                'm2' => array (
                    'type' => 'varchar(255)',
                    'label' => app::get('b2c')->_('活动商品表名'),
                    'width' => 130,
                    'editable' => false,
                ),
                'start_time'=>array(
                    'type'=>'time',
                    'label'=>__('活动开始时间'),
                    'editable'=>false,
                    'in_list'=>true,
                    'default_in_list'=>true,
                ),
                'end_time'=>array(
                    'type'=>'time',
                    'label'=>__('活动结束时间'),
                    'editable'=>false,
                    'in_list'=>true,
                    'default_in_list'=>true,
                ),
            ),
            'idColumn' => 'unit_id',
            'textColumn' => 'act_name',
            'in_list' => array (
                0 => 'act_name',
                1 => 'app',
                2 => 'start_time',
                3 => 'end_time',
            ),
            'default_in_list' => array (
                0 => 'act_name',
                1 => 'app',
                2 => 'start_time',
                3 => 'end_time',
            ),
        );
        return $schema;
    }
}
