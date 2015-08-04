<?php

 
class pointprofessional_misc_task{

    function week(){

    }

    function minute(){
        
    }

    function hour(){

    }

    function day(){
		// 每天更新用户冻结积分
		$this->generate_obtained_point();
		
		// 执行完间隔添加积分的事情
		/*$site_get_point_interval_time = app::get('b2c')->getConf('site.get_point_interval_time');
		if ($site_get_point_interval_time > 0)
			$this->add_member_point_task();
        */
        $obj_member_point_task = app::get('pointprofessional')->model('member_point_task');
        $filter = array(
            'task_type' => '1',
            'status' => '0',
        );
        if ($obj_member_point_task->count($filter) > 0)
            $this->add_member_point_task();
    }

    function month(){

    }
	
	/**
	 * 定时事件-扫描积分临时文件表，看那些需要执行
	 * @param null
	 * @return null
	 */
	public function add_member_point_task()
	{
		$obj_member_point_task = app::get('pointprofessional')->model('member_point_task');
		$today_time = strtotime(date('Y-m-d'));
		$tomorrow_time = $today_time + 24 * 3600;
		$sql = "SELECT * FROM " . $obj_member_point_task->table_name(1) . " WHERE task_type = '1' AND addtime != enddate AND enddate >= " . $today_time . " AND enddate < " . $tomorrow_time . ' AND status = "0"';
		
		$rows = $obj_member_point_task->db->select($sql);
		$obj_member_point_task->tidy_data($rows, '*');
		
		if ($rows && is_array($rows))
		{
			$app_b2c = app::get('b2c');
			$obj_member_point = $app_b2c->model('member_point');
			$obj_member = $app_b2c->model('members');
			$obj_member_lv = $app_b2c->model('member_lv');
			
			foreach ($rows as $arr_task)
			{
				$tmp = $obj_member->getList('point,member_lv_id', array('member_id'=>$arr_task['member_id']));
				if ($tmp)
				{
					$member_lv_id = $tmp[0]['member_lv_id'];
					$point_total = intval($tmp[0]['point']) + intval($arr_task['point']);
				}
				else
				{
					$member_lv_id = 0;
					$point_total = 0;
				}
				
				$time = time();
				$rows_member_lv = $obj_member_lv->getList('*', array('member_lv_id'=>$member_lv_id));
				$time = time();
				if ($rows_member_lv)
				{
					$site_point_expired = $app_b2c->getConf('site.point_expired');
					$site_point_expried_method = $app_b2c->getConf('site.point_expried_method');
					if ($site_point_expired == 'true')
					{
						switch ($site_point_expried_method)
						{
							case '1':
								$expired_time = $rows_member_lv[0]['expiretime'];
								break;
							case '2':
								$expired_time = $time + $rows_member_lv[0]['expiretime'];
								break;
							default:
								$expired_time = $rows_member_lv[0]['expiretime'];
								break;
						}
					}
				}
				
				$sdf_point = array(
				  'member_id'=>$arr_task['member_id'],
				  'point'=>$point_total,
				  'change_point'=>$arr_task['point'],
				  'addtime'=>$time,
				  'expiretime'=>(isset($expired_time) && $expired_time) ? $expired_time : $time,
				  'reason'=>$arr_task['task_name'],
				  'type'=>($arr_task['task_type']=='1') ? '2' : '4',
				  'related_id'=>($arr_task['related_id']) ? $arr_task['related_id'] : 0,
				);
				
				$obj_member_point_task->update(array('status'=>'1'), array('member_id'=>$arr_task['member_id'],'related_id'=>$arr_task['related_id'],'task_type'=>$arr_task['task_type']));
				/** 防止并发处理 **/
				if (!$obj_member_point_task->db->affect_row()) continue;
				$obj_member_point->insert($sdf_point);				
			}
		}
	}
	
	/**
	 * 获得积分的张某书--每一个用户
	 * @param null
	 * @return null
	 */
	public function generate_obtained_point()
	{
		$app_b2c = app::get('b2c');
		$site_point_expired = $app_b2c->getConf('site.point_expired');
		$site_point_expried_method = $app_b2c->getConf('site.point_expried_method');
		$obj_member_point = app::get('pointprofessional')->model('member_point');
		$obj_member_lv = $app_b2c->model('member_lv');
		$obj_member = $app_b2c->model('members');
		
		$total_point = 0;
		$sql = "SELECT * FROM " . $obj_member->table_name(1);
		$rows = $obj_member->db->select($sql);
		$obj_member->tidy_data($rows, '*');
		if ($rows)
		{
			foreach ($rows as $arr_row)
			{
				$total_point = $obj_member_point->get_total_count($arr_row['member_id']);
				$obj_member->update(array('point'=>$total_point), array('member_id'=>$arr_member_points['member_id']));
				$total_point_cumulation = $obj_member_point->get_total_cumulation($arr_row['member_id']);
				$obj_member->update(array('cumulation_point'=>$total_point_cumulation), array('member_id'=>$arr_member_points['member_id']));
			}
		}	
	}
}
