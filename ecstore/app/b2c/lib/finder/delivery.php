<?php

 
class b2c_finder_delivery{

    var $detail_delivery = '参数设置';
    function __construct($app){
        $this->app = $app;
    }

    function detail_delivery($payment_id){
        $payment = $this->app->model('delivery');
        if($_POST['delivery_id']){
            $sdf = $_POST;
            unset($sdf['_method']);
            if($payment->save($sdf));
            {
                echo 'ok';
            }
        }else{
            $subsdf = array('delivery_items'=>array('*'));
            $sdf_payment = $payment->dump($payment_id, '*', $subsdf);
            if ($sdf_payment)
            {
                $render = $this->app->render();
                
                $render->pagedata['payments'] = $sdf_payment;
                if (isset($render->pagedata['payments']['member_id']) && $render->pagedata['payments']['member_id'])
                {
                    $obj_pam = app::get('pam')->model('account');
                    $arr_pam = $obj_pam->dump(array('account_id' => $render->pagedata['payments']['member_id'], 'account_type' => 'member'), 'login_name');
                    $render->pagedata['payments']['member_id'] = $arr_pam['login_name'];
                }
                if (isset($render->pagedata['payments']['delivery']) && $render->pagedata['payments']['delivery'])
                {
                    $obj_dlytype = $this->app->model('dlytype');
                    $arr_dlytype = $obj_dlytype->dump($render->pagedata['payments']['delivery'], 'dt_name');
                    $render->pagedata['payments']['delivery'] = $arr_dlytype['dt_name'];
                }
                
                if (isset($sdf_payment['delivery_items']) && $sdf_payment['delivery_items'])
                {
                    foreach ($sdf_payment['delivery_items'] as $items)
                    {
                        $obj_product = $this->app->model('products');
                        $arr_products = $obj_product->dump($items['product_id']);
                    }
                }
                
                return $render->fetch('admin/order/delivery.html',$this->app->app_id);
                
                /*$this->ui = new base_component_ui($this);
                $html .= $this->ui->form_start();
                foreach($sdf_payment as $k=>$val){
                    $v['value'] = $val;
                    $v['name'] = $k;
                    $v['type'] = 'label';
                    $v['title'] = $payment->schema['columns'][$k]['label'];
                    $html .= $this->ui->form_input($v);
                }
        
                $html .= $this->ui->form_end(0);
                return $html;*/
            }else{
                return app::get('b2c')->_('无内容');
            }
        }
    }
}
