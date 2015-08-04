<?php



/**
 * rpc service request class
 * 统一请求接口
 */
class b2c_api_rpc_request
{
    /**
     * app object
     */
    protected $app;

    /**
     * 构造方法
     * @param object app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }	
	
	/**
	 * 重试发送的请求
	 *  @param string method
     * @param array parameters
     * @param array callback array
     * @param string request title
     * @param int shop id
     * @param int time out type
     * @param string rpc id
	 * @return null
	 */
	public function rpc_recaller_request($method, $params, $callback=array(), $title, $shop_id=NULL, $time_out=1, $rpc_id=null)
	{
		parent::request($method,$params,$callback,$title,$shop_id,$time_out,$rpc_id);
	}

    /**
     * 整理和验证请求的数据
     * @param string method
     * @param array parameters
     * @param array callback array
     * @param string request title
     * @param int shop id
     * @param int time out type
     * @param string rpc id
     * @return null
     */
    protected function request($method, $params, $callback=array(), $title, $time_out=1, $rpc_id=null)
    {
        if($callback && $callback['class'] && $callback['method']){
            $rpc_callback = array('class' => $callback['class'], 'method' => $callback['method'], 'params' => $callback['params']);
        }else{
            $rpc_callback = array('class' => 'b2c_api_callback_app', 'method' => 'callback');
        }

        $this->write_log($title,$rpc_callback[0],'rpc_request',array($method,$params,$rpc_callback));
        $this->rpc_request($method,$params,$rpc_callback,$time_out,$rpc_id);
    }

    /**
     * rpc方式发送请求
     * @param string method name
     * @param array parameters array
     * @param array callback
     * @param int timeout type
     * @param string rpc id
     */
    protected function rpc_request($method,$params,$callback,$time_out=1,$rpc_id=null)
    {
        // 取到连接对方的信息
        $obj_shop = $this->app->model('shop');
        //$arr_shop = $obj_shop->dump(array('status' => 'bind', 'node_type' => 'ecos.ome'));
        $obj_shop_filter = array('status' => 'bind');
        $obj_shop_filter_services = kernel::servicelist('b2c_rpc_request_shop_filter');
        if( $obj_shop_filter_services )
        {        	
        	foreach( $obj_shop_filter_services as $service_item )
        	{        		
        		if( method_exists($service_item, 'filter') ) {
        			$service_item->filter($method, $obj_shop_filter);
        		}
        	}
        }
        
        $arr_shops = $obj_shop->getList('*',$obj_shop_filter);
        $orginal_params = $params;

        // 取到rpc表中的数据
        if (!is_null($rpc_id))
        {
            $obj_rpc_poll = app::get('base')->model('rpcpoll');
            $arr_pk = explode('-', $rpc_id);
            $real_rpc_id = $arr_pk[0];
            $rpc_calltime = $arr_pk[1];
            $tmp = $obj_rpc_poll->getList('*', array('id'=>$real_rpc_id,'calltime'=>$rpc_calltime));

            if ($tmp)
            {
                $arr_params = $tmp[0]['params'];
                $shop_node_type = $arr_params['node_type'];
                $shop_node_id = $arr_params['to_node_id'];
            }
        }
        if ($arr_shops)
        {
            foreach($arr_shops as $arr_shop)
            {
                if (!isset($shop_node_type) || !isset($shop_node_id))
                {
                    if ($arr_shop['node_type'] && $arr_shop['node_id'])
                    {
                        $params = array_merge(array('node_type' => $arr_shop['node_type'], 'to_node_id' => $arr_shop['node_id']), $orginal_params);

                        $callback_class = $callback['class'];
                        $callback_method = $callback['method'];
                        $callback_params = (isset($callback['params'])&&$callback['params'])?$callback['params']:array();
                        $rst = $this->app->matrix()->set_callback($callback_class,$callback_method,$callback_params)
                            ->set_timeout($time_out)
                            ->call($method,$params,$rpc_id);
                    }
                }
                else
                {
                    if ($shop_node_type == $arr_shop['node_type'] && $shop_node_id == $arr_shop['node_id'])
                    {
                        $params = array_merge(array('node_type' => $arr_shop['node_type'], 'to_node_id' => $arr_shop['node_id']), $orginal_params);

                        $callback_class = $callback['class'];
                        $callback_method = $callback['method'];
                        $callback_params = (isset($callback['params'])&&$callback['params'])?$callback['params']:array();
                        $rst = $this->app->matrix()->set_callback($callback_class,$callback_method,$callback_params)
                            ->set_timeout($time_out)
                            ->call($method,$params,$rpc_id);
                    }
                }
            }
        }
    }

    private function write_log($title='', $class_name='b2c_api_callback_app', $request_method_name='rpc_request', $data=array())
    {
        if ($data)
        {
            /*$log_file = DATA_DIR.'/logs/b2c/request/{date}/api_request.php';
            $logfile = str_replace('{date}', date("Ymd"), $log_file);

            if(!file_exists($logfile))
            {
                if(!is_dir(dirname($logfile)))  utils::mkdir_p(dirname($logfile));

                $fs = fopen($logfile, 'w');
                $str_xml = "<?php exit(0);";
                $str_xml .= "'<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $str_xml .= "<request>";
                $str_xml .= "</request>';";
                //error_log(print_r($str_xml,true),3,$logfile);
                fwrite($fs, $str_xml);
                fclose($fs);
            }*/

            $arr_data = array(
                'method' => $data[0],
                'params' => $data[1],
                'rpc_callback' => $data[2],
            );

            // 记录api日志
            /*if (filesize($logfile))
            {
                $fs = fopen($logfile, 'a+');
                $str_xml = fread($fs, filesize($logfile));
                $str_xml = substr($str_xml, 0, strlen($str_xml) - 12);
                fclose($fs);
            }
            $fs = fopen($logfile, 'w');*/
            $str_xml .= "<query>";
            $str_xml .= "<method>" . $arr_data['method'] . "</method>";
            $str_xml .= "<params>" . print_r($arr_data['params'], true) . "</params>";
            $str_xml .= "<rpc_callback>";
            foreach ($arr_data['rpc_callback'] as $key=>$value)
            {
                $str_xml .= "<$key>" . $value . "</$key>";
            }
            $str_xml .= "</rpc_callback></query>";
            //$str_xml .= "</request>';";

            /*fwrite($fs, $str_xml);
            fclose($fs);*/
            kernel::log($str_xml);
        }
    }
}