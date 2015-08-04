<?php

 
class pointprofessional_point_discount
{
    /**
     * 订单总计额外的字段
     */
    private $payment_detail_extends = array();

    /**
     * 订单总金额
     */
    private $total_amount='0.00';
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }

    /**
     * 设置订单最大金额
     * @param float 订单总金额
     * @return null
     */
    public function set_order_total($total='0.00')
    {
        $this->total_amount = $total;
    }

    /**
     * 折扣的html
     * @param null
     * @return string html
     */
    public function get_html($member_id=0)
    {
        if ($member_id)
        {
            $render = $this->app->render();
            $app_b2c = app::get('b2c');
            $site_get_policy_method = $app_b2c->getConf('site.get_policy.method');
            $site_point_usage = $app_b2c->getConf('site.point_usage');
            if ($site_get_policy_method != '1' && $site_point_usage == '2')
            {
                $site_point_deductible_value = $app_b2c->getConf('site.point_deductible_value');
                $obj_extend_point = kernel::service('b2c.member_extend_point_info');
                if ($obj_extend_point)
                {
                    // 当前会员实际可以使用的积分
                    $obj_extend_point->get_usage_point($member_id, $real_usage_point);
                }
                $render->pagedata['discount_rate'] = $site_point_deductible_value;
                if ($real_usage_point < 0)
                    $real_usage_point = 0;
                $render->pagedata['real_usage_point'] = $real_usage_point;
                $site_point_max_deductible_method = $app_b2c->getConf('site.point_max_deductible_method');
                $site_point_max_deductible_value = $app_b2c->getConf('site.point_max_deductible_value');
                $objMath = kernel::single("ectools_math");
                if ($site_point_max_deductible_method == '1')
                {
                    $render->pagedata['max_discount_value'] = $site_point_max_deductible_value;
                }
                else
                {
                    $render->pagedata['max_discount_value'] = $objMath->number_multiple($this->total_amount, $site_point_max_deductible_value);
                }
                if ($render->pagedata['max_discount_value'] < 0)
                    $render->pagedata['max_discount_value'] = 0;
                return $render->fetch('site/cart/point_dis.html');
            }
            else
            {
                return '';
            }
        }
        
        return '';
    }

    /**
     * 折扣的javascript
     * @param null
     * @return string javascript
     */
    public function get_javascript($member_id=0)
    {
        if ($member_id)
        {
            $render = $this->app->render();
            $site_point_usage = app::get('b2c')->getConf('site.point_usage');
            if ($site_point_usage == '2')
                return $render->fetch('site/cart/js/point.js');
            else
                return '';
        }
        
        return '';
    }

    /**
     * 折扣金额的计算
     * @param array order post
     * @param float total amount
     * @param int total consume score
     * @param int total discount score
     * @param int cost_freight 运费
     * @return string 'true' or false
     */
    public function generate_total($sdf_order=array(), &$total_amount, &$subtotal_consume_score, &$total_point,$cost_freight=null)
    {
        if ($sdf_order['member_id'])
        {
            if ($sdf_order['dis_point'] && $sdf_order['dis_point'] > 0)
            {
                $over_real_point = 'false';
                
                $site_point_deductible_value = app::get('b2c')->getConf('site.point_deductible_value');
                $site_point_max_deductible_method = app::get('b2c')->getConf('site.point_max_deductible_method');
                $site_point_max_deductible_value = app::get('b2c')->getConf('site.point_max_deductible_value');
                $objMath = kernel::single('ectools_math');
                $point_dis_value = $objMath->number_multiple(array($site_point_deductible_value, $sdf_order['dis_point']));
                $obj_member = $this->app->model('members');
                $real_total_point = $obj_member->get_real_point($sdf_order['member_id']);
                $real_total_point = $real_total_point - $obj_member->get_freez_point($sdf_order['member_id']);
                if ($real_total_point < 0)
                    $real_total_point = 0;
                $real_total_money = $objMath->number_multiple(array($real_total_point, $site_point_deductible_value));
                
                switch ($site_point_max_deductible_method)
                {
                    case '1':// 每一笔订单最大的抵扣金额。				
                        if ($point_dis_value > $site_point_max_deductible_value)
                        {
                            $point_dis_value = $site_point_max_deductible_value;
                        }
                        if ($site_point_max_deductible_value > $total_amount)
                            $max_point_amount = $total_amount;
                        else
                            $max_point_amount = $site_point_max_deductible_value;
                        if ($max_point_amount > $real_total_money)
                            $max_point_amount = $real_total_money;
                        if ($point_dis_value > $max_point_amount)
                            $point_dis_value = $max_point_amount;
                        $total_point = floor($objMath->number_div(array($site_point_max_deductible_value, $site_point_deductible_value)));
                        $subtotal_consume_score = floor($objMath->number_div(array($point_dis_value, $site_point_deductible_value)));
                        $point_dis_value = $objMath->number_multiple(array($site_point_deductible_value, $subtotal_consume_score));
                        if ($point_dis_value > $real_total_money){
                            $point_dis_value = $real_total_money;
                        }
                        break;
                    case '2':// 每一笔订单最大的抵扣比例。
                        if ($site_point_max_deductible_value > 1)
                        {
                            $site_point_max_deductible_value = 1;
                        }
                        $max_point_amount = $objMath->number_multiple(array($total_amount, $site_point_max_deductible_value));
                        //有运费时 start 
                        if($cost_freight && $cost_freight > 0){
                            $total_dis_consume_money = $total_amount - $cost_freight;
                            $max_point_amount = $objMath->number_multiple(array($total_dis_consume_money, $site_point_max_deductible_value));;
                        }
                        //有运费时 end 
                        if ($point_dis_value > $max_point_amount)
                        {
                            $point_dis_value = $max_point_amount;
                        }
                        if ($max_point_amount > $real_total_money)
                            $max_point_amount = $real_total_money;
                        $total_point = floor($objMath->number_div(array($max_point_amount, $site_point_deductible_value)));
                        $subtotal_consume_score = floor($objMath->number_div(array($point_dis_value, $site_point_deductible_value)));
                        $point_dis_value = $objMath->number_multiple(array($site_point_deductible_value, $subtotal_consume_score));
                        if ($point_dis_value > $real_total_money){
                            $point_dis_value = $real_total_money;
                        }
                        break;
                }
                
                if ($subtotal_consume_score > $real_total_point)
                {
                    $subtotal_consume_score = $real_total_point;					
                    $over_real_point = 'true';
                }
                
                if ($total_point > $real_total_point)
                {
                    $total_point = $real_total_point;
                }
                
                if ($objMath->number_minus(array($total_amount, $point_dis_value)) < 0)
                    $over_real_point = 'true';
                $total_amount = $objMath->number_minus(array($total_amount, $point_dis_value));
                $total_dis_consume_money = $point_dis_value;
                if ($objMath->number_multiple(array($site_point_deductible_value, $sdf_order['dis_point'])) > $total_dis_consume_money)
                {
                    $over_real_point = 'true';
                }
                if ($max_point_amount < 0)
                    $max_point_amount = $objMath->number_plus(array(0,0));
                // 计算整数的最大可以抵扣金额
                $max_points = floor($objMath->number_div(array($max_point_amount, $site_point_deductible_value)));
                $max_point_amount = $objMath->number_multiple(array($site_point_deductible_value, $max_points));
                if ($total_dis_consume_money < 0)
                    $total_dis_consume_money = $objMath->number_plus(array(0,0));
                if ($subtotal_consume_score < 0)
                    $subtotal_consume_score = 0;
                $this->payment_detail_extends = array(
                    'site_point_max_deductible_method'=>$site_point_max_deductible_method,
                    'site_point_deductible_value'=>$site_point_deductible_value,
                    'total_discount_consume_score'=>$subtotal_consume_score,
                    'total_point'=>$real_total_point,
                    'over_real_point'=>$over_real_point,
                    'total_dis_consume_money'=>$total_dis_consume_money,
                    'total_real_dis_total_money'=>$real_total_money,
                    'total_dis_money'=>$max_point_amount,
                    'total_dis_point'=>$max_points,
                );
                return $over_real_point;
            }
            else
            {
                $site_point_deductible_value = app::get('b2c')->getConf('site.point_deductible_value');
                $site_point_max_deductible_method = app::get('b2c')->getConf('site.point_max_deductible_method');
                $site_point_max_deductible_value = app::get('b2c')->getConf('site.point_max_deductible_value');
                $obj_member = $this->app->model('members');
                $real_total_point = $obj_member->get_real_point($sdf_order['member_id']);
                $real_total_point = $real_total_point - $obj_member->get_freez_point($sdf_order['member_id']);
                $objMath = kernel::single('ectools_math');
                $real_total_money = $objMath->number_multiple(array($real_total_point, $site_point_deductible_value));
                
                switch ($site_point_max_deductible_method)
                {
                    case '1':// 每一笔订单最大的抵扣金额。				
                        if ($total_amount > $site_point_max_deductible_value)
                        {
                            $total_dis_consume_money = $site_point_max_deductible_value;
                        }
                        else
                        {
                            $total_dis_consume_money = $total_amount;
                        }
                        if ($total_dis_consume_money > $real_total_money)
                            $total_dis_consume_money = $real_total_money;
                        $total_point = floor($objMath->number_div(array($site_point_max_deductible_value, $site_point_deductible_value)));
                        $subtotal_consume_score = 0;	
                        break;
                    case '2':// 每一笔订单最大的抵扣比例。
                        if ($site_point_max_deductible_value > 1)
                        {
                            $site_point_max_deductible_value = 1;
                        }
                        $max_point_amount = $objMath->number_multiple(array($total_amount, $site_point_max_deductible_value));

                        //有运费时 
                        if($cost_freight && $cost_freight > 0){
                            $total_dis_consume_money = $total_amount - $cost_freight;
                            $max_point_amount = $objMath->number_multiple(array($total_dis_consume_money, $site_point_max_deductible_value));
                        }
                        //有运费时 

                        if (1 >= $site_point_max_deductible_value)
                        {
                            $total_dis_consume_money = $max_point_amount;
                        }
                        else
                        {
                            $total_dis_consume_money = $total_amount;
                        }
                        if ($total_dis_consume_money > $real_total_money)
                            $total_dis_consume_money = $real_total_money;
                        $total_point = floor($objMath->number_div(array($max_point_amount, $site_point_deductible_value)));
                        $subtotal_consume_score = 0;
                        break;
                }
                
                if ($total_dis_consume_money < 0)
                    $total_dis_consume_money = $objMath->number_plus(array(0,0));
                // 计算整数的最大可以抵扣金额
                $max_points = floor($objMath->number_div(array($total_dis_consume_money, $site_point_deductible_value)));
                $total_dis_consume_money = $objMath->number_multiple(array($site_point_deductible_value, $max_points));
                if ($subtotal_consume_score < 0)
                    $subtotal_consume_score = 0;
                $this->payment_detail_extends = array(
                    'site_point_max_deductible_method'=>$site_point_max_deductible_method,
                    'site_point_deductible_value'=>$site_point_deductible_value,
                    'total_discount_consume_score'=>$subtotal_consume_score,
                    'total_point'=>$real_total_point,
                    'over_real_point'=>$over_real_point,
                    'total_dis_consume_money'=>0,
                    'total_real_dis_total_money'=>$real_total_money,
                    'total_dis_money'=>$total_dis_consume_money,
                    'total_dis_point'=>$max_points,
                );
                
                return 'false';
            }
            
            return 'false';
        }
        
        return 'false';
    }

    /**
     * 结算页面生成订单总计
     * @param mixed order payment detail array
     * @return string extends html
     */
    public function gen_payment_detail(&$arr_payment_detail)
    {
        if (!$this->payment_detail_extends || !$arr_payment_detail)
            return;
        
        foreach ($this->payment_detail_extends as $key=>$str_extend)
        {
            $arr_payment_detail[$key] = $str_extend;
        }
        
        $render = $this->app->render();
        if(isset($arr_payment_detail['goods_use_score'])){
            if($arr_payment_detail['total_discount_consume_score']>($arr_payment_detail['total_point'] - $arr_payment_detail['goods_use_score'])){
                $arr_payment_detail['total_discount_consume_score'] = $arr_payment_detail['total_point'] - $arr_payment_detail['goods_use_score'];
                $arr_payment_detail['totalConsumeScore'] = $arr_payment_detail['goods_use_score'] + $arr_payment_detail['total_discount_consume_score'];
                
            }
        }
        $render->pagedata['order_detail'] = $arr_payment_detail;
        return $render->fetch('site/cart/checkout_total.html');
    }
}