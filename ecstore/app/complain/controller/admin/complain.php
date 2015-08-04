<?php
//投诉管理
class complain_ctl_admin_complain extends desktop_controller
{
    var $workground = 'complain_ctl_admin_complain';
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct($app)
	{
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->mdl_complain=$this->app->model('complain');
    }
    public function index(){
        $this->finder('complain_mdl_complain',array(
            'title'=>app::get('ectools')->_('投诉管理'),
            'allow_detail_popup'=>false,
            'actions'=>array(
                         array('label' => "新建", 'href' => 'index.php?app=complain&ctl=admin_complain&act=addnew', 'target' => '_blank'),

			),
            'use_buildin_set_tag'=>true,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
		));
    }
     public function _views(){
        $sub_menu = array(
            0=>array('label'=>app::get('complain')->_('全部'),'optional'=>false,'filter'=>array('disabled'=>'false')),
            1=>array('label'=>app::get('complain')->_('处理中'),'optional'=>false,'filter'=>array('status'=>'intervene','disabled'=>'false')),
            2=>array('label'=>app::get('complain')->_('投诉成立'),'optional'=>false,'filter'=>array('status'=>'success','disabled'=>'false')),
            3=>array('label'=>app::get('complain')->_('投诉不成立'),'optional'=>false,'filter'=>array('status'=>'error','disabled'=>'false')),
            4=>array('label'=>app::get('complain')->_('投诉撤销'),'optional'=>false,'filter'=>array('status'=>'cancel','disabled'=>'false'))
        );
        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                $show_menu[$k]['addon'] = $this->mdl_complain->count($v['filter']);
                $show_menu[$k]['href'] = 'index.php?app=complain&ctl=admin_complain&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }
        }
        return $show_menu;
     }
     public function cprocess($complain_id,$action)
    {
        if (!$complain_id)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("投诉编号传递出错.").'",_:null}';exit;
        }
        $this->pagedata['complain']=array('complain_id'=>$complain_id,'action'=>$action);
        $this->display('admin/complain/goaction.html');
    }
    public function docomplain(){
        $complain_id=$_POST['complain_id'];
        if (!$complain_id)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("投诉编号传递出错.").'",_:null}';exit;
        }
        
        $action=$_POST['status'];
        $data=array();
        $data['complain_id']=$complain_id;
        $data['status']=$action;
        $data['last_modified']=time();
        $data_item=array();
        $data_item['complain_id']=$complain_id;
        $data_item['author_id']=$this->user->user_id;
        $data_item['source']='platform';
        $data_item['author']=$this->user->user_data['account']['login_name'];
        $data_item['comment']=$_POST['memo'];
        $data_item['last_modified']=time();
        $data['complain_comments'][]=$data_item;
        $result=$this->mdl_complain->save($data);
        if($result){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('b2c')->_("操作成功！.").'",_:null}';exit;
        }else{
           header('Content-Type:text/jcmd; charset=utf-8');
           echo '{error:"操作失败",_:null}';exit;
        }
    }


    function addnew(){
        $filter['disabled']='false';
        $filter['filter_sql']=" {table}seller is null ";
        $this->pagedata['memberfilter']= http_build_query($filter);
        $this->singlepage('admin/complain/new.html');
    }

    function toAdd(){

        $complain=$_POST['complain'];

        if($complain['order_id']){  
          $aryorder = app::get('b2c')->model('orders')->getList('store_id,member_id',array('order_id'=>$complain['order_id'],'disabled'=>'false'));
          if($aryorder){
              $store_id= $aryorder[0]['store_id'];
              $buyer_id= $aryorder[0]['member_id'];
          }

        }
      
        if(!$store_id){
           header('Content-Type:text/jcmd; charset=utf-8');
           echo '{error:"操作失败,无相关数据",_:null}';exit;
        }

        if($buyer_id != $complain['from_member_id']){
           header('Content-Type:text/jcmd; charset=utf-8');
           echo '{error:"此订单不是该会员所有",_:null}';exit;

        }

        //店铺及店主信息
        $arystore =  app::get('business')->model('storemanger')->getList('account_id,shop_name,store_name',array('store_id'=>$store_id));
     
        if(!$arystore){
           header('Content-Type:text/jcmd; charset=utf-8');
           echo '{error:"操作失败,无相关数据",_:null}';exit;
        }
        $account_id=$arystore[0]['account_id'];
        $shop_name=$arystore[0]['shop_name'];
        $store_name=$arystore[0]['store_name'];

        //投诉者信息
        $memberinfo=app::get('b2c')->model('members')->get_member_info($buyer_id);
        if($memberinfo){
           $uname=$memberinfo['uname'];
        }

       
        
        $c_id=$this->mdl_complain->gen_id();
        $data=array();
        $data['complain_id']=$c_id;
        $data['order_id']=$complain['order_id'];
        $data['from_member_id']=$complain['from_member_id'];

        $data['to_member_id']=$account_id;
        $data['source']='buyer';
        $data['store_id']=$store_id;
        $data['store_name']=$store_name;
        $data['mobile']=$complain['mobile'];

        $data['from_uname']=$uname;

        $data['to_uname']=$shop_name;

        $data['reason']=$complain['reason'];
        $data['memo']=$complain['memo'];
        $data['status']='intervene';
        $data['createtime']=time();
        $data['last_modified']=time();
        $data_item=array();
        $data_item['complain_id']=$c_id;
        $data_item['author_id']=$complain['from_member_id'];
        $data_item['author']=$uname;
        $data_item['comment']=$data['memo'];

        $data_item['image_0']=$complain['image_0'];
        $data_item['image_1']=$complain['image_1'];
        $data_item['image_2']=$complain['image_2'];

        $data_item['last_modified']=time();
        
        $data['complain_comments'][]=$data_item;  

        $result=$this->mdl_complain->save($data);
        if($result){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('b2c')->_("操作成功！.").'",_:null}';exit;
        }else{
           header('Content-Type:text/jcmd; charset=utf-8');
           echo '{error:"操作失败",_:null}';exit;
        }
  
    }

} 