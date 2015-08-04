<?php

 
class pointprofessional_ctl_site_point extends b2c_ctl_site_member
{
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct(&$app)
	{
		$this->app_current = $app;
		$this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }
	
	public function point_detail($nPage=1)
	{
		$this->path[] = array('title'=>app::get('pointprofessional')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('pointprofessional')->_('我的积分'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
		
        $member = $this->app_current->model('members');
        $member_point = $this->app_current->model('member_point');
		
        $obj_gift_link = kernel::service('b2c.exchange_gift');
		if ($obj_gift_link)
		{
			$this->pagedata['exchange_gift_link'] = $obj_gift_link->gen_exchange_link();
		}
		
		$data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = $member_point->count(array('member_id' => $this->app->member_id));
        // 扩展的积分信息
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_detail_point($data);
            }
        }
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $member_point->get_all_list('*',array('member_id' => $this->member['member_id'], 'change_point|than'=>'0'),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'point_detail','','pointprofessional','site_point');
        $this->pagedata['total'] = $data['score']['total'];
        $this->pagedata['historys'] = $params['data'];
        $this->output('pointprofessional');
	}
}