<?php
class business_ctl_admin_storeviolation extends desktop_controller {
    var $workground = 'business.wrokground.store';
    var $pagelimit = 10;
    var $member_model ;
    public function __construct($app) {
        parent :: __construct($app);
        $this -> member_model = $this -> app -> model('storemanger');
        header("cache-control: no-store, no-cache, must-revalidate");
    } 
    function index() {
        /*
        $custom_actions[] =  array('label' => "批量操作",
                        'icon' => 'batch.gif',
                        'group' => array(
                            array('label' => app :: get('shop') -> _('排序'), 'icon' => 'download.gif', 'submit' => 'index.php?app=business&ctl=admin_storemanger&act=singleBatchEdit&p[0]=storemangerdorder', 'target' => 'dialog'),
                            ),
                        );
                        */

        $actions_base['title'] = app::get('b2c')->_('店铺违规列表');
        $actions_base['actions'] = $custom_actions;
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_buildin_export'] = true;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['finder_aliasname'] ='index';
        $actions_base['use_view_tab'] = true;
        $actions_base['object_method'] =array('count' => 'count_finder', 'getlist' => 'get_List');
        $this -> finder('business_mdl_storeviolation',$actions_base);
                
    } 


     function indextotal() {
       
        $actions_base['title'] = app::get('b2c')->_('店铺处理列表');
        $actions_base['actions'] = $custom_actions;
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_buildin_export'] = true;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['finder_aliasname'] ='indextotal';
        $actions_base['use_view_tab'] = true;
        $actions_base['object_method'] =array('count' => 'count_finder_total', 'getlist' => 'get_List_total');
        $this -> finder('business_mdl_storeviolation',$actions_base);
                
    } 


    function singleBatchEdit($editType = '') {
        // $objshopinfo = &$this->app->model('storegrade');
        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        if ($_POST['isSelectedAll'] == '_ALL_')
            $_POST['store_id'][0] = '_ALL_';
        if (count($_POST['store_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']) {
            echo __('请选择记录');
            exit;
        } 
        if ($_POST['filter']) {
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        } 
        // 选中记录的ID经序列化后保存于页面
        $this -> pagedata['filter'] = serialize($_POST['store_id']);
        $this -> pagedata['editInfo'] = array('count' => count($_POST['store_id']));

        $this -> display('admin/store/batch/batchEdit' . $editType . '.html');
    } 
  
    function saveBatchEdit() { 
        $filter = unserialize($_POST['filter']); 

        $objGoods = &$this -> app -> model('storemanger');
        $sto= kernel::single("business_memberstore",$shopinfo['account_id']);

        $haserror = false;  

        $this -> begin('');   
        foreach($filter as $key => $row) {
            $storegrade['store_id'] = $row;

            switch($_POST['updateAct']){
                //排序
                default:
                    $storegrade['d_order'] = $_POST['set']['dorder'];
                    break;

            }

            if ($objGoods -> save($storegrade)) {
               
            } 
        } 

        ini_set('track_errors', '1');
        restore_error_handler();

        if (!$haserror) {
            $this -> end(true, app :: get('business') -> _('批量操作成功。'));
        } else {
             echo $GLOBALS['php_errormsg'];
            //$this -> end(false, app :: get('business') -> _('批量操作失败：') . $haserror);
        } 
    } 
  
   
} 
