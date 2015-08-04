<?php
class business_ctl_site_coupon_order extends site_controller{

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
       $render = $this->app->render(); 
       
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
                array('conditions'=>array('storeid_filter' =>array('store_id'=>$store_id),
                                           'isfront' =>'true'),
                      'action_conditions'=>array('storeid_filter' =>array('store_id'=>$store_id),
                                           'isfront' =>'true')
                     )
            );

        } else {

            $aHtml = $oSOP->getTemplate($_POST['template']);

        }

       $this->_block($aHtml);
      
       echo   $render->fetch('site/store/promotion/order_rule.html');
       
      
    }



    /**
     * 用于优惠方案获取模板
     */
    public function solution() {

        $render = $this->app->render();
        $oSSP = kernel::single('b2c_sales_solution_process');
        // 只载入模板 这里只是选择模板

        //设置前后台区分。
        $aData[$_POST['template']]['isfront'] ="true";

        //前台只有一个店铺
        if($_POST['store_id']){
            $aData[$_POST['template']]['store_id'] = ','.$_POST['store_id'].',';
        }
       
        $html = $oSSP->getTemplate($_POST['template'], $aData, $_POST['type']);
        if(empty($html)) die("<b align=\"center\">".app::get('b2c')->_("模板生成失败")."</b>");

        $this->pagedata['conditions'] = $html;
        //$this->display('site/store/promotion/goods_rule.html');

         echo   $render->fetch('site/store/promotion/goods_rule.html');
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
