<?php



/*
 * @package site
 * @author edwin.lzh@gmail.com
 * @license
 */
class site_controller extends base_controller
{
    /*
     * @var string $__theme
     * @access private
     */
    private $__theme = null;

    /*
     * @var string $__tmpl
     * @access private
     */
    private $__tmpl = null;

    /*
     * @var string $__tmpl_file
     * @access private
     */
    private $__tmpl_file = null;

    /*
     * @var string $__tmpl_main_app_id
     * @access private
     */
    private $__tmpl_main_app_id = null;

    /*
     * @var array $__widgets_css
     * @access private
     */
    private $__widgets_css = array();

    /*
     * @var array $__finish_modifier
     * @access private
     */
    private $__finish_modifier = array();

    /*
     * @var array $__cachecontrol
     * @access private
     */
    private $__cachecontrol = array(
                                'access' => 'public',
                                'no-cache' => '',
                                'no-store' => '',
                                'max-age' => 'max-age=1',
                              );

    /*
     * @var string $transaction_start
     * @access public
     */
    public $transaction_start = false;

    /*
     * @var string $contentType
     * @access public
     */
    public $contentType = 'text/html;charset=utf-8';

    /*
     * @var string $enable_strip_whitespace
     * @access public
     */
    public $enable_strip_whitespace = true;

    /*
     * @var string $is_splash
     * @access public
     */
    public $is_splash = false;

    /*
     * 构造
     * @var object $app
     * @access public
     * @return void
     */
    function __construct(&$app)
    {
        parent::__construct($app);
        if(@constant('WITHOUT_STRIP_HTML')){
            $this->enable_strip_whitespace = false;
        }
        $this->app = $app;
        $this->_request = kernel::single('base_component_request');
        $this->_response = kernel::single('base_component_response');

        foreach(kernel::servicelist('site_controller_construct') AS $service){
            $service->prepare($this);
        }//构造时加载

        //finish modify controler-action html
        foreach(kernel::servicelist(sprintf('site_controller_content.%s.%s.%s', $this->_request->get_app_name(), $this->_request->get_ctl_name(), $this->_request->get_act_name())) AS $class_name=>$service){
            if($service instanceof site_interface_controller_content){
                if(method_exists($service, 'modify')){
                    $this->__finish_modifier[$class_name] = $service;
                }
                if(method_exists($service,'boot')){
                    $service->boot($this);
                }
            }
        }
    }//End Function

    /*
     * 追加widgets css
     * @var array $css
     * @access public
     * @return void
     */
    public function append_widgets_css($css)
    {
        $this->__widgets_css = array_merge($this->__widgets_css, $css);
    }//End Function

    /*
     * 提取widgets css
     * @var string $body
     * @access public
     * @return void
     */
    public function extract_widgets_css(&$body)
    {
        preg_match_all('/<\s*style.*?>(.*?)<\s*\/\s*style\s*>/is', $body, $matchs);
        if(isset($matchs[0][0]) && !empty($matchs[0][0])){
            foreach($matchs[0] AS $matchcontent){
                $body = str_replace($matchcontent, '', $body);
            }
            $this->append_widgets_css($matchs[1]);
        }
    }//End Function

    /*
     * 生成widgets特有的前缀信息
     * @var array $values
     * @var array $varys
     * @access public
     * @return string
     */
    public function create_widgets_key_prefix($values, $varys)
    {
        $ret['__PREFIX__'] = 'WIDGETS_CACHE_KEY';
        if($varys[0] == '*'){
            $ret['*'] = $values;
        }else{
            foreach($varys AS $vary){
                $ret[$vary] = $values[$vary];
            }
        }
        ksort($ret);
        return serialize($ret);
    }//End Function

    /*
     * 构成链接
     * @var array $params
     * @access public
     * @return string
     */
    final public function gen_url($params=array())
    {
        return app::get('site')->router()->gen_url($params);
    }//End Function

    /*
     * 设置主题模版类型
     * @var string $tmpl
     * @access public
     * @return void
     */
    final public function set_tmpl($tmpl)
    {
        $this->__tmpl = $tmpl;
    }//End Function

    /*
     * 读取主题模版类型
     * @access public
     * @return string
     */
    final public function get_tmpl()
    {
        return $this->__tmpl;
    }//End Function

    /*
     * 设置主题模版文件
     * @var string $tmpl
     * @access public
     * @return void
     */
    final public function set_tmpl_file($tmpl_file)
    {
        $this->__tmpl_file = $tmpl_file;
    }//End Function

    /*
     * 读取主题模版文件
     * @access public
     * @return string
     */
    final public function get_tmpl_file()
    {
        return $this->__tmpl_file;
    }//End Function

    /*
     * 设置主题
     * @var string $theme
     * @access public
     * @return void
     */
    final public function set_theme($theme)
    {
        $this->__theme = $theme;
    }//End Function

    /*
     * 读取主题
     * @access public
     * @return string
     */
    final public function get_theme()
    {
        return $this->__theme;
    }//End Function

    /*
     * 主题模板嵌套
     * @var string $tmpl
     * @access public
     * @return string
     */
    protected function _fetch_tmpl_compile_require($tmpl_file,$is_preview=false)
    {
        $html = $this->fetch_tmpl($tmpl_file,$is_preview);
        if($tmpl_file == 'block/header.html'){
            $this->_change_style($html);
        }//todo: 如果是header文件，考虑换肤设置~~~~~~~~ 今后可能会有用户设置换肤问题
        return $html;
    }//End Function

    /*
     * 主题模板换肤
     * @var string $html
     * @access public
     * @return void
     */
    protected function _change_style(&$html)
    {
        $style = kernel::single('site_theme_base')->get_theme_style($this->get_theme());
        if(!empty($style['value'])){
            $style_css = kernel::single('site_theme_tmpl_file')->get_style_css($this->get_theme(), $style['value']);
            $html .= sprintf('<link href="%s" rel="stylesheet" type="text/css" media="screen, projection" />', $style_css);
        }
    }//End Function

    /*
     * 修补模板媒体文件
     * @var string $code
     * @access public
     * @return string
     */
    private function fix_theme_media($code)
    {
        $from = array(
            '/((?:background|src|href)\s*=\s*["|\'])(?:\.\/|\.\.\/)?(images\/.*?["|\'])/is',
            '/((?:background|background-image):\s*?url\()(?:\.\/|\.\.\/)?(images\/)/is',
            '/<!--[^<|>|{|\n]*?-->/'
        );

        $theme_url = defined('THEMES_IMG_URL') ? THEMES_IMG_URL : kernel::base_url(1) . '/themes';

        /*
        $to = array(
            '\1<?php echo kernel::base_url(), "/themes/",  \$this->get_theme(), "/";?>\2',
            '\1<?php echo kernel::base_url(), "/themes/",  \$this->get_theme(), "/";?>\2',
            ''
        );
        */
        $to = array(
            '\1<?php echo \$theme_url, "/", \$this->get_theme(), "/";?>\2',
            '\1<?php echo \$theme_url, "/", \$this->get_theme(), "/";?>\2',
            ''
        );
        return preg_replace($from, $to, $code);
    }//End Function

    /*
     * 设置模块main区域app_id
     * @var string $app_id
     * @access public
     * @return void
     */
    final public function set_tmpl_main_app_id($app_id)
    {
        $this->__tmpl_main_app_id = $app_id;
    }//End Function

    /*
     * 读取模块main区域app_id
     * @access public
     * @return string
     */
    final public function get_tmpl_main_app_id()
    {
        return $this->__tmpl_main_app_id;
    }//End Function

    /*
     * 显示模板
     * @var string $tmpl
     * @access public
     * @return void
     */
    final public function display_tmpl($tmpl, $fetch=false,$is_preview=false)
    {
        array_unshift($this->_files, $this->get_theme() . '/' . $tmpl);
        $this->_vars = $this->pagedata;
        $tmpl_file = realpath(THEME_DIR . '/' . $this->get_theme() . '/' . $tmpl);
        $this->tmpl_cachekey('__theme_app_id', ($this->get_tmpl_main_app_id()?$this->get_tmpl_main_app_id():$this->app->app_id));
        $this->tmpl_cachekey('__theme_main_page', $this->pagedata['_MAIN_']);
        $compile_id = $this->compile_id( $this->get_theme() . "/" . $tmpl );
        $last_modified = filemtime($tmpl_file);
        #if($this->force_compile || base_kvstore::instance('cache/theme')->fetch($compile_id, $compile_code, filemtime($tmpl_file))===false){

        if (!$is_preview){
            if($this->force_compile || !cachemgr::get($compile_id.$last_modified, $compile_code)){
                cachemgr::co_start();

                $tmpl_content = kernel::single('site_theme_tmpl_file')->get_tmpl_content($this->get_theme(), $tmpl);
                $compile_code = $this->_compiler()->compile($tmpl_content);

                if($compile_code!==false){
                    $compile_code = $this->fix_theme_media($compile_code);
                    #base_kvstore::instance('cache/theme')->store($compile_id, $compile_code);
                }
                cachemgr::set($compile_id.$last_modified, $compile_code, cachemgr::co_end());
            }
        }else{
            $this->_compiler()->is_preview = $is_preview;

            $tmpl_content = kernel::single('site_theme_tmpl_file')->get_tmpl_content($this->get_theme(), $tmpl);
            $compile_code = $this->_compiler()->compile($tmpl_content);

            if($compile_code!==false){
                $compile_code = $this->fix_theme_media($compile_code);
            }
        }

        kernel::single('site_theme_base')->get_theme_cache_version($this->get_theme());

        /** 添加theme_url的值 **/
        $theme_url = defined('THEMES_IMG_URL') ? THEMES_IMG_URL : kernel::base_url(1) . '/themes';
        ob_start();
        eval('?>'.$compile_code);
        $content = ob_get_contents();
        ob_end_clean();
        array_shift($this->_files);

        $this->pre_display($content);

        if($fetch === true){
            return $content;
        }else{
            echo $content;
        }
    }//End Function

    /*
     * 取出模板结果
     * @var string $tmpl
     * @access public
     * @return string
     */
    final public function fetch_tmpl($tmpl,$is_preview=false)
    {
        return $this->display_tmpl($tmpl, true, $is_preview);
    }//End Function

    /*
     * page调用 view
     * @var string $view
     * @var boolean $no_theme
     * @var string $app_id
     * @access public
     * @return string
     */
    final public function page($view, $no_theme=false, $app_id=null)
    {
        $params = $this->_request->get_params(true);
        if (!$params['response_type']){
        $current_theme = ($params['theme'])?$params['theme']:kernel::single('site_theme_base')->get_default();
        $is_preview = (isset($_COOKIE['site']['preview'])&&$_COOKIE['site']['preview']=='true')?true:false;
        $views = kernel::single('site_theme_base')->get_theme_views($current_theme);
        $views_app_id = is_null($app_id) ? $this->app->app_id : $app_id;
        if(is_array($views[$views_app_id]) && array_key_exists($view, $views[$views_app_id])){
            $view = 'theme:' . $views[$views_app_id][$view];
        }else{
            $service = kernel::service(sprintf('site_controller_display.%s.%s.%s', $this->_request->get_app_name(), $this->_request->get_ctl_name(), $this->_request->get_act_name()));
            if(is_object($service)){
                if(method_exists($service, 'get_view'))  $view = $service->get_view();
                if(method_exists($service, 'get_app_id'))   $app_id = $service->get_app_id();
                if(method_exists($service, 'get_no_theme'))   $no_theme = $service->get_no_theme();
            }
        }

        $global_prepare = kernel::servicelist('site_controller_prepare');
        $single_prepare = kernel::servicelist(sprintf('site_controller_prepare.%s.%s.%s', $this->_request->get_app_name(), $this->_request->get_ctl_name(), $this->_request->get_act_name()));
        foreach($global_prepare as $prepare){
            $prepare->modify($this);
        }
        foreach($single_prepare as $prepare){
            $prepare->modify($this);
        }
        if($no_theme==false && $current_theme){
            $this->set_theme($current_theme);
            $this->pagedata['_MAIN_'] = $view;      //强制替换
                $this->pagedata['_THEME_'] = kernel::base_url() . "/themes/" . $this->get_theme();   //模版地址
            $tmpl_file = $this->get_tmpl_file();    //指定模板
            $tmplObj = kernel::single('site_theme_tmpl');
            if(!$tmpl_file || !$tmplObj->tmpl_file_exists($tmpl_file, $current_theme)){
                $tmpl = ($this->get_tmpl()) ? $this->get_tmpl() : 'defalut';
                $tmpl = $tmplObj->get_default($tmpl, $current_theme);
                $tmpl_file = ($tmpl) ? $tmpl : (($tmpl_default = $tmplObj->get_default('default', $current_theme)) ? $tmpl_default : 'default.html');
            }//如果有模版，检测当前theme下是否有此模板
            $this->set_tmpl_main_app_id($app_id);
            $html = $this->fetch_tmpl($tmpl_file,$is_preview);
        }else{
            $html = $this->fetch($view, $app_id,$is_preview);
        }

        //finish html modify
        foreach($this->__finish_modifier AS $modifier){
            $modifier->modify($html,$this);
        }

        //modify html services
        $services = kernel::servicelist('site_controller_content');
        foreach($services AS $service){
            if($service instanceof site_interface_controller_content){
               $service->modify($html,$this);
            }
        }

        //append widgets css add by edwin.lzh@gmail.com 2010/6/11
        /*$html = str_replace('<%site_widgets_css%>', implode("", array_unique($this->__widgets_css)), $html);
        if(strpos($view, 'content:') !== 0){
            $this->extract_widgets_css($html);
        }*/
        $html = str_replace('<%site_widgets_css%>', app::get('site')->base_url(1).'widgetsproinstance-get_css-'.$current_theme.'-'.base64_encode($tmpl_file).'-'.$this->pagedata['store_id'].'.html', $html);

        //filter html special
        if($this->enable_strip_whitespace){
            $this->strip_whitespace($html);
        }
        }else{
            $html = json_encode($this->pagedata);
            echo $html;exit;
        }
        if(!$this->_response->get_header('Content-type', $header)){
            $this->_response->set_header('Content-type', $this->contentType, true);
        }//如果没有定义Content-type，默认加text/html;charset=utf-8

        if(!$this->_response->get_header('Cache-Control', $header)){
            $$cache_control = array();
            foreach($this->__cachecontrol AS $val){
                $val = trim($val);
                if(empty($val)) continue;
                $cache_control[] = $val;
            }
            $this->_response->set_header('Cache-Control', join(',', $cache_control), true);
        }//如果没有定义Content-Control，使用系统配置

        $this->_response->set_body($html);
    }

    /*
     * 去掉空白
     * @var string $html
     * @access public
     * @return void
     */
    final public function strip_whitespace(&$html)
    {
        $html = preg_replace("/(<[\s\n\r\t]{0,}script[^>]{0,}>[\s\n\r\t]{0,})<!--(.*?)[\/\/]{0,2}-->([\s\n\r\t]{0,}<[\s\n\r\t]{0,}\/[\s\n\r\t]{0,}script[\s\n\r\t]{0,}>)/is","\\1\\2\\3", $html);
        $html = preg_replace("/(<[\s\n\r\t]{0,}style[^>]{0,}>[\s\n\r\t]{0,})<!--(.*?)[\/\/]{0,2}-->([\s\n\r\t]{0,}<[\s\n\r\t]{0,}\/[\s\n\r\t]{0,}style[\s\n\r\t]{0,}>)/is","\\1\\2\\3", $html);
        //replace <!-- /*   */ -->
        $html = preg_replace("|<!-- /\*(.*)\*/ -->|isU", "", $html);
        //replace all \n\r to null
        $html = preg_replace("![\n\r]{2,}!is", "\n", $html);
        //replace space to null
        $html = preg_replace("!\n[\s\t]{1,}!is", "\n", $html);
        //replace space to null
        $html = preg_replace("![\x20\t]{1,}!is", " ", $html);
    }//End Function

    /*
     * 跳转
     * @var string $app
     * @var string $ctl
     * @var string $act
     * @var array $args
     * @var boolean $js_jump
     * @access public
     * @return void
     */
    final public function redirect($url, $js_jump=false)
    {
        if(is_array($url)){
            $url = $this->gen_url($url);
        }
        if($js_jump){
            echo "<header><meta http-equiv=\"refresh\" content=\"0; url={$url}\"></header>";
            exit;
        }else{
            $this->_response->set_redirect($url)->send_headers();
        }
        exit;
    }//End Function

    /*
     * 错误处理开始
     * @var string $url
     * @var string $errAction
     * @var string $shutHandle
     * @access public
     * @return void
     */
    function begin($url=null, $errAction=null, $shutHandle=null){
        $this->_action_url = $url;
        $this->_errAction = $errAction;
        $this->_shutHandle = $shutHandle ? $shutHandle : (E_USER_ERROR | E_ERROR);
        set_error_handler(array(&$this, '_errorHandler'), $this->_shutHandle);
        if($this->transaction_start) trigger_error('The transaction has been started', E_USER_ERROR);
        $this->transaction_start = true;
    }

    /*
     * 错误处理结束并显示
     * @var boolean $result
     * @var string $message
     * @var string $url
     * @var boolean $showNotice
     * @access public
     * @return void
     */
    function end($result=true, $message=null, $url=null, $show_notice=false,$ajax=false){
        if(!$this->transaction_start) trigger_error('The transaction has not started yet', E_USER_ERROR);
        $this->transaction_start = false;
        restore_error_handler();
        if(is_null($url)){
            $url = $this->_action_url;
        }
        $this->splash($result ? 'success' : 'failed', $url, $result ? $message : ($message ? $message:app::get('site')->_('操作失败')), $show_notice,'',$ajax);
    }

    /*
     * 错误处理结束
     * @access public
     * @return void
     */
    function end_only(){
        if(!$this->transaction_start) trigger_error('The transaction has not started yet', E_USER_ERROR);
        $this->transaction_start = false;
        restore_error_handler();
    }

    /*
     * 结果处理
     * @var string $result
     * @var string $jumpto
     * @var string $msg
     * @var boolean $show_notice
     * @var int $wait
     * @access public
     * @return void
     */
    function splash($status='success', $jumpto=null, $msg=null, $show_notice=false, $wait=0,$ajax=false){
        if($ajax){
            header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
            header('Progma: no-cache');
            if($status == 'failed'){
                $status = 'error';
            }
            $default = array(
                    $status=>$msg?$msg:app::get('site')->_('操作成功'),
                    'redirect'=>$jumpto,
                );
            $json = json_encode($default);
            if($_FILES){
                header('Content-Type: text/html; charset=utf-8');
            }else{
                 header('Content-Type:text/jcmd; charset=utf-8');
            }
            echo $json;
            exit;
        }
        if(!$msg)$msg =app::get('site')->_("操作成功");
        $this->_succ = true;

        $this->pagedata['msg'] = $msg;

        if(!is_null($jumpto)){
            $this->pagedata['jumpto'] = (is_array($jumpto)) ? $this->gen_url($jumpto) : $jumpto;
            if($wait > 0){
                $this->pagedata['wait'] = $wait;
            }elseif($status=='success'){
                $this->pagedata['wait'] = 1;
            }else{
                $this->pagedata['wait'] = 3;
            }
        }

        if($show_notice){
            $this->pagedata['error_info'] = &$this->_err;
        }

        $this->is_splash = true;

        $this->_response->set_header('Cache-Control', 'no-store, no-cache')->set_header('Content-type', $this->contentType)->send_headers();
        $this->pagedata['title'] = $status=='success'?app::get('site')->_('执行成功'):app::get('site')->_('执行失败');
        $this->set_tmpl('splash');
        $this->page('splash/'.$status.'.html', false, 'site');
        echo @join("\n", $this->_response->get_bodys());
        exit;
    }

    /*
     * 设置超时
     * @var int $time
     * @access public
     * @return void
     */
    public function set_max_age($time)
    {
        $this->__cachecontrol['max-age'] = 'max-age=' . (($time >= 0) ? intval($time) : 1);
    }//End Function

    /*
     * 设置no_cache
     * @var boolean $status
     * @access public
     * @return void
     */
    public function set_no_cache($status=true)
    {
        if($status){
            $this->__cachecontrol['no-cache'] = 'no-cache';
            $this->set_max_age(0);
        }else{
            $this->__cachecontrol['no-cache'] = '';
        }
    }//End Function

    /*
     * 设置no_store
     * @var boolean $status
     * @access public
     * @return void
     */
    public function set_no_store($status=true)
    {
        if($status){
            $this->__cachecontrol['no-store'] = 'no-store';
            $this->set_max_age(0);
        }else{
            $this->__cachecontrol['no-store'] = '';
        }
    }//End Function

    /*
     * 设置cache access
     * @var string $access
     * @access public
     * @return void
     */
    public function set_cache_access($access='public')
    {
        $this->__cachecontrol['access'] = ($access=='public') ? 'public' : ((empty($access)) ? '' : 'private');
    }//End Function

}//End Class
