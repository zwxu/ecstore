<?php

 
class business_ctl_site_mypoint extends b2c_ctl_site_member
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
	
	public function my_point($nPage=1)
	{
		$this->path[] = array('title'=>app::get('business')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('我的积分'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
		
        $member = app::get('b2c')->model('members');
        $member_point = app::get('business')->model('member_point');
        $orders = app::get('b2c')->model('orders');
		
		
		$data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = count($member_point->get_all_list('*',array('member_id' => $this->member['member_id'])));
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
        $params['data'] = $member_point->get_all_list('*',array('member_id' => $this->member['member_id']),$aPage['start'],$this->pagesize);

        foreach($params['data'] as &$v){
            if($v['change_point'] >= 0){
                $v['score_u'] = 0;
                $v['score_g'] = $v['change_point'];
            }else{
                $v['score_u'] = abs($v['change_point']);
                $v['score_g'] = 0;
            }
        
        }

        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'my_point','','business','site_mypoint');
        $this->pagedata['total'] = $data['score']['total'];
        $this->pagedata['historys'] = $params['data'];
        $this->output('business');
	}
}