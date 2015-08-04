<?php
 
 
//*******************************************************************
//  订单促销规则控制器
//  $ 2010-04-07 16:27 $
//*******************************************************************
class b2c_ctl_admin_sales_order extends desktop_controller{

    public $workground = 'b2c_ctl_admin_sales_coupon';

    public function index(){
        $this->finder('b2c_mdl_sales_rule_order',array(
            'title'=>app::get('b2c')->_('订单促销规则'),
            'actions'=>array(
                            array('label'=>app::get('b2c')->_('添加规则'),'href'=>'index.php?app=b2c&ctl=admin_sales_order&act=add','target'=>'_blank'),
                        ),
            'base_filter'=>array('rule_type'=>'N')
            ));
    }

    /**
     * 添加新规则
     */
    public function add() {
        $this->pagedata['rule']['sort_order'] = 50;
        $this->_editor();
    }

    /**
     * 修改规则
     *
     * @param int $rule_id
     */
    public function edit($rule_id) {
        $mOrderPromotion = $this->app->model("sales_rule_order");
        $aRule = $mOrderPromotion->dump($rule_id,'*','default');

        ///////////////////////////// 规则信息 ////////////////////////////
        $aRule['member_lv_ids'] = empty($aRule['member_lv_ids'])? null :explode(',',$aRule['member_lv_ids']);
        $aRule['conditions'] = empty($aRule['conditions'])? null : ($aRule['conditions']);
        $aRule['action_conditions'] = empty($aRule['conditions'])? null : ($aRule['action_conditions']);
        $aRule['action_solutions'] = empty($aRule['action_solutions'])? null : ($aRule['action_solutions']);
        $this->pagedata['rule'] = $aRule;

        ///////////////////////////// 过滤条件 ////////////////////////////
        $oSOP = kernel::single('b2c_sales_order_process');
        $aHtml = $oSOP->getTemplate($aRule['c_template'],$aRule);
        $this->_block($aHtml);

        ///////////////////////////// 优惠方案 ////////////////////////////
        $aRule['action_solution'] = empty($aRule['action_solution'])? null : ($aRule['action_solution']);
        $oSSP = kernel::single('b2c_sales_solution_process');
        $this->pagedata['solution_type'] = $oSSP->getType($aRule['action_solution'], $aRule['s_template']);
        $html = $oSSP->getTemplate($aRule['s_template'],$aRule['action_solution'], $this->pagedata['solution_type']);
        $this->pagedata['action_solution_name'] = $aRule['s_template'];
        $this->pagedata['action_solution'] = $html;

        $this->_editor( $rule_id );
    }

    /**
     * add & edit 公共部分
     *
     */
    private function _editor( $rule_id=0 ) {
        //排斥状态显示优先级项  默认加载 addtime:14:09 2010-8-19
        $time = time();
        $filter = array('from_time|sthan'=>$time, 'to_time|bthan'=>$time, 'status'=>'true', 'rule_type'=>'N');
        if( $rule_id ) $filter['rule_id|noequal'] = $rule_id;
        $arr = $this->app->model('sales_rule_order')->getList( 
                                                        'name,sort_order', 
                                                        $filter,
                                                        0,-1,'sort_order ASC'
                                                    );
        $this->pagedata['sales_list'] = $arr;
        $arr = null;
        //end  
        
        
        $this->pagedata['promotion_type'] = 'order'; // 规则类型 用于公用模板

        ////////////////////////////  模块  ////////////////////////////////
        $this->pagedata['sections'] = $this->_sections();

        //////////////////////////// 会员等级 //////////////////////////////
        $mMemberLevel = &$this->app->model('member_lv');
        $this->pagedata['member_level'] = $mMemberLevel->getList('member_lv_id,name', array(), 0, -1, 'member_lv_id ASC');

        //////////////////////////// 过滤条件模板 //////////////////////////////
        $oSOP = kernel::single('b2c_sales_order_process');
        $this->pagedata['pt_list'] = $oSOP->getTemplateList();

        //////////////////////////// 优惠方案模板 //////////////////////////////
        $oSSP = kernel::single('b2c_sales_solution_process');
        $this->pagedata['stpl_list'] = $oSSP->getTemplateList();

        header("Cache-Control:no-store");
        $this->singlepage('admin/sales/promotion/frame.html');
    }

    private function _sections() {
       return  array(
                 'basic'=> array(
                             'label'=>app::get('b2c')->_('基本信息'),
                             'options'=>'',
                             'file'=>'admin/sales/promotion/basic.html',
                           ), // basic
               'conditions'=> array(

                                'label'=>app::get('b2c')->_('优惠条件'),

                                'options'=>'',
                                'file'=>'admin/sales/promotion/conditions.html',
                              ), // conditions
               'solution'=> array(
                              'label'=>app::get('b2c')->_('优惠方案'),
                              'options'=>'',
                              'file'=>'admin/sales/promotion/solution.html',
                            ), // solutions
             );
    }

    public function toAdd() {
        $this->begin();
        $aData = $this->_prepareRuleData($_POST);
        if (isset($aData['conditions']['conditions'][0]['value']) && $aData['conditions']['conditions'][0]['value']){
            if(floatval($aData['conditions']['conditions'][0]['value']) <= 0){
                $this->end( false,'请输入正数！'  );
            }
        }
        if (isset($aData['action_solution']['b2c_promotion_solutions_byfixed']['total_amount']) && $aData['action_solution']['b2c_promotion_solutions_byfixed']['total_amount']){
            if(floatval($aData['action_solution']['b2c_promotion_solutions_byfixed']['total_amount']) <= 0){
                $this->end( false,'请输入正数！'  );
            }
        }
        $mSRO = $this->app->model("sales_rule_order");
        $bResult = $mSRO->save($aData);

        $this->end($bResult,app::get('b2c')->_('操作成功'));
    }

    /**
     * 这个MS可以放入model里处理
     */
    private function _prepareRuleData($aData) {
        ///////////////////////////// 基本信息 //////////////////////////////////
        $aResult = $aData['rule'];
        
        if( !$aResult['name'] ) $this->end( false,'促销名称不能为空！' );

        // 开始时间&结束时间
        foreach ($aData['_DTIME_'] as $val) {
            $temp['from_time'][] = $val['from_time'];
            $temp['to_time'][] = $val['to_time'];
        }
        $aResult['from_time'] = strtotime($aData['from_time'].' '. implode(':', $temp['from_time']));
        $aResult['to_time'] = strtotime($aData['to_time'].' '. implode(':', $temp['to_time']));
        if( $aResult['to_time']<=$aResult['from_time'] ) $this->end( false,'结束时间不能小于开始时间！' );
        
        // 会员等级
        $aResult['member_lv_ids'] = empty($aResult['member_lv_ids'])? null : implode(',',$aResult['member_lv_ids']);

        // 创建时间 (修改时不处理)
        if(empty($aResult['rule_id'])) $aResult['create_time'] = time();

        ////////////////////////////// 过滤规则 //////////////////////////////////
        $aResult['conditions'] = empty($aData['conditions'])? ( array('type'=>'combine','conditions'=>array())) : ($aData['conditions']);
        //if(is_null($aResult['conditions'])) $aResult['c_template'] = null;
        $aResult['action_conditions'] = empty($aData['action_conditions'])? ( array('type'=>'product_combine','conditions'=>array())) : ($aData['action_conditions']);

        ////////////////////////////// 优惠方案 //////////////////////////////////
        if ($aData['action_solution']['progetcoupon_promotion_solutions_getcoupon']['cpns_id']){
            if (!is_array($aData['action_solution']['progetcoupon_promotion_solutions_getcoupon']['cpns_id'])){
                $this->end( false,'请选择至少一张优惠券' );
                $aData['action_solution']['progetcoupon_promotion_solutions_getcoupon']['cpns_id'] = null;
            }
        }
        $aResult['action_solution'] = empty($aData['action_solution'])? null : ($aData['action_solution']);
        if( empty($aResult['sort_order']) && $aResult['sort_order']!==0 )
            $aResult['sort_order'] = 50;
        
        if( $aResult['sort_order'] ) $aResult['sort_order'] = (int)$aResult['sort_order'];
        
        /** 
         * 校验删选相应的表单元素
         */
	if(is_null($aData['rule']['c_template'])){
		$this->end(false,'优惠条件必选一项');
	}

        if(is_null($aData['rule']['s_template'])){
                $this->end(false,'优惠方案必选一项');
        }

        $obj_rule_c_template = kernel::single($aData['rule']['c_template']);
        if ($obj_rule_c_template){
            if (method_exists($obj_rule_c_template, 'verify_form'))
                if (!$obj_rule_c_template->verify_form($aData,$msg)){
                    $this->end( false, $msg);
                }
        }
        
        $obj_rule_s_template = kernel::single($aData['rule']['s_template']);
        if ($obj_rule_s_template){
            if (method_exists($obj_rule_s_template, 'verify_form'))
                if (!$obj_rule_s_template->verify_form($aData,$msg)){
                    $this->end( false, $msg);
                }
        }
        
        if($aResult['c_template'] == "proqgoods_conditions_goods_goodsofquantity" && (strpos($aResult['conditions']['conditions'][0]['value'],".") || strpos($aResult['conditions']['conditions'][0]['value'],".") === 0)){
            $goods_id = $aResult['conditions']['conditions'][0]['conditions'][0]['value'];
            $goodsinfo = $this->app->model('goods')->getList('type_id',array('goods_id'=>$aResult['conditions']['conditions'][0]['conditions'][0]['value']));
            $typeinfo = $this->app->model('goods_type')->getList('floatstore',array('type_id'=>$goodsinfo[0]['type_id']));
            if(!$typeinfo[0]['floatstore'])
                $aResult['conditions']['conditions'][0]['value'] = floor($aResult['conditions']['conditions'][0]['value']);
        }
        return $aResult;
    }

    private function _block($aHtml) {
        if((empty($aHtml)) || ( is_array($aHtml) && (empty($aHtml['conditions']) || empty($aHtml['action_conditions']))) ) die("<b align=\"center\">".app::get('b2c')->_("模板生成失败")."</b>");
        if(is_array($aHtml)) {
            $this->pagedata['conditions'] = $aHtml['conditions'];
            $this->pagedata['action_conditions'] = $aHtml['action_conditions'];
            $this->pagedata['multi_conditions'] = true;
        } else {
            $this->pagedata['multi_conditions'] = false;
            $this->pagedata['conditions'] = $aHtml;
        }
    }

    /**
     * 获取指定模板
     */
    public function template(){
        $oSOP = kernel::single('b2c_sales_order_process');  //$aData['action_conditions']
        // 只载入模板 有值的话也是没什么用的 
         $storeid =$_POST['storeid'];
        if($storeid){
            $store_id = explode(',',$_POST['storeid']);  

             foreach($store_id as $key => $val) {
                 if ($val == '') unset($store_id[$key]);
             } 
             sort($store_id);
            $aHtml = $oSOP->getTemplate($_POST['template'],
               
            array('conditions'=>array('storeid_filter' =>array('store_id'=>$store_id)),
                      'action_conditions'=>array('storeid_filter' =>array('store_id'=>$store_id))
                     )

            );

          

        } else {

            $aHtml = $oSOP->getTemplate($_POST['template']);

        }

        $this->_block($aHtml);
        $this->display('admin/sales/promotion/order_rule.html');
    }



    /**
     * 用于优惠方案获取模板
     */
    public function solution() {
        $oSSP = kernel::single('b2c_sales_solution_process');

        $storeid =$_POST['storeid'];
         // print_r($storeid);exit;

        // 只载入模板 这里只是选择模板
        $html = $oSSP->getTemplate($_POST['template'], array(), $_POST['type']);
        if(empty($html)) die("<b align=\"center\">".app::get('b2c')->_("模板生成失败")."</b>");

        $this->pagedata['conditions'] = $html;
        $this->display('admin/sales/promotion/goods_rule.html');
    }



    /**
     * 选择条件
     *
     */
    public function conditions(){
        // 传入的值为空的处理
        if(empty($_POST)) exit;

        // vpath
        $_POST['path'] .= '[conditions]['.$_POST['position'].']';
        $_POST['level'] += 1;

        $oSOP = kernel::single('b2c_sales_order_process');
        echo $oSOP->makeCondition($_POST);
    }
}
