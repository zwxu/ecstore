<?php
class business_ctl_admin_storeroles extends desktop_controller {
    var $workground = 'business.wrokground.store';

    public function __construct($app) {
        parent :: __construct($app);

        header("cache-control: no-store, no-cache, must-revalidate");
    } 

    function index() {
        $this -> finder('business_mdl_storeroles', array('title' => '店员角色',
                'allow_detail_popup' => true,
                'use_buildin_export' => true,
                'use_buildin_set_tag' => true,
                'use_buildin_filter' => true,
                'use_view_tab' => true,
                'actions' => array(
                   // array('label' => app :: get('business') -> _('新建角色'), 'href' => 'index.php?app=business&ctl=admin_storeroles&act=addnew', 'target' => 'dialog::{title:\'' . app :: get('business') -> _('新建角色') . '\'}'),
                    )

                ));
    } 

    function addnew() {
        /**
         * print_r('<pre>');
         * 
         * print_r($this ->get_cpmenu());
         * 
         * print_r('</pre>');
         * 
         * exit;
         */
        // 业务权限
        //$treedata = $this -> get_cpmenu();
        $obj_storeroles=  &app :: get('business') -> model('storeroles');
        $treedata =$obj_storeroles -> get_cpmenu();

        foreach($treedata as $item) {
            $this -> pagedata['menus3'][] = $this -> procHTML($item);
        } 

        $this -> page('admin/store/add_roles.html');
    } 

    

    function procHTML(&$tree) {
        $html = '';

        if ($tree['label']) {
            $html .= "<li style='text-align:left;font-weight:bold;font-style:italic;'>" . $tree['label'];
        } 
        foreach($tree['items'] as $k => $t) {
            if ($t['checked']) {
                $html .= "<li style='padding-left:25px;text-align:left;'>
                           <input  class='leaf'  type='checkbox' checked='checked' name='workground[]' value='" . "app=" . $t['app'] . "&ctl=" . $t['ctl'] . "&act=" . $t['link'] . "'>" . $t['label'];
            } else {
                $html .= "<li style='padding-left:25px;text-align:left;'>
                           <input  class='leaf'  type='checkbox' name='workground[]' value='" . "app=" . $t['app'] . "&ctl=" . $t['ctl'] . "&act=" . $t['link'] . "'>" . $t['label'];
            } 
            // $html .= $this->procHTML($t['parent']);
            $html = $html . "</li>";
        } 
        // return $html ? "<ul>".$html."</ul>" : $html;
        return "<ul>" . $html . "</ul>";
    } 

    function save() {
        $this -> begin();
        $roles = $this -> app -> model('storeroles');
        if ($roles -> validate($_POST, $msg)) {
            if ($roles -> save($_POST))
                $this -> end(true, app :: get('business') -> _('保存成功'));
            else
                $this -> end(false, app :: get('business') -> _('保存失败'));
        } else {
            $this -> end(false, $msg);
        } 
    } 

    function edit($roles_id) {
        $objroles = &$this -> app -> model('storeroles');

        $sdf_roles = $objroles -> dump($roles_id);

        $workground = unserialize($sdf_roles['workground']);

        $this -> pagedata['roles'] = $sdf_roles; 
        // 业务权限
         $obj_storeroles=  &app :: get('business') -> model('storeroles');
        $treedata =$obj_storeroles -> get_cpmenu();
     

        foreach($treedata as $k => &$tree) {
            foreach($tree['items'] as $k => &$t) {
                $permission = "app=" . $t['app'] . "&ctl=" . $t['ctl'] . "&act=" . $t['link'];

                if (in_array($permission, $workground)) {
                    $t['checked'] = 1;
                } 
            } 
        } 

        foreach($treedata as $item) {
            $this -> pagedata['menus3'][] = $this -> procHTML($item);
        } 

        $this -> page('admin/store/add_roles.html');
    } 
} 
