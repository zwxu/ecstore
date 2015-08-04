<?php
 
 
class b2c_ctl_admin_dlytype extends desktop_controller{

    var $workground = 'b2c_ctl_admin_system';

    function index(){
        $this->finder('b2c_mdl_dlytype',array(
            'title'=>app::get('b2c')->_('配送方式'),
            'actions'=>array(
                            array('label'=>app::get('b2c')->_('添加配送方式'),'target'=>'_blank','href'=>'index.php?app=b2c&ctl=admin_dlytype&act=add_dlytype'),
                        )
            ));
    }
    
    public function add_dlytype()
    {
        $oDlyCorp = &$this->app->model('dlycorp');
        $dlycorp = $oDlyCorp->getList('*','',0,-1);
        $this->pagedata['weightunit'] = $this->_weightunit();
        $this->pagedata['config']=array(
                'firstunit' => '1000',
                'continueunit'=>'1000'
        );

        $this->pagedata['clist'] = $dlycorp;
        $this->pagedata['is_delivery_discount_close'] = $this->app->getConf('is_delivery_discount_close');
		$this->pagedata['arr_is_threshold_value'] = array(
            '0'=>app::get('b2c')->_('不启用'),
            '1'=>app::get('b2c')->_('启用'),
        );
        
        $this->singlepage('admin/delivery/dtype_edit.html');
    }
    function get_dlytype($area_id){
        $oDlyType = &$this->app->model('dlytype');
        $a = $oDlyType->get_dlytype($area_id);
    }
    function showEdit($dt_id){
        $oDlyType = &$this->app->model('dlytype');
        $oDlyCorp = &$this->app->model('dlycorp');
        $dlycorp = $oDlyCorp->getList('*','',0,-1);
      
        $dt_info = $oDlyType->dump($dt_id);
        $dt_info['area_fee_conf'] = unserialize($dt_info['area_fee_conf']);
        $dt_info['protect_rate'] = $dt_info['protect_rate']*100;
        $tmp_threshold = array();
        if ($dt_info['is_threshold'])
        {
            if ($dt_info['threshold'])
            {
                $dt_info['threshold'] = unserialize(stripslashes($dt_info['threshold']));
                if (isset($dt_info['threshold']) && $dt_info['threshold'])
                {
                    array_shift($dt_info['threshold']);
                    foreach ($dt_info['threshold'] as $res)
                    {
                        $tmp_threshold[] = array(
                            'area'=>$res['area'][0],
                            'first_price'=>$res['first_price'],
                            'continue_price'=>$res['continue_price'],
                        );                        
                    }
                }
            }
        }
        $dt_info['threshold'] = $tmp_threshold;
        
        $this->pagedata['dt_info'] = $dt_info;
        $this->pagedata['clist'] = $dlycorp;
        $this->pagedata['weightunit'] = $this->_weightunit();
        $this->pagedata['is_delivery_discount_close'] = $this->app->getConf('is_delivery_discount_close');
        $this->pagedata['arr_is_threshold_value'] = array(
            '0'=>app::get('b2c')->_('不启用'),
            '1'=>app::get('b2c')->_('启用'),
        );
		
		/** 
		 * 扩展配送方式的信息
		 */
		if ($obj_dlytype_extension = kernel::service('b2c_dlytype_fixed_time'))
		{
			$this->pagedata['extends_html'] = $obj_dlytype_extension->get_html($dt_info);
		}
        $this->singlepage('admin/delivery/dtype_edit.html');
    }

    function showRegionTreeList($serid,$multi=false){
         if($serid){
         $this->pagedata['sid'] = $serid;
         }else{
         $this->pagedata['sid'] = substr(time(),6,4);
         }
         $this->pagedata['multi'] =  $multi;
         $this->display('regionSelect.html');
    }
    function getRegionById($pregionid){
        $oDlyType = &$this->app->model('dlytype');
        echo json_encode($oDlyType->getRegionById($pregionid));
    }

    public function saveDlType()
    {
        $oObj = &$this->app->model('dlytype');
        // Make the checkbox default value.
        if (!isset($_POST['protect']))
            $_POST['protect'] = '0';
        if (!isset($_POST['def_area_fee']))
            $_POST['def_area_fee'] = '0';
        if ($_POST['has_cod'] == '0')
            $_POST['has_cod'] = 'false';
        else
            $_POST['has_cod'] = 'true';
        if (!$_POST['firstprice'])
            $_POST['firstprice'] = '0';
        if (!$_POST['continueprice'])
            $_POST['continueprice'] = '0';
        if (!$_POST['dt_useexp'])
            $_POST['dt_useexp'] = '0';
        if (!$_POST['ordernum'])
            $_POST['ordernum'] = '50';
        
        $is_saved = $oObj->save($_POST);
		if ($obj_dlytype_extension = kernel::service('b2c_dlytype_fixed_time'))
		{
			$is_saved = $obj_dlytype_extension->save_dlytype($_POST);
		}
		
        if (!$is_saved)
        {
            $this->begin('index.php?app=b2c&ctl=admin_dlytype&act=showEdit&p[0]=' . $_POST['dt_id']);
            $this->end(false, $this->app->_('配送方式保存失败！'));
        }
        else
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.$this->app->_('配送方式保存成功！').'",_:null,dt_id:"'.$_POST['dt_id'].'"}';
        }
    }

    function _weightunit(){
        return array(
            "500"=>app::get('b2c')->_("500克"),
            "1000"=>app::get('b2c')->_("1公斤"),
            "1200"=>app::get('b2c')->_("1.2公斤"),
            "2000"=>app::get('b2c')->_("2公斤"),
            "5000"=>app::get('b2c')->_("5公斤"),
            "10000"=>app::get('b2c')->_("10公斤"),
            "20000"=>app::get('b2c')->_("20公斤"),
            "50000"=>app::get('b2c')->_("50公斤")

        );
    }
    
    function checkExp(){
        $oObj = &$this->app->model('dlytype');
        $this->pagedata['expressions'] = $_GET['expvalue'];
        $this->display('admin/delivery/check_exp.html');
    }
}
