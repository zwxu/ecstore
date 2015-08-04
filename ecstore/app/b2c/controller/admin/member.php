<?php
 

class b2c_ctl_admin_member extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';
    var $pagelimit = 10;
    var $member_model ;
    public function __construct($app)
    {
        parent::__construct($app);
        $this->member_model = $this->app->model('members');
        header("cache-control: no-store, no-cache, must-revalidate");
    }

   function index(){
        if($_GET['action'] == 'export') $this->_end_message = '导出会员';
        //增加会员相关权限判断@lujy
        if($this->has_permission('addmember')){
            $custom_actions[] = array('label'=>app::get('b2c')->_('添加会员'),'href'=>'index.php?app=b2c&ctl=admin_member&act=add_page','target'=>'dialog::{title:\''.app::get('b2c')->_('添加会员').'\',width:460,height:460}');
        }
        if($this->has_permission('send_email')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发邮件'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_email','target'=>'dialog::{title:\''.app::get('b2c')->_('群发邮件').'\',width:700,height:400}');
        }
        if($this->has_permission('send_msg')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发站内信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_msg','target'=>'dialog::{title:\''.app::get('b2c')->_('群发站内信').'\',width:500,height:350}');
        }
        if($this->has_permission('send_sms')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发短信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_sms','target'=>'dialog::{title:\''.app::get('b2c')->_('群发短信').'\',width:500,height:350}');
        }
        $actions_base['title'] = app::get('b2c')->_('会员列表');
        $actions_base['actions'] = $custom_actions;
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_buildin_export'] = true;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['use_buildin_recycle'] = false;
        $actions_base['use_view_tab'] = true;

        $this->finder('b2c_mdl_members',$actions_base);
    }

   function _views(){
        $mdl_member = $this->app->model('members');
        //今日新增会员
        $today_filter = array(
                    '_regtime_search'=>'between',
                    'regtime_from'=>date('Y-m-d'),
                    'regtime_to'=>date('Y-m-d'),
                    'regtime' => date('Y-m-d'),
                    '_DTIME_'=>
                        array(
                            'H'=>array('regtime_from'=>'00','regtime_to'=>date('H')),
                            'M'=>array('regtime_from'=>'00','regtime_to'=>date('i'))
                        )
                );
        $today_reg = $mdl_member->count($today_filter);
        $sub_menu[0] = array('label'=>app::get('b2c')->_('今日新增会员'),'optional'=>true,'filter'=>$today_filter,'addon'=>$today_reg,'href'=>'index.php?app=b2c&ctl=admin_member&act=index&view=0&view_from=dashboard');

        //昨日新增
        $date = strtotime('yesterday');
        $yesterday_filter = array(
                    '_regtime_search'=>'between',
                    'regtime_from'=>date('Y-m-d',$date),
                    'regtime_to'=>date('Y-m-d'),
                    'regtime' => date('Y-m-d',$date),
                    '_DTIME_'=>
                        array(
                            'H'=>array('regtime_from'=>'00','regtime_to'=>date('H',$date)),
                            'M'=>array('regtime_from'=>'00','regtime_to'=>date('i',$date))
                        )
                );
        $yesterday_reg = $mdl_member->count($yesterday_filter);
        $sub_menu[1] = array('label'=>app::get('b2c')->_('昨日新增会员'),'optional'=>true,'filter'=>$yesterday_filter,'addon'=>$yesterday_reg,'href'=>'index.php?app=b2c&ctl=admin_member&act=index&view=1&view_from=dashboard');

        //TAB扩展
        foreach(kernel::servicelist('desktop_member_view_extend') as $service){
            if(method_exists($service,'getViews')) {
                $service->getViews($sub_menu);
            }
        }

         foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array(),$v['filter']);
                }else{
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                $show_menu[$k]['addon'] = $mdl_member->count($v['filter']);
                $show_menu[$k]['href'] = 'index.php?app=b2c&ctl=admin_member&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }else{
                $show_menu[$k]['addon'] = false;
            }
        }
        return $show_menu;
    }

    function add_page(){
        $member_lv=$this->app->model("member_lv");
        foreach($member_lv->getMLevel() as $row){
            $options[$row['member_lv_id']] = $row['name'];
        }
        $a_mem['lv']['options'] = is_array($options) ? $options : array(app::get('b2c')->_('请添加会员等级')) ;
        $a_mem['lv']['value'] = $a_mem['member_lv']['member_group_id'];
         $member_model = $this->app->model('members');
        $mem_schema = $member_model->_columns();
         $attr =array();
            foreach($this->app->model('member_attr')->getList() as $item){
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item){
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath){
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1){
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }else{
                $name = $item['attr_column'];
            }
              if($attr[$key]['attr_type'] == 'select' ||$attr[$key]['attr_type'] == 'checkbox'){
                $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
            }

            $attr[$key]['attr_column'] = $name;
             if($attr[$key]['attr_column']=="birthday"){
              $attr[$key]['attr_column'] = "profile[birthday]";
          }
        }
        $this->pagedata['attr'] = $attr;
        $this->pagedata['mem'] = $a_mem;
       $this->display('admin/member/new.html');
    }

    function add(){
         foreach($_POST as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $_POST[$aTmp[1]] = serialize($val);
            }
        }
        
        if($_POST['seller']=='true'){
            $_POST['seller']='seller';
        }else{

            unset($_POST['seller']);

        }
        $this->begin();
        $mem_model = &$this->app->model("members");
        if($mem_model->validate($_POST,$message)){

        //同步到ucenter yindingsheng
        if( $uc = kernel::service("uc_user_register") ) {
            $uid = $uc->uc_user_register($_POST['pam_account']['login_name'],$_POST['pam_account']['psw_confirm'],'','','',$_POST['contact']['email'],$_POST['contact']['phone']['mobile']);
            if($uid>0){
                $_POST['foreign_id'] = $uid;
            }else{
				$this->end(false, app::get('b2c')->_('sso系统添加失败！'));
			}
        }
        //同步到ucenter yindingsheng

        $id = $mem_model->create($_POST);
        if($id!=''&&$id){
            $data['member_id'] = $id;;
            $data['uname'] = $_POST['pam_account']['login_name'];
            $data['passwd'] = $_POST['pam_account']['psw_confirm'];
            $data['email'] = $_POST['contact']['email'];
            $data['is_frontend'] = false;

            //增加会员同步 2012-5-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->createActive($id);
            }

            $obj_account=&$this->app->model('member_account',$data['passwd'],$data['email']);
            $obj_account->fireEvent('register',$data,$id);
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog')){
                if(method_exists($obj_operatorlogs,'inlogs')) {
                    $memo = '添加新会员，会员名为  "'.$data['uname'].'"';
                    $obj_operatorlogs->inlogs($memo, '添加会员', 'members');
                }
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            $this->end(true, app::get('b2c')->_('添加成功！'));
        }else{
            $this->end(false, app::get('b2c')->_('添加失败！'));
        }
        }
        else{
            $this->end(false, $message);
        }
    }

    function regitem(){
            $this->display('member/member_regitem.html');
        }

    function send_email(){
        if($_POST['isSelectedAll'] == '_ALL_'){ 
            //店铺列表  
            if($_POST['ctl'] =='admin_storemanger'){
                $aMember = array();
                $obj_store = app::get('business')->model('storemanger');
                if($_GET['source']=='index'){
                    $aStoreData = $obj_store->get_list_approved('account_id');
                } else {
                    $aStoreData = $obj_store->get_list_approve('account_id');
                }
                
                //取得店主的会员ID
                foreach($aStoreData as $key => $val){
                    $aMember[] = $val['account_id'];
                }

                $seller =$_GET['source'];

            } else {
                $aMember = array();
                $obj_member = app::get('b2c')->model('members');
                $aData = $obj_member->getList('member_id');
                foreach((array)$aData as $key => $val){
                    $aMember[] = $val['member_id'];
                }
            }
        }
        else{ 
            //店铺列表 
            if($_POST['store_id']){

                $obj_store = app::get('business')->model('storemanger');
                if($_GET['source']=='index'){
                    $aStoreData = $obj_store->get_list_approved('account_id',array('store_id'=>$_POST['store_id']));
                } else {
                    $aStoreData = $obj_store->get_list_approve('account_id',array('store_id'=>$_POST['store_id']));
                }


                
                //取得店主的会员ID
                foreach($aStoreData as $key => $val){
                    $aMember[] = $val['account_id'];
                }
               $seller =$_GET['source'];

            } else {
                $aMember = $_POST['member_id'];
            }
        }


        $aEmail = array();
        foreach( $aMember as $mid){
            $aEmail[] = $this->get_email($mid);
        }
        $this->pagedata['seller'] =$seller;
        $this->pagedata['aEmail'] = json_encode($aEmail);
        $this->page('admin/messenger/write_email.html');
    }

    function send_msg(){
        if($_POST['isSelectedAll'] == '_ALL_'){

             //店铺列表  
            if($_POST['ctl'] =='admin_storemanger'){
                $aMember = array();
                $obj_store = app::get('business')->model('storemanger');
                if($_GET['source']=='index'){
                    $aStoreData = $obj_store->get_list_approved('account_id');
                } else {
                    $aStoreData = $obj_store->get_list_approve('account_id');
                }
                
                //取得店主的会员ID
                foreach($aStoreData as $key => $val){
                    $aMember[] = $val['account_id'];
                }

                $seller =$_GET['source'];

            } else {
                $aMember = array();
                $obj_member = app::get('b2c')->model('members');
                $aData = $obj_member->getList('member_id');
                foreach((array)$aData as $key => $val){
                    $aMember[] = $val['member_id'];
                }
            }
        }
        else{
             //店铺列表  
            if($_POST['store_id']){

                $obj_store = app::get('business')->model('storemanger');
                 if($_GET['source']=='index'){
                    $aStoreData = $obj_store->get_list_approved('account_id',array('store_id'=>$_POST['store_id']));
                } else {
                    $aStoreData = $obj_store->get_list_approve('account_id',array('store_id'=>$_POST['store_id']));
                }
                
                //取得店主的会员ID
                foreach($aStoreData as $key => $val){
                    $aMember[] = $val['account_id'];
                }

                $seller =$_GET['source'];

            } else {
                $aMember = $_POST['member_id'];
            }
        }

        $this->pagedata['seller'] =$seller;
        $this->pagedata['aMember'] = json_encode($aMember);
        $this->page('admin/messenger/write_msg.html');
    }

    function send_sms(){
        $obj_member = app::get('b2c')->model('members');
        $params = kernel::single('base_component_request')->get_post();
        $response = kernel::single('base_component_response');

        if($params['isSelectedAll'] == '_ALL_'){

             //店铺列表  
            if($_POST['ctl'] =='admin_storemanger'){
                $aMember = array();
                $obj_store = app::get('business')->model('storemanger');
                 if($_GET['source']=='index'){
                    $aStoreData = $obj_store->get_list_approved('account_id');
                } else {
                    $aStoreData = $obj_store->get_list_approve('account_id');
                }
                
                //取得店主的会员ID
                foreach($aStoreData as $key => $val){
                    $aMember[] = $val['account_id'];
                }

                $seller =$_GET['source'];

            } else {

                $aMember = array();
                $aData = $obj_member->getList('member_id');
                foreach($aData as $val){
                    $aMember[] = $val['member_id'];
                }
            }
        }
        else{
             //店铺列表 
            if($_POST['store_id']){

                $obj_store = app::get('business')->model('storemanger');
                if($_GET['source']=='index'){
                    $aStoreData = $obj_store->get_list_approved('account_id',array('store_id'=>$_POST['store_id']));
                } else {
                    $aStoreData = $obj_store->get_list_approve('account_id',array('store_id'=>$_POST['store_id']));
                }
               
                //取得店主的会员ID
                foreach($aStoreData as $key => $val){
                    $aMember[] = $val['account_id'];
                }
               $seller =$_GET['source'];

            } else {
                $aMember = $params['member_id'];
            }
        }
        $aEmail = array();
        foreach( $aMember as $mid){
            $row = $obj_member->getList('mobile',array('member_id' => $mid));
            if($row[0]['mobile']){
                $mobile_number[] = $row[0]['mobile'];
            }else{
                $noMobile[] = $mid;
            }
        }

        if($noMobile) {
            $account = kernel::single('pam_mdl_account')->getList('login_name',array('account_id'=>$noMobile));
            $this->pagedata['noMobile'] = $account;
        }

        $this->pagedata['seller'] =$seller;
        $this->pagedata['mobile_number'] = json_encode($mobile_number);
        $this->page('admin/messenger/write_sms.html');
    }

    function sms_queue(){
       
        if($_GET['seller']){
           $this->begin('index.php?app=business&ctl=admin_storemanger&act='.$_GET['seller']);
        }else {
           $this->begin('index.php?app=b2c&ctl=admin_member&act=index');
        }


       //$this->begin();
       $queue = app::get('base')->model('queue');
       $member_obj = $this->app->model('members');
       $mobile_number = json_decode($_POST['mobile_number']);

       if(!$mobile_number) $this->end(false,app::get('b2c')->_('所选会员都没有填写手机号码'));
       $mobile_number = array_unique($mobile_number);

       $mobile_number = array_chunk($mobile_number,200,false);
       $_POST['sendType'] = 'fan-out';
       foreach($mobile_number as $m){
           $data = array(
                'queue_title' => app::get('b2c')->_('群发短信'),
                'start_time' => time(),
                'params' => array(
                    'mobile_number' => implode(',',(array)$m),
                    'data' => $_POST
                ),
                'worker' => 'b2c_queue.send_sms'
           );

           if(!$queue->insert($data)){
                $this->end(false,app::get('b2c')->_('操作失败！'));
           }
       }
            $this->end(true,app::get('b2c')->_('操作成功！'));
    }

    function msg_queue(){

       
        if($_GET['seller']){
           $this->begin('index.php?app=business&ctl=admin_storemanger&act='.$_GET['seller']);
        }else {
           $this->begin('index.php?app=b2c&ctl=admin_member&act=index');
        }


       //$this->begin();
       $queue = app::get('base')->model('queue');
       $member_obj = $this->app->model('members');
       $aMember = json_decode($_POST['arrMember']);
       unset($_POST['arrMember']);
       foreach($aMember as $key=>$val){
            $member_sdf = $member_obj->dump($val,'*',array(':account@pam'=>array('login_name')));
            $login_name = $member_sdf['pam_account']['login_name'];
            $data = array(
            'queue_title'=>app::get('b2c')->_('发站内信'),
            'start_time'=>time(),
            'params'=>array(
            'member_id'=>$val,
            'data' =>$_POST,
            'name' => $login_name,
            ),
            'worker'=>'b2c_queue.send_msg',
        );
       if(!$queue->insert($data)){
            $this->end(false,app::get('b2c')->_('操作失败！'));
        }
       }
            $this->end(true,app::get('b2c')->_('操作成功！'));
    }

    function insert_queue(){
        
         
        if($_GET['seller']){
           $this->begin('index.php?app=business&ctl=admin_storemanger&act='.$_GET['seller']);
        }else {
           $this->begin('index.php?app=b2c&ctl=admin_member&act=index');
        }
        
        // $this->begin();
        $queue = app::get('base')->model('queue');
        $aEmail = json_decode($_POST['aEmail']);
        $service = kernel::service("b2c.messenger.email_content");
        if(is_object($service))
        {
            if(method_exists($service,'get_content'))
                $_POST['content'] = $service->get_content($_POST['content']);
        }
        $content = trim($_POST['content'],'&nbsp;');
        if(empty($content)){
            $this->end(false,app::get('b2c')->_('邮件内容不能为空！'));
        }
        foreach($aEmail as $key=>$val){
            $data = array(
                'queue_title'=>app::get('b2c')->_('发邮件').$key,
                'start_time'=>time(),
                'params'=>array(
                'acceptor'=>$val,
                'body' =>$_POST['content'],
                'title' =>$_POST['title'],
                ),
                'worker'=>'b2c_queue.send_mail',
            );
            if(!$queue->insert($data)){
                $this->end(false,app::get('b2c')->_('操作失败！'));
            }
        }
        $this->end(true,app::get('b2c')->_('操作成功！'));


  }

   function get_email($member_id){

       $obj_member = app::get('b2c')->model('members');
       $sdf = $obj_member->dump($member_id);
       return $sdf['contact']['email'];
  }

  function chkpassword($member_id=null){
    $member = $this->app->model('members');
    $aMem = $member->dump($member_id,'*',array( ':account@pam'=>array('*')));
    if($_POST){
        $this->begin();
        $member_id = $_POST['member_id'];
        if($_POST['newPassword']!==$_POST['confirmPassword']){
            $this->end(false,app::get('b2c')->_('两次密码不一致'));
        }
        $sdf = $member->dump($member_id,'*',array( ':account@pam'=>array('*')));
        $sdf['pam_account']['login_password'] = md5(trim($_POST['newPassword']));

		//同步到ucenter yindingsheng 修改密码到ucenter
		if( $member_object = kernel::service("uc_user_edit")) {
			$aData['member_id'] = $member_id;
			$aData['passwd_re'] = $_POST['newPassword'];
			if(!$member_object->uc_user_edit_pwd($aData)){
				$this->end(false,app::get('b2c')->_('修改失败'));
			}
		}
		//同步到ucenter yindingsheng

        if($member->save($sdf)){
            if($_POST['sendemail']){
            $data['member_id'] = $this->app->member_id;
            $data['uname'] = $sdf['pam_account']['login_name'];
            $data['passwd'] = $_POST['newPassword'];
            $data['email'] = $sdf['contact']['email'];
            $obj_account = $this->app->model('member_account');
            $obj_account->fireEvent('chgpass',$data,$member_id);
            }
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog')){
                if(method_exists($obj_operatorlogs,'inlogs')) {
                    $memo = '会员 "'.$sdf['pam_account']['login_name'].'" 的密码被修改';
                    $obj_operatorlogs->inlogs($memo, $sdf['pam_account']['login_name'], 'members');
                }
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            $this->end(true,app::get('b2c')->_('修改成功'));
        }
        else{
            $this->end(false,app::get('b2c')->_('修改失败'));
        }
    }
    $this->pagedata['member_id'] = $member_id;
    $this->pagedata['email'] = $aMem['contact']['email'];
    $this->display('admin/member/chkpass.html');
  }

   public function pagination($current,$count,$get){ //本控制器公共分页函数
        $app = app::get('b2c');
        $render = $app->render();
        $ui = new base_component_ui($this->app);
        //unset($get['singlepage']);
        $link = 'index.php?app=b2c&ctl=admin_member&act=ajax_html&id='.$get['id'].'&finder_act='.$get['page'].'&'.$get['page'].'=%d';
        $this->pagedata['pager'] = $ui->pager(array(
                'current'=>$current,
                'total'=>ceil($count/$this->pagelimit),
                'link' =>$link,
                ));
    }

    public function ajax_html()
    {
        $finder_act = $_GET['finder_act'];
        $html = $this->$finder_act($_GET['id']);
        echo $html;
    }

    public function detail_point($member_id=null)
        {
        if(!$member_id) return null;
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $mem_point = $this->app->model('member_point');
         if($member_id)
         {
            $row = $mem_point->getList('id',array('member_id' => $member_id));
            $count = count($row);
        }
        $data = $this->member_model->dump($member_id,'*',array('score/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $this->pagedata['member'] = $data;
        $this->pagedata['event'] = $data['score']['event'];
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_point';
        $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/member/page_point_list.html');
    }

    public function detail_advance($member_id=null)
    {
        if(!$member_id) return null;
        $nPage = $_GET['detail_advance'] ? $_GET['detail_advance'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $mem_adv =  $this->app->model('member_advance');
           $data = $this->member_model->dump($member_id,'*',array('advance/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $items_adv = $data['advance']['event'];
        if($member_id){
             $row = $mem_adv->getList('log_id',array('member_id' => $member_id));
             $count = count($row);
        }
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_advance';
        $this->pagination($nPage,$count,$_GET);
        $this->pagedata['items_adv'] = $items_adv;
        $this->pagedata['member'] = $this->member_model->dump($member_id,'advance');
        echo $this->fetch('admin/member/page_advance_list.html');
    }

    public function detail_order($member_id=null)
    {
        if(!$member_id) return null;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orders = $this->member_model->getOrderByMemId($member_id,$this->pagelimit*($nPage-1),$this->pagelimit);
        $order =  $this->app->model('orders');
        if($member_id){
         $row = $order->getList('order_id',array('member_id' => $member_id));
         $count = count($row);
        }
        foreach($orders as $key=>$order1){
         $orders[$key]['status'] = $order->trasform_status('status',$orders[$key]['status']);
         $orders[$key]['pay_status'] = $order->trasform_status('pay_status',$orders[$key]['pay_status'] );
         $orders[$key]['ship_status'] = $order->trasform_status('ship_status', $orders[$key]['ship_status']);
        }
        $this->pagedata['orders'] = $orders;
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_order';
        $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/member/page_order.html');
    }

    public function detail_msg($member_id=null)
    {
        if(!$member_id) return null;
        $member_id = intval($member_id);
        $nPage = $_GET['detail_msg'] ? $_GET['detail_msg'] : 1;
        $this->db = kernel::database();
        $_count_row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).')');
        $row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).') limit '.$this->pagelimit*($nPage-1).','.$this->pagelimit);
        $count = count($_count_row);
        $this->pagedata['msgs'] =  $row;
        if($_GET['page']) unset($_GET['page']);
         $_GET['page'] = 'detail_msg';
         $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/member/member_msg.html');
    }

}
