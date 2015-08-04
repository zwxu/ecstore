<?php

 
class pointprofessional_point_task_comment implements pointprofessional_point_task_interface
{
	 /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }
	
	public function get_point_task_type()
	{
		return 'comment';
	}
	
	public function generate_data($arr_data=array(), &$arr_point_task)
	{
		if (!$arr_data)
			return array();
			
		$arr_point_task = array(
			'member_id' => $arr_data['member_id'],
			'task_name' => app::get('pointprofessional')->_('商品评论添加积分'),
			'point' => $arr_data['point'],
			'addtime' => $arr_data['addtime'],
			'enddate' => $arr_data['enddate'],
			'related_id' => $arr_data['related_id'],
			'task_type' => '2',
			'remark' => app::get('pointprofessional')->_('商品评论添加积分定时任务'),
			'operator' => $arr_data['operator'],
		);
		
		return $arr_point_task;
	}
}