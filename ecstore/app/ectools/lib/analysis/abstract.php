<?php


/**
 * 这个类实现报表的数据统计和显示的抽象类
 * @abstract implements ectools_analysis_interface
 * 
 * @version 0.1
 * @package ectools.lib.analysis
 */
abstract class ectools_analysis_abstract 
{
	/**
	 * @var protected service object
	 */
    protected $_serivce = null;
    /**
	 * @var protected params array
	 */
    protected $_params = null;
    /**
	 * @var protected render object
	 */
    protected $_render = null;
    /**
	 * @var protected extra view
	 */
    protected $_extra_view = null;
    /**
	 * @var protected title string
	 */
    protected $_title = null;
	/**
	 * @var public layout type
	 */
    public $layout = 1;
    /**
	 * @var public report type string
	 */
    public $report_type = 'false';
    /**
	 * @var public log options array
	 */
    public $logs_options = array();
    /**
	 * @var public type options array
	 */
    public $type_options = array();
    /**
	 * @var public detail options array
	 */
    public $detail_options = array(
        'hidden' => false,
        'force_ext' => false,
    );
    /**
	 * @var public graph options array
	 */
    public $graph_options = array(
        'hidden' => false,
        'iframe_height' => 180,
    );
    /**
	 * @var public rank options array
	 */
    public $rank_options = array(
    
    );
    /**
	 * @var public finder options array
	 */
    public $finder_options = array(
        'hidden' => false,    
    );
	
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    function __construct(&$app) 
    {   
        $this->app = $app;
        if(substr(PHP_SAPI_NAME(),0,3) !== 'cli') {
            $this->_render = kernel::single('desktop_controller');
            if(isset($this->analysis_config)){
                 $this->_render->pagedata['time_shortcut'] = $this->analysis_config['setting']['time_shortcut'];
             }
        }
        $this->_params = array();
        $this->_service = get_class($this);
        $this->_extra_view = array('ectools' => 'analysis/extra_view.html');
        $this->analysis_config = app::get('ectools')->getConf('analysis_config');
    }//End Function
	
    /**
     * 得到报表日志-各种报表各自实现
     * @param string time
     * @return array 日志信息
     */
    public function get_logs($time) 
    {
        //todo:各自实现
    }//End Function
	
    /**
     * 设置报表统计的参数
     * @param array 需要设置的参数
     * @return object 本类对象
     */
    public function set_params($params) 
    {
        $this->_params = $params;

        if(isset($this->analysis_config)){
            $time_from = date("Y-m-d", time()-(date('w')?date('w')-$this->analysis_config['setting']['week']:7-$this->analysis_config['setting']['week'])*86400);
        }else{
            $time_from = date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400);
        }
        $time_to = date("Y-m-d", strtotime($time_from)+86400*7-1);

        $this->_params['time_from'] = ($this->_params['time_from']) ? $this->_params['time_from'] : $time_from;
        $this->_params['time_to'] = ($this->_params['time_to']) ? $this->_params['time_to'] : $time_to;
        $this->_params['order_status'] = $this->analysis_config['filter']['order_status'];
        $this->_params['status'] = 'succ';
        return $this;
    }//End Function
	
    /**
     * 设置extra视图
     * @param array view视图数组
     * @return object 本类对象
     */
    public function set_extra_view($array) 
    {
        $this->_extra_view = $array;
        return $this;
    }//End Function
	
    /**
     * 设置service
     * @param object service
     * @return object 本类对象
     */
    public function set_service($service){
        $this->_service = $service;
        return $this;
    }
	
    /**
     * 设置图像方法，设置页面参数
     * @param null
     * @return boolean 成功与否
     */
    public function graph() 
    {
        if($this->graph_options['hidden'] == true){
            $this->_render->pagedata['graph_hidden'] = 1;
            return false;
        }
        foreach($this->logs_options AS $key=>$val){
            $this->_render->pagedata['graph'][$key]['name'] = $this->app->_($val['name']);
        }
        $this->_render->pagedata['target'] = ($this->_render->pagedata['target']) ? $this->_render->pagedata['target'] : 1;
        $this->_render->pagedata['ext_url'] .= '&type='.$this->_params['type'];
        $this->_render->pagedata['ext_url'] .= '&time_from='.$this->_params['time_from'];
        $this->_render->pagedata['ext_url'] .= '&time_to='.$this->_params['time_to'];
        $this->_render->pagedata['ext_url'] .= '&service='.$this->_service;
        $this->_render->pagedata['ext_url'] .= '&callback='.$this->graph_options['callback'];
        $this->_render->pagedata['ext_url'] .= '&report='.$this->_params['report'];
        $this->_render->pagedata['iframe_height'] = $this->graph_options['iframe_height'];
        return true;
    }//End Function
	
    /**
     * 生成页面详细区域信息
     * @param null
     * @return boolean 成功与否
     */
    public function detail() 
    {
        if($this->detail_options['hidden'] == true){
            $this->_render->pagedata['detail_hidden'] = 1;
            return false;
        }
        $detail = array();
        if($this->detail_options['force_ext'] == false){
            $analysis_id = app::get('ectools')->model('analysis')->select()->columns('id')->where('service = ?', $this->_service)->instance()->fetch_one();
            $obj = app::get('ectools')->model('analysis_logs')->select()->columns('target, sum(value) AS value')->where('analysis_id = ?', $analysis_id);
            if(isset($this->_params['type']))   $obj->where('type = ?', $this->_params['type']);
            if(isset($this->_params['target']))   $obj->where('target = ?', $this->_params['target']);
            if(isset($this->_params['time_from']))   $obj->where('time >= ?', strtotime(sprintf('%s 00:00:00', $this->_params['time_from'])));
            if(isset($this->_params['time_to']))   $obj->where('time <= ?', strtotime(sprintf('%s 23:59:59', $this->_params['time_to'])));
            $rows = $obj->where('flag = ?', 0)->group(array('target'))->instance()->fetch_all();
            foreach($rows AS $row){
                $tmp[$row['target']] = $row['value'];
            }
            foreach($this->logs_options AS $target=>$option){
                $detail[$option['name']]['value'] = ($tmp[$target]) ? $tmp[$target] : 0;
                $detail[$option['name']]['memo'] = $this->logs_options[$target]['memo'];
                $detail[$option['name']]['icon'] = $this->logs_options[$target]['icon'];
            }
        }
        if(method_exists($this, 'ext_detail')){
            $this->ext_detail($detail);
        }
        foreach($detail AS $key=>$val){
            $name = $this->app->_($key);
            $data[$name]['value'] = $val['value'];
            $data[$name]['memo'] = $this->app->_($val['memo']);
            $data[$name]['icon'] = $val['icon'];
        }
        $this->_render->pagedata['detail'] = $data;
        return true;
    }//End Function
	
    /**
     * 统计的类型-内容
     * @param null
     * @return string 类型值
     */
    public function get_type() 
    {
        //todo:各自实现
    }//End Function
	
    /**
     * 统计频率
     * @param null
     * @return string 频率值
     */
    public function rank() 
    {
        //todo:各自实现
    }//End Function
	
    /**
     * 生成各自统计内容的finder
     * @param null
     * @return array - finder统一格式的数组
     */
    public function finder() 
    {
        //todo:各自实现
    }//End Function
	
    /**
     * 生成头部信息，统计图表的头部
     * @param null
     * @return null
     */
    public function headers() 
    {
        $this->_render->pagedata['title'] = $this->_title;
        $this->_render->pagedata['time_from'] = $this->_params['time_from'];
        $this->_render->pagedata['time_to'] = $this->_params['time_to'];
        $this->_render->pagedata['today'] = date("Y-m-d");
        $this->_render->pagedata['yesterday'] = date("Y-m-d", time()-86400);
        if(isset($this->analysis_config)){
            $this->_render->pagedata['this_week_from'] = date("Y-m-d", time()-(date('w')?date('w')-$this->analysis_config['setting']['week']:7-$this->analysis_config['setting']['week'])*86400);
        }else{
            $this->_render->pagedata['this_week_from'] = date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400);
        }
        $this->_render->pagedata['this_week_to'] = date("Y-m-d", strtotime($this->_render->pagedata['this_week_from'])+86400*7-1);
        $this->_render->pagedata['last_week_from'] = date("Y-m-d", strtotime($this->_render->pagedata['this_week_from'])-7*86400);
        $this->_render->pagedata['last_week_to'] = date("Y-m-d", strtotime($this->_render->pagedata['last_week_from'])+86400*7-1);
        $this_month_t = date('t');
        $this->_render->pagedata['this_month_from'] = date("Y-m-" . 01);
        $this->_render->pagedata['this_month_to'] = date("Y-m-" . $this_month_t);
        $last_month_t = date('t', strtotime("last month"));
        $this->_render->pagedata['last_month_from'] = date("Y-m-" . 01, strtotime("last month"));
        $this->_render->pagedata['last_month_to'] = date("Y-m-" . $last_month_t, strtotime("last month"));
        $this->_render->pagedata['layout'] = $this->layout;

        if($this->report_type == 'true'){
            $this->_render->pagedata['report'] = $this->_params['report'];
            $this->_render->pagedata['report_type'] = $this->report_type;
            $this->_render->pagedata['month'] = array(1,2,3,4,5,6,7,8,9,10,11,12);
            for($i = 2000;$i<=date("Y",time());$i++){
                $year[] = $i;
            }
            $this->_render->pagedata['year'] = $year;
            $this->_render->pagedata['from_selected'] = explode('-',$this->_params['time_from']);
            $this->_render->pagedata['to_selected'] = explode('-',$this->_params['time_to']);
        }

        if($this->type_options['display'] == 'true'){
            $this->_render->pagedata['type_display'] = 'true';
            $this->_render->pagedata['typeData'] = $this->get_type();
            $this->_render->pagedata['type_selected'] = $this->_params['type_id'];
        }
    }//End Function
	
   /**
    * 展示页面内容的方法
    * @param boolean true - 提出内容，相当于fetch，false echo内容
    * @return string html结果内容
    */
    public function display($fetch=false) 
    {
        $this->headers();
        $this->detail();
        $this->graph();
        $this->rank();
        if($this->finder_options['hidden']){
            foreach($this->_extra_view AS $app_id=>$view){
                $content = $this->_render->fetch($view, $app_id);
                break;
            }
        }else{
            $finder = $this->finder();
            $finder['params']['base_filter'] = $this->_params;
            $finder['params']['top_extra_view'] = $this->_extra_view;
            ob_start();
            $this->_render->finder($finder['model'], $finder['params']);
            $content = ob_get_contents();
            ob_end_clean();
        }

        if($fetch){
            return $content;
        }else{
            echo $content;
        }
    }//End Function
	
    /**
     * fetch 页面的html
     * @param null
     * @return string html页面nei'ron
     */
    public function fetch() 
    {
        return $this->display(true);
    }//End Function

}//End Function
