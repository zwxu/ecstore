<?php
class dev_controller extends base_controller{
    
    var $workground = 'project';
    
    function page($html){
        $menus = array(
                'project'=>app::get('dev')->_('项目'),
                //'tools'=>'工具',
                'apps'=>app::get('dev')->_('应用程序'),
                'doc'=>app::get('dev')->_('文档'),
                'setting'=>app::get('dev')->_('系统设置'),
            );
        $this->pagedata['__CUR_MENU__'] = $this->workground;
        $this->pagedata['__MENUS__'] = $menus;
        $this->pagedata['__PAGE__'] = $html;
        $this->display('frame.html');
    }
    
}