<?php
class business_ctl_admin_storegrade extends desktop_controller{
    var $workground = 'business.wrokground.store';
    var $pagelimit = 10;
   
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
  function index(){
 
        $this->finder('business_mdl_storegrade',array(
        'title'=>'等级列表',
        'allow_detail_popup'=>true,
        'use_buildin_export'=>true,
        'use_buildin_set_tag'=>true,
        'use_buildin_filter'=>true,
        'use_view_tab'=>true,
        'actions'=>array(
                        array('label'=>"添加等级",'href'=>'index.php?app=business&ctl=admin_storegrade&act=add_page','target'=>'_blank'),
                        array('label'=>"批量操作",
                        'icon'=>'batch.gif',
                        'group'=>array(
                                array('label'=>app::get('shop')->_('排序'),'icon'=>'download.gif','submit'=>'index.php?app=business&ctl=admin_storegrade&act=singleBatchEdit&p[0]=storegradedorder','target'=>'dialog'),
                                ),
                        ),
                        ),
            'object_method' => array('count'=>'count_finder','getlist'=>'get_list_finder'),
        ));
            
    }


    function singleBatchEdit($editType=''){

       // $objshopinfo = &$this->app->model('storegrade'); 

        $newFilter = $_POST;
        unset($newFilter['app']);
        unset($newFilter['ctl']);
        unset($newFilter['act']);
        unset($newFilter['_finder']);
        unset($newFilter['marketable']);
        unset($newFilter['_DTYPE_BOOL']);

        if($_POST['isSelectedAll'] == '_ALL_')
            $_POST['grade_id'][0] = '_ALL_';
        if(count($_POST['grade_id']) == 0 && $_POST['_finder']['select'] != 'multi' && !$_POST['_finder']['id'] && !$_POST['filter']){
            echo __('请选择记录');
            exit;
        }
        if($_POST['filter']){
            $_POST['_finder'] = unserialize($_POST['filter']);
            $editType = $_POST['updateAct'];
        }

        //选中记录的ID经序列化后保存于页面
        $this->pagedata['filter'] =serialize($_POST['grade_id']);

        $this->display('admin/store/batch/batchEdit'. $editType .'.html');

        
    }

    function saveBatchEdit(){
        $this->begin('');
        $filter = unserialize($_POST['filter']);

        $objGoods = &$this->app->model('storegrade');

        $haserror = false;

        foreach($filter as $key=>$row){

            $storegrade['grade_id'] =$row;

            //保存批量操作:排序
            $storegrade['d_order'] = $_POST['set']['dorder'];
           
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



    function add_page(){
        //店铺类型
        //三种店铺类型：1.旗舰店-放开类目；2.专营店（1种品牌）；3.专卖店（1个品类）
        //2013-6-17 Add  品牌旗舰店   卖场型旗舰店 专营店（1个品类） 专卖店（1种品牌）
        $this->pagedata['issue_type'] =array('0'=>'卖场型旗舰店','3'=>'品牌旗舰店(单一品牌)','1'=>'专卖店(单一品类)','2'=>'专营店(单一品牌)');
        $this->pagedata['title'] = "添加店铺等级";
        $this->singlepage('admin/store/storegradenew.html');
    }


    public function toAdd() {
        $this->begin('');
        $aData = $_POST['storegrade'];
        $aData['certification'] = '1';

        $o = $this->app->model('storegrade');
        
        foreach( $aData as $key => $val ) {
            if( $val=='' ) unset($aData[$key]);
        }
       

        $lv = $o->getList('*',array('default_lv'=>1,'issue_type'=>$aData['issue_type']));
        if($lv){
            if($aData['default_lv'] == 1 && $aData['grade_id'] !=$lv[0]['grade_id']){
               $this -> end(false, app :: get('business') -> _($lv[0]['grade_name'].'已是默认等级，请先取消！' ));
            } elseif($aData['default_lv'] != 1 && $aData['grade_id'] ==$lv[0]['grade_id']){
               $this -> end(false, app :: get('business') -> _('该类型未设置默认等级，不能取消本默认等级！' ));
            }
        } else {
                $aData['default_lv'] = 1 ;
        }

        if($flag = $o->save($aData)) {
            if(! $aData['grade_id'] ) {
                    $this->end(true,app::get('business')->_('保存成功！'. '新增ID为：' . $o -> db -> lastInsertId()));
            } else {
                    $this->end(true,app::get('business')->_('更新成功！'. '更新ID为：' . $aData['grade_id']));
            }
        } else {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{false:"'. app::get('business')->_('失败: 操作失败！') .'",_:null,grade_id:"'.$aData['grade_id'].'"}';
        }
    }


    public function edit() {

        if(($id=$_GET['grade_id'])) {

            $filter = array(
                'grade_id'   => $id,
            );

            $arr_info = $this->app->model('storegrade')->dump($filter,'*','default');
            
            //后台编辑 无商品信息时提示数据错误
            if( !$arr_info ) {
                exit('操作失败！相关商品信息为空！数据异常！！');
            } else {

                $this->pagedata['storegrade'] = $arr_info;
                $this->add_page();
            }
        } else {
            exit('操作失败！相关信息为空！等级id不能为空！');
        }
    }

}