<?php

 
class b2c_ctl_admin_member_attr extends desktop_controller{

    var $workground = 'b2c.workground.member';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $attr_model = &$this->app->model('member_attr');
        $tmpdate =$attr_model->getList('*',null,0,-1,array('attr_order','asc'));
        #$t_num = count($tmpdate);
        foreach($tmpdate as $key=>$val){
            if($val['attr_type'] == "select" || $val['attr_type'] == "checkbox"){
                $val['attr_option'] = unserialize($val['attr_option']);
            }
            $n_tmpdate[$key] = $val;
        }

		foreach($n_tmpdate as $key=>$val){
			if($val['attr_column'] == 'mobile'){
			   unset($n_tmpdate[$key]);
			}
        }
        $this->pagedata['tree'] = $n_tmpdate;
        $this->page('admin/member/attr_map.html');
    }

    function add_page(){
        $this->display('admin/member/attr_new.html');
    }
    
    function add(){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index'); 
        $attr_model = &$this->app->model('member_attr');
        if($this->check_column($_POST['attr_column'])){
            $this->end(false,app::get('b2c')->_('该注册项字段名已存在'));
        }
        $flag = $attr_model->save($_POST);
        if($flag!=''){
            $this->end(true,app::get('b2c')->_('保存成功！'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败！'));
        }
    }
    
    function check_column($column){
        $member = $this->app->model('members');
        $metaColumn = $member->metaColumn;
        if(in_array($column,(array)$metaColumn)){
            return true;
        }
        else{
            return false;
        }
    }
    
    function edit_page($attr_id){
        $attr_model = &$this->app->model('member_attr');
        $data = $attr_model->dump($attr_id);

        if($data['attr_option'] !=''){
            $data['attr_option'] = unserialize($data['attr_option']);
            $data['attr_optionNo1'] = $data['attr_option'][0];
            unset($data['attr_option'][0]);
        }
        $this->pagedata['memattr'] = $data;
        $this->page('admin/member/attr_edit.html');
    }
    
    function edit(){
        if(!$_POST['attr_required']) $_POST['attr_required']="false";
        if($_POST['attr_option'] !=''){
            $_POST['attr_option'] = serialize($_POST['attr_option']);
        }
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index'); 
        $attr_model = &$this->app->model('member_attr');    
        if($attr_model->save($_POST)){
            $this->end(true,app::get('b2c')->_('编辑成功！'));
        }else{
           $this->end(false,app::get('b2c')->_('编辑失败！'));
        }
    }
    
    function remove($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = &$this->app->model('member_attr');
        $this->end($attr_model->delete($attr_id),app::get('b2c')->_('选项删除成功'));
    }
    
    function show_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = &$this->app->model('member_attr');
        $this->end( $attr_model->set_visibility($attr_id,true),app::get('b2c')->_('已设置显示状态'));
    }
    
    function hidden_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = &$this->app->model('member_attr');
        $this->end( $attr_model->set_visibility($attr_id,false),app::get('b2c')->_('已设置关闭状态'));
    }
    
    function save_order(){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = &$this->app->model('member_attr');
        $this->end( $attr_model->update_order($_POST['attr_order']),app::get('b2c')->_('选项排序更改成功'));
    }
    
	/**
	*注册项显示控制 
	*/
	function show_regswitch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = &$this->app->model('member_attr');
        $this->end( $attr_model->set_regvisibility($attr_id,true),app::get('b2c')->_('已设置显示状态'));
    }
    
	/**
	*注册项隐藏控制 
	*/
    function hidden_regswitch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = &$this->app->model('member_attr');
        $this->end( $attr_model->set_regvisibility($attr_id,false),app::get('b2c')->_('已设置关闭状态'));
    }

    function profit(){
        $this->pagedata['profit'] = $this->app->getConf('member.profit');
        $this->pagedata['isprofit'] = $this->app->getConf('member.isprofit');
        $this->page('admin/member/profit.html');
    }

    function updateprofit(){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=profit');
        $this->app->setConf('member.profit',$_POST['profit']);
        $this->app->setConf('member.isprofit',$_POST['isprofit']);
        $this->end(true,app::get('b2c')->_('设置成功'));     
    }

    function setTime(){
        $this->pagedata['to_finish'] = $this->app->getConf('member.to_finish');
        $this->pagedata['to_finish_BAM'] = $this->app->getConf('member.to_finish_BAM');
        $this->pagedata['to_finish_XU'] = $this->app->getConf('member.to_finish_XU');
        $this->pagedata['to_refund'] = $this->app->getConf('member.to_refund');
        $this->pagedata['to_close'] = $this->app->getConf('member.to_close');
        $this->pagedata['to_agree'] = $this->app->getConf('member.to_agree');
        $this->pagedata['to_buyer_refund'] = $this->app->getConf('member.to_buyer_refund');
        $this->pagedata['to_buyer_slr'] = $this->app->getConf('member.to_buyer_slr');
        $this->pagedata['payed_ship_time'] = $this->app->getConf('site.activity.payed_ship_time');
        $this->pagedata['no_attendActivity_time'] = $this->app->getConf('site.activity.no_attendActivity_time');
        $this->pagedata['group_payed_time'] = $this->app->getConf('site.group.payed_time');
        $this->pagedata['spike_payed_time'] = $this->app->getConf('site.spike.payed_time');
        $this->pagedata['timedbuy_payed_time'] = $this->app->getConf('member.timedbuy_payed_time');
        $this->pagedata['to_buyer_edit'] = $this->app->getConf('member.to_buyer_edit');

       
        $this->pagedata['comment_original_time'] = $this->app->getConf('site.comment_original_time');
        $this->pagedata['comment_additional_time'] = $this->app->getConf('site.comment_additional_time');
        
        
        $this->page('admin/member/setTime.html');
    }

    function updateTime(){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=setTime');
        $this->app->setConf('member.to_finish',$_POST['to_finish']);
        $this->app->setConf('member.to_finish_BAM',$_POST['to_finish_BAM']);
        $this->app->setConf('member.to_finish_XU',$_POST['to_finish_XU']);
        $this->app->setConf('member.to_refund',$_POST['to_refund']);
        $this->app->setConf('member.to_close',$_POST['to_close']);
        $this->app->setConf('member.to_agree',$_POST['to_agree']);
        $this->app->setConf('member.to_buyer_refund',$_POST['to_buyer_refund']);
        $this->app->setConf('member.to_buyer_slr',$_POST['to_buyer_slr']);
        $this->app->setConf('site.activity.payed_ship_time',$_POST['payed_ship_time']);
        $this->app->setConf('site.activity.no_attendActivity_time',$_POST['no_attendActivity_time']);
        $this->app->setConf('site.group.payed_time',$_POST['group_payed_time']);
        $this->app->setConf('site.spike.payed_time',$_POST['spike_payed_time']);
        $this->app->setConf('site.score.payed_time',$_POST['score_payed_time']);
        $this->app->setConf('member.timedbuy_payed_time',$_POST['timedbuy_payed_time']);
        $this->app->setConf('member.to_buyer_edit',$_POST['to_buyer_edit']);
        
        
        $this->app->setConf('site.comment_original_time',$_POST['comment_original_time']);
        $this->app->setConf('site.comment_additional_time',$_POST['comment_additional_time']);
       
        
        $this->end(true,app::get('b2c')->_('设置成功'));  
    }

    function setService(){
        $this->pagedata['ServiceQQ'] = $this->app->getConf('member.ServiceQQ');    
        $this->page('admin/member/setService.html');
    }

    function updateService(){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=setService');
        $this->app->setConf('member.ServiceQQ',$_POST['ServiceQQ']);
        
        $this->end(true,app::get('b2c')->_('设置成功'));  
    }


}
