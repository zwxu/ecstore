<?php

class business_ctl_admin_violation extends desktop_controller{
    var $workground = 'business.wrokground.store';

    public function __construct($app){
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $custom_actions[] = array('label' => "添加", 'href' => 'index.php?app=business&ctl=admin_violation&act=add_page&p[0]=add', 'target' => '_blank');
        $actions_base['title'] = app::get('b2c')->_('违规处理内容');
        $actions_base['actions'] = $custom_actions;
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_buildin_export'] = true;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['use_view_tab'] = true;
        $actions_base['object_method'] =array('count' => 'count_finder', 'getlist' => 'getList');
       
        $this -> finder('business_mdl_violation',$actions_base);

       
    }

    function add_page($title='') {

        if($title){
            $this -> pagedata['title'] = "添加"; 
        }else {
            $this -> pagedata['title'] = "编辑"; 
        }

        $othis = &$this -> app -> model('violation');
        $aryval= $othis->getList('cat_id',array('disable'=>false));

        if($aryval){
             foreach($aryval as $key => $row) {
                 if($title == $row['cat_id']){
                     continue;
                 }
                 $aryRe[]=$row['cat_id'];
                 
             }
             
             $filter=array('parent_id'=>0,'cat_id|notin'=>$aryRe);
        }else {
             $filter=array('parent_id'=>0);
        }

        $o = &$this -> app -> model('violationcat'); 
        $arycat_id= $o->getList('cat_id,cat_name', $filter);

        $this -> pagedata['arycat_id'] =$arycat_id;


        $this -> singlepage('admin/store/violation/new.html');
    } 

    public function toAdd() {
        $this -> begin(''); 
        $aData = $this -> _prepareData($_POST['violation']);
        $o = &$this -> app -> model('violation'); 
        if ($o -> save($aData)) {
            if($_POST['__type'] == 'add') {
                $this -> end(true, app :: get('business') -> _('保存成功！' . '新增ID为：' . $o -> db -> lastInsertId()));
            }else {
                $this -> end(true, app :: get('business') -> _('更新成功！' . '更新ID为：' . $aData['violation_id']));
            }
        } else {
            $this -> end(false, app :: get('business') -> _('失败: 操作失败！' . '更新ID为：' . $aData['violation_id']));
        }

    }

    function _prepareData(&$aData) {  

       foreach($aData as $key => $val) {
           if ($val == '') unset($aData[$key]);
       }

       if(!$aData['violation_id']){
            $o = &$this -> app -> model('violation'); 
            $esData=$o->getList('*',array('cat_id'=>$aData['cat_id']));
            if($esData){
                $aData['violation_id']=$esData[0]['violation_id'];
            }
       }
       return  $aData;
    }

    public function edit() {
        if (($id = $_GET['violation_id'])) {
            $filter = array('violation_id' => $id);
            $arr_info = $this -> app -> model('violation') -> getList( '*', $filter);
           
            // 后台编辑 无商品信息时提示数据错误
            if (!$arr_info) {
                exit('操作失败！相关信息为空！数据异常！！');
            } else {
                $this -> pagedata['violation'] = $arr_info[0];
                $this -> add_page($arr_info[0]['cat_id']);
            } 
        } else {
            exit('操作失败！相关信息为空！店铺id不能为空！');
        } 
    }
    
}
