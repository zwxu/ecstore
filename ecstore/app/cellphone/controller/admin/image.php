<?php  
class cellphone_ctl_admin_image extends desktop_controller
{
    var $workground = 'cellphone.wrokground.mobile';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index()
    {
        $image = &app::get('image')->model('image');
        $allsize = array();
        
        if($_POST['pic']){
            $image_set = (array)$_POST['pic'];
            $cur_image_set = $this->app->getConf('image.set');

            foreach(kernel::servicelist('image_set') as $class_name=>$service){
                if($service instanceof image_interface_set){
                    if(method_exists($service,'setconfig')){
                       $service->setconfig($_POST);
                    }
                }
            }
            
            foreach($image_set as $size=>$item){
                if($item['wm_type']=='text'){
                    $image_id = '';
                    if($cur_image_set && $cur_image_set[$size] && $cur_image_set[$size]['wm_text_image']){
                        $image_id = $cur_image_set[$size]['wm_text_image'];
                    }
                    //生产文字水印图
                    if(constant("ECAE_MODE")) {
                      $tmpfile = tempnam(sys_get_temp_dir(),'img');
                    } else {
                      $tmpfile = tempnam(DATA_DIR,'img');
                    }
                    $url = 'http://chart.apis.google.com/chart?chst=d_text_outline&chld=000000|20|h|ffffff|_|'.urlencode($item['wm_text']);
                    file_put_contents($tmpfile,file_get_contents($url));
                    $image_id = $image->store($tmpfile,$image_id,null,$item['wm_text']);
                    $image_set[$size]['wm_text_image'] = $image_id;
                }
            }
            $this->app->setConf('image.set',$image_set);
            $cur_image_set = $this->app->getConf('image.set');
        }
        
        $def_image_set = $this->app->getConf('image.cellphone.set');
        $minsize_set = false;
        
        foreach($def_image_set as $k=>$v){
            if(!$minsize_set || $v['height'] < $minsize_set['height']){
                $minsize_set = $v;
            }
        }

        $this->pagedata['allsize'] = $def_image_set;
        $this->pagedata['minsize'] = $minsize_set;
        $cur_image_set = $this->app->getConf('image.set');
        $this->pagedata['image_set'] = $cur_image_set;
        $this->pagedata['this_url'] = $this->url;
        $this->page('admin/imageset.html');
    }
}