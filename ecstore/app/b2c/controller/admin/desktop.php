<?php
 
 
class b2c_ctl_admin_desktop extends desktop_controller{

    var $workground = 'b2c_ctl_admin_desktop';
    
    /**
     * 构造方法
     * @params object app object
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }
    public function viewstats(){
        $view = $_GET['view']?$_GET['view']:1;
        if($view==1 || $view==2){
            $currency = app::get('ectools')->model('currency');
            $cur_row = $currency->getDefault();
            $currency = $cur_row['cur_sign']?$cur_row['cur_sign']:'¥';
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
            $rows = $db->select('select sum(total_amount) as order_amount,count(1) as order_nums, DATE_FORMAT(FROM_UNIXTIME(createtime),"%Y-%m-%d") as mydate from sdb_b2c_orders  where createtime>='.$to.' and createtime<'.$from.' and pay_status=\'1\' group by DATE_FORMAT(FROM_UNIXTIME(createtime),"%Y-%m-%d")');
            foreach($rows as $row){
                $data[$row['mydate']] = array('order_amount'=>$row['order_amount'],'order_nums'=>$row['order_nums']);
            }
            if($view==1){
                foreach((array)$data as $key=>$value){
                    $values[] = array('value'=>$value['order_amount'],'tip'=>'#val#'.$currency);
                    $all_values[] = $value['order_amount'];
                    $labels[] = $key;

                }
                $all_values = (array)$all_values;
                rsort($all_values);
                $max = $all_values[0];
                $steps = (ceil($max/5));
                $title = app::get('b2c')->_('本周订单金额(').$currency.')';
                $values = json_encode($values);
            }else{
                foreach((array)$data as $key=>$value){
                    $values[] = array('value'=>$value['order_nums'],'tip'=>'#val#'.app::get('b2c')->_('笔'));
                    $all_values[] = $value['order_nums'];
                    $labels[] = $key;
                }
                $all_values = (array)$all_values;
                rsort($all_values);
                $max = $all_values[0];
                $steps = (ceil($max/5));
                $title = app::get('b2c')->_('本周订单数量(笔)');
                $values = json_encode($values);                
            }
            $labels = json_encode($labels);
            //print_r($result);
            
        }
        $this->pagedata['values'] = $values;
        $this->pagedata['max'] = $max;
        $this->pagedata['steps'] = $steps;
        $this->pagedata['labels'] = $labels;
        $this->pagedata['title'] = $title;
        echo $this->fetch('desktop/widgets/json.html');
    }
    public function index(){
        $this->finder('b2c_mdl_orders',array(
            'title'=>app::get('b2c')->_('订单列表'),
            'allow_detail_popup'=>true,
            'actions'=>array(
                            array('label'=>app::get('b2c')->_('添加订单'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_order&act=addnew','target'=>'_blank'),
                        ),'use_buildin_set_tag'=>true,'use_buildin_recycle'=>false,'use_buildin_filter'=>true,'use_view_tab'=>true,
            ));
    }

}
