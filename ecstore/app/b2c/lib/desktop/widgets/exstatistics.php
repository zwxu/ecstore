<?php

 
class b2c_desktop_widgets_exstatistics implements desktop_interface_widget{
    
    var $order = 1;
    function __construct($app){
        $this->app = $app; 
        $this->render =  new base_render(app::get('b2c'));  
    }
    
    function get_title(){
            
        return app::get('b2c')->_("运营分析");
        
    }
    function get_html(){

        $render = $this->render;

        //近一周成交订金额
        
        $mdl_orders = $this->app->model('orders');
        $db = kernel::database();
        $lastweek_filter = array(
                    '_createtime_search'=>'between',
                    'createtime_from'=>date('Y-m-d',strtotime('-1 week')),
                    'createtime_to'=>date('Y-m-d'),
                    'createtime' => date('Y-m-d'),
                    '_DTIME_'=>
                        array(
                            'H'=>array('createtime_from'=>date('H'),'createtime_to'=>date('H')),
                            'M'=>array('createtime_from'=>date('i'),'createtime_to'=>date('i'))
                        ),
                    'pay_status'=>'1',
                );
        $from = time();
        $to = strtotime('-1 week');
        $rows = $db->select('select sum(total_amount) as order_amount,count(1) as order_nums, DATE_FORMAT(FROM_UNIXTIME(createtime),"%Y-%m-%d") as mydate from sdb_b2c_orders  where createtime>='.intval($to).' and createtime<'.intval($from).' and pay_status=\'1\' group by DATE_FORMAT(FROM_UNIXTIME(createtime),"%Y-%m-%d")');
        foreach($rows as $row){
            $data[$row['mydate']] = array('order_amount'=>$row['order_amount'],'order_nums'=>$row['order_nums']);
        }
        //print_r($result);
        $render->pagedata['data'] = $data;
        $render->pagedata['this_week_from'] = date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400);
        $render->pagedata['this_week_to'] = date("Y-m-d", strtotime($render->pagedata['this_week_from'])+86400*7-1);
        
        return $render->fetch('desktop/widgets/exstatistics.html');
    }
    function get_className(){
        
          return " valigntop exstatistics";
    }
    function get_width(){
          
          return "l-1";
        
    }
    
}

?>