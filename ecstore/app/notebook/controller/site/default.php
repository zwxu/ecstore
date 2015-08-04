<?php
class notebook_ctl_site_default extends site_controller{

    public function index(){
        $this->pagedata['items'] = $this->app->model('item')->getList('*');
        /*
        $gravatar = new notebook_gravatar;
        foreach($this->pagedata['items'] as $k=>$item){
            $this->pagedata['items'][$k]['avatar'] = $gravatar->get_avatar($item['item_email']);
        }
        */
        //新修改的部分开始
        foreach(kernel::servicelist('notebook_addon') as $object){
            foreach($this->pagedata['items'] as $k=>$item){
                $this->pagedata['items'][$k]['addon'][] = $object->get_output($item);
            }
        }
        //修改的部分结束
        //$this->display('default.html');
        $this->page('default.html');
    }

    public function addnew(){
        $this->begin(array("ctl" => "site_default", "act" => "index", "app"=> "notebook"));
        $data = array(
            'item_subject'=>$_POST['subject'],
            'item_content'=>$_POST['content'],
            'item_email'=>$_POST['email'],
            'item_posttime'=>time(),
        );
        $result = $this->app->model('item')->insert($data);
        $this->end($result);
    }

}
?>