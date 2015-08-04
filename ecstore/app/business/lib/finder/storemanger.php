<?php
class business_finder_storemanger{
   
    function __construct($app){
        $this->app = $app;
        $this->controller = app::get('business')->controller('admin_storemanger');
        $this->detail_experience = app::get('business')->_('经验值');
        $this->detail_space = app::get('business')->_('图片空间');
        $this->detail_earnest = app::get('business')->_('保证金');
        $this->detail_service = app::get('business')->_('服务统计');
    }

	var $column_control = '操作';
    var $column_control_width = 100;

   
    var $pagelimit = 10;
    
    public function column_control($row){
        $render = $this->app->render();
        $arr = array(
            'app'=>$_GET['app'],
            'ctl'=>$_GET['ctl'],
            'act'=>$_GET['act'],
            'finder_id'=>$_GET['_finder']['finder_id'],
            'action'=>'detail',
            'finder_name'=>$_GET['_finder']['finder_id'],
        );
        $arr_link = array(
            'info'=>array(
                'detail_edit'=>array(
                    'href'=>'index.php?app=business&ctl=admin_storemanger&act=edit&store_id='.$row['store_id'].'&finder_id='.$_GET['_finder']['finder_id'],
                    'label'=>app::get('business')->_('编辑店铺信息'),
                    'target'=>'blank',
                ),
            ),
            'finder'=>array(
                'detail_experience'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_experience&id='.$row['store_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],
                    'label'=>app::get('business')->_('经验值'),
                    'target'=>'tab',
                ),
                'detail_space'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_space&id='.$row['store_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],
                    'label'=>app::get('business')->_('图片空间'),
                    'target'=>'tab',
                ),
            'detail_earnest'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_earnest&id='.$row['store_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],
                    'label'=>app::get('business')->_('保证金'),
                    'target'=>'tab',
                ),
            'detail_service'=>array(
                    'href'=>'javascript:void(0);',
                    'label'=>app::get('business')->_('服务统计'),
                    'target'=>'tab',
                ),
            ),


        );
        /* $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('editexp')){
            unset($arr_link['finder']['detail_experience']);
        }*/

       
        $render->pagedata['arr_link'] = $arr_link;
        $render->pagedata['handle_title'] = app::get('business')->_('编辑');
        $render->pagedata['is_active'] = 'true';
        return $render->fetch('admin/store/manger/actions.html');
    }
    
    function detail_experience($store_id){
        if(!$store_id) return null;
        $nPage = $_GET['detail_experience'] ? $_GET['detail_experience'] : 1;
        $app = app::get('business');
        $obj_store = $app->model('storemanger');
        $obj_user = kernel::single('desktop_user');
        $obj_log = $app->model('store_log');

        if($_POST){
           if(!$obj_store->change_experience($store_id,$_POST['modify_experience'],$msg,$obj_user->user_id,2,null,$_POST['modify_remark'])){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
           }
        }
        $filter = array('store_id'=>$store_id,'type'=>'experience');
        $count = $obj_log->count($filter);
        $data['log'] = $obj_log->getList('*', $filter, $this->pagelimit*($nPage-1), $this->pagelimit, 'last_modify desc');
        $accountObj = app::get('pam')->model('account');
        foreach($data['log'] as $key=>$val){
            if($val['source'] == '1'){
                $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
                $data['log'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
            }else{
                $data['log'][$key]['operator_name'] = '管理员';
            }
        }
        $data['total'] = $obj_store->getList('experience',array('store_id'=>$store_id));
        $data['total'] = $data['total'][0]['experience'];
        $render = $app->render();
        $render->pagedata['store'] = $data;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_experience';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/store/manger/experience_list.html');
    }
    
    function detail_space($store_id){

        //这部分的权限只分配给超级管理员 
        $user= kernel::single('desktop_user');
        if(!$user->is_super()){
           return app::get('desktop')->_("无相应权限，请与超级管理员联系。");
        }

        if(!$store_id) return null;
        $nPage = $_GET['detail_space'] ? $_GET['detail_space'] : 1;
        $app = app::get('business');
        $obj_store = $app->model('storemanger');
        $obj_user = kernel::single('desktop_user');
        $obj_log = $app->model('store_log');

        if($_POST){
            if(!$obj_store->change_spece($store_id,$_POST['modify_space'],$msg,$obj_user->user_id,$_POST['modify_remark'])){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
            }
        }
        $filter = array('store_id'=>$store_id,'type'=>'space');
        $count = $obj_log->count($filter);
        $data['log'] = $obj_log->getList('*', $filter, $this->pagelimit*($nPage-1), $this->pagelimit, 'last_modify desc');
        $accountObj = app::get('pam')->model('account');
        foreach($data['log'] as $key=>$val){
            $data['log'][$key]['change_value'] = floor($data['log'][$key]['change_value']*10/1024/1024/1024)/10;
            if($val['source'] == '1'){
                $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
                $data['log'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
            }else{
                $data['log'][$key]['operator_name'] = '管理员';
            }
        }
        $store_info = $obj_store->getList('store_space,store_usedspace',array('store_id'=>$store_id));
        $data['total'] = floor($store_info[0]['store_space']*10/1024/1024/1024)/10;
        $data['space'] = floor($store_info[0]['store_usedspace']*100/1024/1024/1024)/100;
        $render = $app->render();
        $render->pagedata['store'] = $data;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_space';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/store/manger/space_list.html');
    }
   

    function detail_earnest($store_id){
        if(!$store_id) return null;
        $nPage = $_GET['detail_earnest'] ? $_GET['detail_earnest'] : 1;
        $app = app::get('business');
        $obj_store = $app->model('storemanger');
        $obj_user = kernel::single('desktop_user');
        $obj_log = $app->model('earnest_log');

        if($_POST){
           if(!$obj_store->change_earnest($store_id,$_POST['modify_earnest'],$msg,$obj_user->user_id,2,null,$_POST['modify_remark'])){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
           }
        }

        $filter = array('store_id'=>$store_id);
        $count = $obj_log->count($filter);
        $data['log'] = $obj_log->getList('*', $filter, $this->pagelimit*($nPage-1), $this->pagelimit, 'last_modify desc');
        $accountObj = app::get('pam')->model('account');
        foreach($data['log'] as $key=>$val){
            if($val['source'] == '1'){
                $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
                $data['log'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
            }else{
                $data['log'][$key]['operator_name'] = '管理员';
            }
        }
        $data['total'] = $obj_store->getList('earnest',array('store_id'=>$store_id));
        $data['total'] = $data['total'][0]['earnest'];
        $render = $app->render();
        $render->pagedata['store'] = $data;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_earnest';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/store/manger/earnest_list.html');
    }


    function detail_service($store_id){
        if(!$store_id) return null;
        $app = app::get('business');
        $render = $app->render();

        //删除的评价
        $objrecycle =  &app::get('desktop')->model('recycle');
        
        //评价总数
        $objcomment =  &app::get('b2c')->model('member_comments');
         
        //近期服务情况
        $objorder =  &app::get('b2c')->model('orders');

        //退款
        $total_refunds = $objorder ->report_refunds($store_id);
        if($total_refunds){
            foreach ($total_refunds as $key => $value) {
                 $report_refunds[$value['total_date']]= $value;
            }

            $report = $report_refunds;
        }
        
        //投诉
        $objViolation =  &app::get('business')->model('storeviolation');
        $total_complain = $objViolation->report_complain($store_id);
        if($total_complain){
            foreach ($total_complain as $key => $value) {
                 $report_complains[$value['total_date']]= $value;
            }

            $report = array_merge_recursive($report,$report_complains);
        }


        //评价
        $total_comment = $objcomment->report_comment($store_id);
        if($total_comment){
            foreach ($total_comment as $key => $value) {
                 $report_comment[$value['total_date']]= $value;
            }
             $report = array_merge_recursive($report,$report_comment);
        }

         //中差评
        $total_scorecomment = $objcomment->report_scorecomment($store_id,4);
        if($total_scorecomment){
            foreach ($total_scorecomment as $key => $value) {
                 $report_scorecomment[$value['total_date']]= $value;
            }

             $report = array_merge_recursive($report,$report_scorecomment);
        }

        //删除的评价
        $total_delcomment = $objcomment->report_delcomment($store_id);
        if($total_delcomment){
            foreach ($total_delcomment as $key => $value) {
                 $report_delcomment[$value['total_date']]= $value;
            }

             $report = array_merge_recursive($report,$report_delcomment);

        }

        //违规
       
        $total_violation =  $objViolation->report_violation($store_id); 
        if($total_violation){
            foreach ($total_violation as $key => $value) {
                 $report_violation[$value['total_date']]= $value;
            }
            $report = array_merge_recursive($report,$report_violation);
        }

        //获取同行店铺ID
        $objstoremanger =  &app::get('business')->model('storemanger');
        $regionary= $objstoremanger->getcounteridbystoreid($store_id);

        $counter_refunds = $objorder ->report_counterrefunds(implode($regionary,','));
        if($counter_refunds){
            foreach ($counter_refunds as $key => $value) {
                 $report_counterrefunds[$value['total_date']]= $value;
            }

            $report = array_merge_recursive($report,$report_counterrefunds);
        }

      
       
        $render->pagedata['report'] = $report;

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_service';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/store/report/page_list.html');


    }
}