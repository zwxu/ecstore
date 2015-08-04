<?php

 
//*******************************************************************
//  订单促销规则控制器
//  $ 2010-04-07 16:27 $
//*******************************************************************
class gift_ctl_admin_gift_cat extends desktop_controller{

    public $workground = 'gift_ctl_admin_gift';

    public function index(){
         $this->finder('gift_mdl_cat',array(
            'title'=>app::get('gift')->_('赠品'),
            'actions'=>array(
                            array('label'=>app::get('gift')->_('添加分类'),'icon'=>'add.gif','href'=>'index.php?app=gift&ctl=admin_gift_cat&act=add','target'=>'_blank'),
                        ),
            ));
    }

    /**
     * 添加新规则
     */
    public function add() {
        //$this->_editor();
        $this->singlepage("admin/gift/cat/add.html");
    }

    /**
     * 修改规则
     *
     * @param int $rule_id
     */
    public function edit() {
        $this->begin(app::get('desktop')->router()->gen_url(array('app'=>'gift', 'ctl'=>'admin_gift_cat', 'act'=>'index')));
        if(($id=$_GET['cat_id'])) {
            $arr_info = $this->app->model('cat')->getList('*', array('cat_id'=>$id));
            if(!isset($arr_info[0])) {
                $this->end(false, app::get('gift')->_('操作失败！！信息为空！'));
            } else {
                $this->pagedata['giftcat'] = $arr_info[0];
                $this->add();
            }
        } else {
            $this->end(false, app::get('gift')->_('分类id不能为空！'));
        }
    }

    



    public function toAdd() {
        $this->begin();
        $aData = $_POST['giftcat'];
        
        $obj = $this->app->model("cat");
        if( empty($aData['cat_id']) ) {
        	$arr = $obj->getList('*', array('cat_name'=>$aData['cat_name']));
        	#if( $arr[0] ) $aData['cat_id'] = $arr[0]['cat_id'];
        	if( $arr[0] ) {
        		#$this->end(false, app::get('gift')->_('操作失败!分类名称重复！') );
        	}
        }
        $flag = $obj->save($aData);
        
        $this->end($flag, ($flag ? app::get('gift')->_('操作成功') : app::get('gift')->_('操作失败!')), null, array('cat_id'=>$aData['cat_id']));
    }

}
