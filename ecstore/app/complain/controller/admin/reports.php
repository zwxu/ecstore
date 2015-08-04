<?php
//投诉管理
class complain_ctl_admin_reports extends desktop_controller
{
    var $workground = 'complain_ctl_admin_reports';
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct($app)
	{
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->mdl_complain=$this->app->model('reports');
    }
    public function index(){
        $this->finder('complain_mdl_reports',array(
            'title'=>app::get('ectools')->_('举报管理'),
            'allow_detail_popup'=>false,
            'actions'=>array(
             array('label' => "新建", 'href' => 'index.php?app=complain&ctl=admin_reports&act=addnew', 'target' => '_blank'),
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
            2=>array('label'=>app::get('complain')->_('举报成立'),'optional'=>false,'filter'=>array('status'=>'success','disabled'=>'false')),
            3=>array('label'=>app::get('complain')->_('举报不成立'),'optional'=>false,'filter'=>array('status'=>'error','disabled'=>'false')),
            4=>array('label'=>app::get('complain')->_('举报撤销'),'optional'=>false,'filter'=>array('status'=>'cancel','disabled'=>'false'))
        );
        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                $show_menu[$k]['addon'] = $this->mdl_complain->count($v['filter']);
                $show_menu[$k]['href'] = 'index.php?app=complain&ctl=admin_reports&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }
        }
        return $show_menu;
     }
     public function cprocess($reports_id,$action)
    {
        if (!$reports_id)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("举报编号传递出错.").'",_:null}';exit;
        }
        $this->pagedata['complain']=array('reports_id'=>$reports_id,'action'=>$action);
        $this->display('admin/reports/goaction.html');
    }
    public function docomplain(){
        $complain_id=$_POST['reports_id'];   
        if (!$complain_id)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("举报编号传递出错.").'",_:null}';exit;
        }
        
        $action=$_POST['status'];
        $data=array();
        $data['reports_id']=$complain_id;
        $data['status']=$action;
        $data['last_modified']=time();
        $data_item=array();
        $data_item['reports_id']=$complain_id;
        $data_item['author_id']=$this->user->user_id;
        $data_item['source']='platform';
        $data_item['author']=$this->user->user_data['account']['login_name'];
        $data_item['comment']=$_POST['memo'];
        $data_item['last_modified']=time();
        $data['reports_comments'][]=$data_item;
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


        //$this -> page('admin/reports/new.html');
         $this->singlepage('admin/reports/new.html');


    }

    function toAdd(){

        $reports=$_POST['reports'];

        if($reports['goods_id']){
          $arygoods = app::get('b2c')->model('goods')->getList('store_id',array('disable'=>false,'goods_id'=>$reports['goods_id']));
          if($arygoods){
              $store_id=  $arygoods[0]['store_id'];
          }

        }

        if(!$store_id){
           header('Content-Type:text/jcmd; charset=utf-8');
           echo '{error:"操作失败,无相关数据",_:null}';exit;
        }

        $arystore =  app::get('business')->model('storemanger')->getList('account_id,shop_name',array('store_id'=>$store_id));
     
        if(!$arystore){
           header('Content-Type:text/jcmd; charset=utf-8');
           echo '{error:"操作失败,无相关数据",_:null}';exit;
        }
        $account_id=$arystore[0]['account_id'];
        $shop_name=$arystore[0]['shop_name'];


        $c_id=$this->mdl_complain->gen_id();
        $data=array();
        $data['reports_id']=$c_id;
        $data['goods_id']=$reports['goods_id'];
        $data['member_id']=$reports['member_id'];

        $memberinfo=app::get('b2c')->model('members')->get_member_info($reports['member_id']);
        if($memberinfo){
            $uname=$memberinfo['uname'];

        }
        
        $data['store_member_id']=$account_id;
        $data['store_id']= $store_id;
        $data['store_uname']=$shop_name;

        $data['cat_id']  =$reports['cat_id']; 
        $data['mobile']=$reports['mobile'];
        $data['memo']=$reports['memo'];
        $data['status']='intervene';
        $data['createtime']=time();
        $data['last_modified']=time();

        $data_item=array();
        $data_item['reports_id']=$c_id;
        $data_item['author_id']=$data['member_id'];
        $data_item['comment']=$data['memo'];
        $data_item['author']=$uname;

        
        $data_item['image_0']=$reports['image_0'];
        $data_item['image_1']=$reports['image_1'];
        $data_item['image_2']=$reports['image_2'];

        $data_item['last_modified']=time();
        $data['reports_comments'][]=$data_item;

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