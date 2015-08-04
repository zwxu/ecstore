<?php

/**
 * 前台店铺模版控制器
 * @author 曹辰吟
 */
class business_ctl_site_theme extends business_ctl_site_member{
    
    /**
     * __construct 构造函数
     * @author 曹辰吟
     * @return null
     */
    public function __construct(&$app)
    {
        parent::__construct($app);
        //指定veiw,作用于display/output方法
        $this->cur_view = 'theme';
        //设置不读缓存
        $GLOBALS['runtime']['nocache']=microtime();
    
    }

    /**
     * _get_theme_tmpl 获取店铺当前模版文件
     * @author 曹辰吟
     * @return array
     */
    private function _get_theme_tmpl(){
        if($this->store['theme_id']){
            $store_theme = app::get('business')->model('theme')->getRow('*',array('theme_id' => $this->store['theme_id']));
            return app::get('site')->model('themes_tmpl')->getRow('*',array('id' => $store_theme['shop_tmpl_id']));
        }
        return null;
    }

    /**
     * _get_theme_tmpls 获取店铺当前所有模版文件
     * @author 曹辰吟
     * @return array
     */
    private function _get_theme_tmpls(){
        if($this->store['theme_id']){
            $store_theme = app::get('business')->model('theme')->getRow('*',array('theme_id' => $this->store['theme_id']));
            $shop_index = app::get('site')->model('themes_tmpl')->getRow('*',array('id' => $store_theme['shop_tmpl_id']));
            $shop_gallery = app::get('site')->model('themes_tmpl')->getRow('*',array('id' => $store_theme['gallery_tmpl_id']));
            return array(
                $shop_index['tmpl_type'] => array($shop_index),
                $shop_gallery['tmpl_type']=>array($shop_gallery)
            );
        }
        return null;
    }

    /**
     * _get_theme_tmpls 获取店铺当前所有模版文件
     * @author 曹辰吟
     * @return array
     */
    private function _get_theme_files(){
        if($this->store['theme_id']){
            $store_theme = app::get('business')->model('theme')->getRow('*',array('theme_id' => $this->store['theme_id']));
            $shop_index = app::get('site')->model('themes_tmpl')->getRow('*',array('id' => $store_theme['shop_tmpl_id']));
            $shop_gallery = app::get('site')->model('themes_tmpl')->getRow('*',array('id' => $store_theme['gallery_tmpl_id']));
            return array(
                $shop_index['tmpl_path'] => $shop_index,
                $shop_gallery['tmpl_path']=> $shop_gallery
            );
        }
        return null;
    }


    /**
     * theme 店铺模版管理页
     * @author 曹辰吟
     */
    public function theme()
    {
        //店铺管理页面路径
        $this->path[] = array('title'=>app::get('business')->_('店铺管理'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('模版设置'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path; 
        $cur_theme;
        //当前店铺使用的模版
        if($this->store['theme_id']){
            $this->pagedata['cur_theme'] =  $cur_theme = app::get('business')->model('theme')->getRow('*', array('theme_id'=>$this->store['theme_id']));
        }
        //可使用模版数
        $theme_num = $this->store['store_gradeinfo']['theme_num'];
        intval($theme_num) == 0 ? $theme_num = -1 : $theme_num = intval($theme_num);
        //当前系统模版下的所有店铺模版文件
        if($cur_theme && $theme_num ==1){
            $themes = array();
        }else{
        $themes = app::get('business')->model('theme')->getList('*', array('theme_id|noequal'=>intval($this->store['theme_id'])),0,$theme_num);
        }
        $this->pagedata['themes'] = $themes;
        // $this->pagedata['res_url'] = $this->app->res_url;
        $this->output();
    }

    /**
     * usetheme 店铺启用模版文件
     * @author 曹辰吟
     */
    public function usetheme($theme_id){
        //事务开始
        $this->begin($this->gen_url( array('app'=>'business','ctl'=>'site_theme','act'=>'theme')));
        //修改店铺模版ID
        $storemanger_model = app::get('business')->model('storemanger');
        if($storemanger_model->update(array('theme_id'=>$theme_id), array('store_id'=>$this->store['store_id']))){
            kernel::single('business_theme_widget')->copy_site_theme_to_store($this->store['store_id'], $theme_id);
            $this->end(true,app::get('business')->_('启用成功'));
            exit;
        }
        $this->end(false,app::get('business')->_('启用失败'));
    }
    
    public function closetheme() {
        $storemanger_model = app::get('business')->model('storemanger');
        $widget_model = app::get('business')->model('widgets_instance');
        if($storemanger_model->update(array('theme_id'=>null), array('store_id'=>$this->store['store_id']))){
            $widget_model->delete(array('store_id'=>$this->store['store_id']));
        }
        $this->redirect(array('app'=>'business','ctl'=>'site_theme','act'=>'theme'));
    }

    /**
     * theme 店铺模版可视化编辑
     * @author 曹辰吟
     * @return null
     */
    public function widgettheme($file){
        //获取当前系统模板
        $theme = kernel::single('site_theme_base')->get_default();
        $themes_tmpl = $this->_get_theme_tmpl();
        if($themes_tmpl){}else{
            $this->splash('failed',$this->gen_url( array('app'=>'business','ctl'=>'site_theme','act'=>'theme')),app::get('business')->_('请先启用模版'));
            exit;
        }

        $file = trim($file);
        $theme_files = $this->_get_theme_files();
        $file =  isset($theme_files[$file])? $file : $themes_tmpl['tmpl_path'];
        $this->pagedata['file'] = $file;

        //
        header('Content-Type: text/html; charset=utf-8');
        $this->path[] = array('text'=>app::get('site')->_('模板可视化编辑'));

        $this->pagedata['theme'] = $theme;


        //$this->pagedata['view'] = $themes_tmpl['tmpl_name'];
       
        $this->pagedata['shopadmin'] = kernel::router()->app->base_url(1);
        $this->pagedata['site_url'] = app::get('site')->base_url(1);
        $this->pagedata['pagehead_active'] = 'preview';
        $this->pagedata['save_url'] = kernel::router()->app->base_url(1).'/theme-dopreview.html';
        $this->pagedata['preview_url'] = app::get('site')->base_url(1);

        $this->pagedata['list'] = $this->_get_theme_tmpls();
    
        $this->pagedata['types'] = kernel::single('site_theme_tmpl')->get_name();
        
        // 站外链接限制
        $website=app::get('business')->getConf('website.url');
        if($website){
            $website=array_map('preg_quote',$website);
            $this->pagedata['website']=implode('|',$website);
        }
        $img_website=app::get('business')->getConf('website.img_url');
        if($img_website){
            $img_website=array_map('preg_quote',$img_website);
            $this->pagedata['img_website']=implode('|',$img_website);
        }
        return $this->singlepage('site/theme/widget/editor.html','business');
    }

    /**
     * previewtheme 预览页面
     * @author 曹辰吟
     * @return null
     */
    public function previewtheme($theme,$file)
    {
        
        /** 清空widgets数据缓存 **/
        if ($_SESSION['WIDGET_TMP_DATA'][$theme.'/'.$file]) $_SESSION['WIDGET_TMP_DATA'][$theme.'/'.$file] = array();
        if ($_SESSION['WIDGET_TMP_DATA'][$theme.'/block/header.html']) $_SESSION['WIDGET_TMP_DATA'][$theme.'/block/header.html'] = array();
        if ($_SESSION['WIDGET_TMP_DATA'][$theme.'/block/footer.html']) $_SESSION['WIDGET_TMP_DATA'][$theme.'/block/footer.html'] = array();

        header('Content-Type: text/html; charset=utf-8');
        kernel::single('base_session')->close();
        $smarty = kernel::single('site_controller');
        $smarty->tmpl_cachekey('widgets_modifty_'.$theme , true);

        $smarty->pagedata['theme_dir'] = kernel::base_url() . '/themes/' . $theme . '/';
        $smarty->pagedata['theme'] = $theme;
        $smarty->pagedata['store_id'] = $this->store['store_id'];
        $smarty->pagedata['store'] = $this->store;
        //注册标签解析方法
        $smarty->_compiler()->set_compile_helper('compile_main', kernel::single('site_theme_complier'));
        $smarty->_compiler()->set_view_helper('function_header', 'business_theme_helper');
        $smarty->_compiler()->set_view_helper('function_footer', 'site_theme_helper');
        $smarty->_compiler()->set_compile_helper('compile_widgets', kernel::single('business_theme_complier'));
        $smarty->set_theme($theme);
        $smarty->display_tmpl(urldecode($file));
    }

    /**
     * addwidgetspage 载入挂件列表页面
     * @author 曹辰吟
     * @param [string] 模版
     */
    public function addwidgetspage($theme)
    {

        $this->pagedata['theme'] = $theme;
        //获取常用标签
        $this->pagedata['widgetsLib'] = kernel::single('business_theme_widget')->get_libs($theme);
    
        $theme_url = kernel::base_url() . strrchr(THEME_DIR,'/') . '/' . $theme . '/';
        $app_base_url = kernel::base_url().'/app/';
        $themesFileObj=app::get('site')->model('themes_file');
        
        $storager = kernel::single('base_storager');

        if ($this->pagedata['widgetsLib']['usual']){
            foreach((array)$this->pagedata['widgetsLib']['usual'] as $key=>$widgets){
                if ($widgets['theme']){

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/icon.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        ecae_kvstore_write('test',$src);
                        $this->pagedata['widgetsLib']['usual'][$key]['img'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['usual'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    }

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/widget.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        $this->pagedata['widgetsLib']['usual'][$key]['bimg'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['usual'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                    }
                }else{//获取系统级挂件信息
                    $this->pagedata['widgetsLib']['usual'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    $this->pagedata['widgetsLib']['usual'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                }
            }
        }
        $this->display('site/theme/widget/add_widgets_page.html','business');
    }

    /**
     * addwidgetspage 载入挂件列表
     * @author 曹辰吟
     * @param [string] 模版
     */
    public function addwidgetspageextend($theme)
    {
        //挂件类型
        $catalog = $this->_request->get_post('catalog');
        $this->pagedata['theme'] = $theme;
        //根据挂件类型获取挂件
        $this->pagedata['widgetsLib'] = kernel::single('business_theme_widget')->get_libs_extend($theme, $catalog);
        $theme_url = kernel::base_url() . strrchr(THEME_DIR,'/') . '/' . $theme . '/';

        $themesFileObj=app::get('site')->model('themes_file');
       
        $storager = kernel::single('base_storager');

        if ($this->pagedata['widgetsLib']['list'])
            foreach((array)$this->pagedata['widgetsLib']['list'] as $key=>$widgets){


                if ($widgets['theme']){

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/icon.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        ecae_kvstore_write('test',$src);
                        $this->pagedata['widgetsLib']['list'][$key]['img'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['list'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    }

                    $rs=$themesFileObj->getList('content',array('fileuri'=>$widgets['theme'].':'.'widgets/'.$widgets['name'].'/images/widget.jpg'));

                    if ($rs[0]['content']) {
                        $ident = $storager->parse($rs[0]['content']);
                        $src = $ident['url'];
                        $this->pagedata['widgetsLib']['list'][$key]['bimg'] = $src;
                    }else{
                        $this->pagedata['widgetsLib']['list'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                    }
                }else{//获取系统级挂件信息
                    $this->pagedata['widgetsLib']['list'][$key]['img'] = $this->app->res_url.'/images/widgets/icon.jpg';
                    $this->pagedata['widgetsLib']['list'][$key]['bimg'] = $this->app->res_url.'/images/widgets/widget.jpg';
                }
            }

        $this->display('site/theme/widget/add_widgets_page_extend.html','business');
    }
    /**
     * doaddwidgets 准备添加挂件(载入挂件配置页)
     * @author 曹辰吟
     * @param  [string] $widgets       
     * @param  [string] $widgets_app   
     * @param  [string] $widgets_theme 
     * @param  [string] $theme        
     */
    public function doaddwidgets($widgets,$widgets_app,$widgets_theme,$theme){
        $this->pagedata['widget_editor'] = kernel::single('business_theme_widget')->store_editor($widgets, $widgets_app, $widgets_theme, $theme,false, $this->store);

        $this->pagedata['widgets_type'] = $widgets;
        $this->pagedata['widgets_app'] = $widgets_app;
        $this->pagedata['widgets_theme'] = $widgets_theme;
        $this->pagedata['theme'] = $theme;

        $this->pagedata['i']=is_array($_SESSION['_tmp_wg_insert'])?count($_SESSION['_tmp_wg_insert']):0;
        $this->pagedata['basic_config'] = kernel::single('site_theme_base')->get_basic_config($theme);

        $this->display('site/theme/widget/do_add_widgets.html','business');
    }
    
    //保存前验证 huoxh add 2013-07-25
    function validator_widgets($widgets,$widgets_app,$theme,$action='insert',&$data){
        $widgets_config=kernel::single('site_theme_widget')->widgets_config($widgets,$widgets_app,$theme);
        
        $widgets_config['vrun'] = 'theme_widget_validator_' . $widgets;
        $widgets_config['vfg'] = $widgets_config['dir'] . '/theme_widget_validator_' . $widgets . '.php';
        $vfunc_file = $widgets_config['vfg'];
        $vfunc = $widgets_config['vrun'];
        if(file_exists($vfunc_file)){
            include_once($vfunc_file);
        }
        if(function_exists($vfunc)){
            return  $vfunc($data,$action);
        }        
        return true;
    }
    /**
     * insertwidget 添加挂件
     * @author 曹辰吟
     * @param  [type] $widgets      
     * @param  [type] $domid         
     * @param  [type] $widgets_app  
     * @param  [type] $widgets_theme 
     * @param  [type] $theme        
     * @return [type]  
     */
    public function insertwidget($widgets,$domid,$widgets_app,$widgets_theme,$theme){
        header('Content-Type: text/html;charset=utf-8');
        
        $wg = $this->_request->get_post('__wg');

        $set = array(
            'widgets_type' => $widgets,
            'app' => $widgets_app,
            'theme' => $widgets_theme,
            'title' => $wg['title'],
            'border' => $wg['border'],
            'tpl' => $wg['tpl'],
            'domid' => $wg['domid']?$wg['domid']:$domid,
            'classname' => $wg['classname'],
        );

        $post = $this->_request->get_post();
        unset($post['__wg']);
        
        $set['params'] = $post;
        $set['_domid'] = $set['domid'];
        
      
        $validator=$this->validator_widgets($widgets,$widgets_app,$theme,'insert',$set);
        if($validator!==true){
            echo $validator;exit;
        }
       
        
        
        kernel::single('base_session')->start();

        $i=is_array($_SESSION['_tmp_wg_insert'])?count($_SESSION['_tmp_wg_insert']):0;
        $_SESSION['_tmp_wg_insert'][$i] = $set;
        $data = kernel::single('business_theme_widget')->admin_wg_border(
            array(  'title'=>$set['title'],
                    'domid'=>$set['domid'],
                    'border'=>$set['border'],
                    'widgets_type'=>$set['widgets_type'],
                    'html'=> kernel::single('business_theme_widget')->store_fetch($set, true,$this->store),
                    'border'=>$set['border']
            ),
            $theme,true);
        $data = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $data);
        echo $data;
    }


    public function doeditwidgets($widgets_id,$theme){

        if(is_numeric($widgets_id)){
            $widgetObj = app::get('business')->model('widgets_instance')->getList('*', array('widgets_id'=>$widgets_id));
            $widgetObj = $widgetObj[0];
        }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){
            $widgetObj = $_SESSION['_tmp_wg_insert'][$match[1]];
        }

        $this->pagedata['widget_editor'] = kernel::single('business_theme_widget')->store_editor($widgetObj['widgets_type'],$widgetObj['app'],$widgetObj['theme'],$theme,$widgetObj['params'],$this->store);
        $this->pagedata['widgets_type'] = $widgetObj['widgets_type'];

         $this->pagedata['widgetsTpl'] = str_replace('\'','\\\'',kernel::single('site_theme_widget')->admin_wg_border(array('title'=>$widgetObj['title'],'html'=>'loading...'),$theme));


        $this->pagedata['widgets_id'] = $widgets_id;
        $this->pagedata['widgets_title'] = $widgetObj['title'];
        $this->pagedata['widgets_border']=$widgetObj['border'];
        $this->pagedata['widgets_classname']=$widgetObj['classname'];
        $this->pagedata['widgets_domid']=$widgetObj['domid'];
        $this->pagedata['widgets_app'] = $widgetObj['app'];
        $this->pagedata['widgets_theme'] = $widgetObj['theme'];

        $this->pagedata['widgets_tpl']=$widgetObj['tpl'];


        $this->pagedata['theme'] = $theme;
        $this->pagedata['basic_config'] = kernel::single('site_theme_base')->get_basic_config($theme);
        $this->display('site/theme/widget/do_edit_widgets.html','business');
    }

    public function savewidget($widgets_id,$widgets,$widgets_app,$widgets_theme,$theme,$domid)
    {
        header('Content-Type: text/html;charset=utf-8');

        $wg = $this->_request->get_post('__wg');

        if($widgets_type=='html')   $widgets_type='usercustom';
        $set = array(
            'widgets_type'=>$widgets,
            'app' => $widgets_app,
            'theme' => $widgets_theme,
            'title' => $wg['title'],
            'border' => $wg['border'],
            'tpl' => $wg['tpl'],
            'domid' => $wg['domid']?$wg['domid']:$domid,
            'classname' => $wg['classname'],
        );

        $post = $this->_request->get_post();
        unset($post['__wg']);

        $set['params'] = $post;
        $set['_domid'] = $set['domid'];
        $set['store_id'] = $this->store['store_id'];
        
      
        $validator=$this->validator_widgets($widgets,$widgets_app,$theme,'edit',$set);
        if($validator!==true){
            echo $validator;exit;
        }
       
        
        if(is_numeric($widgets_id)){
            $sdata = $set;
            $sdata['store_id'] = $this->store['store_id'];
            kernel::single('business_theme_widget')->save_widgets($widgets_id, $sdata);
            $set['widgets_id'] = $widgets_id;
        $_SESSION['_tmp_wg_update'][$widgets_id] = $set;
        }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){
            $_SESSION['_tmp_wg_insert'][$match[1]] = $set;
        }

        $data = kernel::single('site_theme_widget')->admin_wg_border(
            array(  'widgets_id'=>$widgets_id,
                    'title'=>$set['title'],
                    'domid'=>$set['domid'],
                    'border'=>$set['border'],
                    'widgets_type'=>$set['widgets_type'],
                    'html'=> kernel::single('business_theme_widget')->store_fetch($set, true,$this->store),
                    'border'=>$set['border']
            ),
            $theme,true);
        $data = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $data);
        echo $data;
    }


    public function dopreview()
    {
        $widgets = $this->_request->get_post('widgets');
        $html = $this->_request->get_post('html');
        $files = $this->_request->get_post('files');

        if(is_array($widgets)){

            foreach($widgets as $widgets_id=>$base){
                $aTmp=explode(':',$base);
                $base_id=array_pop($aTmp);
                $base_slot=array_pop($aTmp);
                $base_file=implode(':',$aTmp);
                if($html[$widgets_id]){
                    $widgetsSet[$widgets_id] = array(
                        'core_file'=>$base_file,
                        'core_slot'=>$base_slot,
                        'core_id'=>$base_id,
                        'border'=>'__none__',
                        'params'=>array('html'=>stripslashes($html[$widgets_id]))
                    );
                }else{
                    $widgetsSet[$widgets_id] = array('core_file'=>$base_file,'core_slot'=>$base_slot,'core_id'=>$base_id);
                }
            }
        }

        if(false !== ($map = kernel::single('site_theme_widget')->save_preview_all($widgetsSet,$files))){
            setcookie('site[preview]', 'true', 0, kernel::base_url() . '/');
            $map = array(
                'success'=>true
            );
            echo json_encode($map);
        }else{
            echo json_encode(false);
        }
    }

    public function saveall()
    {
        kernel::single('base_session')->start();
        $widgets = $this->_request->get_post('widgets');
        $html = $this->_request->get_post('html');
        $files = $this->_request->get_post('files');

        if(is_array($widgets)){

            foreach($widgets as $widgets_id=>$base){
                $aTmp=explode(':',$base);
                $base_id=array_pop($aTmp);
                $base_slot=array_pop($aTmp);
                $base_file=implode(':',$aTmp);
                if($html[$widgets_id]){
                    $widgetsSet[$widgets_id] = array(
                        'core_file'=>$base_file,
                        'core_slot'=>$base_slot,
                        'core_id'=>$base_id,
                        'border'=>'__none__',
                        'params'=>array('html'=>stripslashes($html[$widgets_id]))
                    );
                }else{
                    $widgetsSet[$widgets_id] = array('core_file'=>$base_file,'core_slot'=>$base_slot,'core_id'=>$base_id);
                }
            }
        }
        if(false !== ($map = kernel::single('business_theme_widget')->save_all($widgetsSet,$files,$this->store['store_id']))){
            echo json_encode($map);
        }else{
            echo json_encode(false);
        }
    }

    /**
     * singlepage 打开单个页面(不应用模版)
     * @param  string $view   
     * @param  string $app_id 
     */
    function singlepage($view, $app_id=''){

        $page = $this->fetch($view, $app_id);
        $this->pagedata['_PAGE_PAGEDATA_'] = $this->_vars;

        $re = '/<script([^>]*)>(.*?)<\/script>/is';
        $this->__scripts = '';
        $page = preg_replace_callback($re,array(&$this,'_singlepage_prepare'),$page)
            .'<script type="text/plain" id="__eval_scripts__" >'.$this->__scripts.'</script>';

        //后台singlepage页面增加自定义css引入到head标签内的操作--@lujy-start
        $recss = '/<link([^>]*)>/is';
        $this->__link_css = '';
        $page = preg_replace_callback($recss,array(&$this,'_singlepage_link_prepare'),$page);
        $this->pagedata['singleappcss'] = $this->__link_css;
        //--end

        $this->pagedata['statusId'] = $this->app->getConf('b2c.wss.enable');
        $this->pagedata['session_id'] = kernel::single('base_session')->sess_id();
        $this->pagedata['desktop_path'] = app::get('desktop')->res_url;
        $this->pagedata['shopadmin_dir'] = dirname($_SERVER['PHP_SELF']).'/';
        $this->pagedata['shop_base'] = $this->app->base_url();
        $this->pagedata['desktopresurl'] = app::get('desktop')->res_url;
        $this->pagedata['desktopresfullurl'] = app::get('desktop')->res_full_url;


        $this->pagedata['_PAGE_'] = &$page;
        $this->display('site/tools/singlepage.html','business');
    }
}