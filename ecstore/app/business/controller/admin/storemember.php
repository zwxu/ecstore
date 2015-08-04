<?php
class business_ctl_admin_storemember extends desktop_controller{
    var $workground = 'business.wrokground.store';
    var $pagelimit = 10;
    var $member_model ;
    public function __construct($app)
    {
        $this -> app_current = $app;
        parent::__construct($app);
        $this->member_model = $this->app->model('storemember');
      
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
 
        $this->finder('business_mdl_storemember',array(
        'title'=>'店铺店员列表',
        'allow_detail_popup'=>true,
        'use_buildin_export'=>true,
        'use_buildin_set_tag'=>true,
        'use_buildin_filter'=>true,
        'use_view_tab'=>true,
        'actions'=>array(
                        array('label'=>"批量操作",
                        'icon'=>'batch.gif',
                        'group'=>array(
                                //array('label'=>app::get('business')->_('修改角色'),'icon'=>'download.gif','submit'=>'index.php?app=business&ctl=admin_storemember&act=singleBatchEdit&p[0]=storememberroles','target'=>'dialog'),
                                array('label'=>app::get('business')->_('排序'),'icon'=>'download.gif','submit'=>'index.php?app=business&ctl=admin_storemember&act=singleBatchEdit&p[0]=storememberdorder','target'=>'dialog'),
                                ),
                        ),
                        ),
            'object_method' => array('count'=>'count_finder','getlist'=>'get_list_finder'),
        ));
            
    }


    function singleBatchEdit($editType=''){
       
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        if($_POST['isSelectedAll'] == '_ALL_')
            $_POST['attach_id'][0] = '_ALL_';

       
        if(count($_POST['attach_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']){
            echo __('请选择记录');
            exit;
        }
        if($_POST['filter']){
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        }

        if($editType =='storememberroles'){

            //角色基础数据
            $roles = array('0'=>'请选择');
            $m = $this -> app_current -> model('storeroles');
            foreach($m -> getList('*') as $item) {
                $roles[$item['role_id']] = $item['role_name'];
            } 
            $this -> pagedata['roles'] = $roles;

        }

        // print_r($editType);exit;

       

        //选中记录的ID经序列化后保存于页面
        $this->pagedata['filter'] =serialize($_POST['attach_id']);
        $this->pagedata['editInfo']=array('count'=>count($_POST['attach_id']));

        $this->display('admin/store/batch/batchEdit'. $editType .'.html');

        
    }

    function saveBatchEdit(){
        $this->begin('');
        $filter = unserialize($_POST['filter']);

        $objGoods = &$this->app->model('storemember');

        $haserror = false;

        foreach($filter as $key=>$row){

            $storegrade['attach_id'] =$row;

            if($_POST['updateAct'] =='roles'){

                //保存批量操作:角色
                $storegrade['roles_id'] = $_POST['set']['dorder'];

            } elseif($_POST['updateAct'] =='password' )  {

                 //保存批量操作:密码
                $storegrade['shop_password'] = md5(trim($_POST['set']['dorder']));


            } else {
            
                //保存批量操作:排序
                $storegrade['d_order'] = $_POST['set']['dorder'];

            }
           
            $objGoods-> save($storegrade);

        }

        ini_set('track_errors','1');
        restore_error_handler();

        if(!$haserror){
           $this->end(true, app::get('business')->_('批量排序'));
           
        }else{
            echo $GLOBALS['php_errormsg'];
        }

    }





}