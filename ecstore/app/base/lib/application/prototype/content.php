<?php

 

//todo： 分离列表和实体
//利用实体的 class::instance() 创建对象，来验证是否有效


class base_application_prototype_content implements Iterator{

    protected $current;
    protected $path;
    protected $iterator = false;

    function __construct($app=null){
        if($app){
            $this->app = $app;
        }
    }

    public function detect($app,$current=null){
        $this->iterator = null;
        $this->target_app = is_string($app)?app::get($app):$app;
        if($current){
            $this->set_current($current);
        }
        return $this;
    }

    function iterator(){
        if(!is_object($this->iterator)){
            $this->iterator = $this->init_iterator();
        }
        return $this->iterator;
    }

    public function rewind() {
        $this->iterator()->rewind();
    }

    public function current() {
        return $this;
    }

    public function key() {
        return $this->key;
    }

    public function next() {
        return $this->iterator()->next();
    }

    public function valid() {
        while($this->iterator()->valid()){
            if($this->prototype_filter()){
                return true;
            }else{
                $this->iterator()->next();
            }
        };
        return false;
    }

    function filter(){
        return true;
    }

    function row(){
        return array(
            'app_id'=>$this->target_app->app_id,
            'content_type' => $this->content_typename(),
            'content_name' => $this->key(),
            );
    }

    function content_typename(){
        if(!$this->content_typename){
            $class_name = get_class($this);
            $this->content_typename = substr($class_name,strrpos($class_name,'_')+1);
        }
        return $this->content_typename;
    }

    function uninstall(){
        kernel::log('Removing '.$this->content_typename().' '.$this->key());
    }

    function set_current($key){
        $this->key = $key;
    }

    function prototype_filter(){
        return $this->filter();
    }
    
    function update($app_id){
        $this->clear_by_app($app_id);
        foreach($this->detect($app_id) as $name=>$item){
            $item->install();
        }
        return true;
    }
    
    //必须被重载
    function last_modified($app_id){
        return 1234567;
    }

    //以下两个如果要重载,就一起重载
    function install(){
        kernel::log('Installing '.$this->content_typename().' '.$this->key());
        return app::get('base')->model('app_content')->insert($this->row());
    }
    
    //清除所有本类型本应用的资源
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('base')->model('app_content')->delete(array(
            'app_id'=>$app_id,'content_type'=>$this->content_typename()));
    }
    
    //关闭所有本类型应用资源，默认是清除，如有其它需求，请重载
    function pause_by_app($app_id) 
    {
        $this->clear_by_app($app_id);
    }//End Function

    //雇用所有本类型应用资源，默认是安装，如有其它需求，请重载
    function active_by_app($app_id) 
    {
        foreach($this->detect($app_id) as $name=>$item){
            $item->install();
        }
    }//End Function

}
