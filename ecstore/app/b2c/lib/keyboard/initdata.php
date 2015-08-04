<?php


class b2c_keyboard_initdata {
    //配置数组
    public $setting = array();
    
    
    public function __construct( &$app ) {
        $o = app::get('desktop')->router();
        
        $url = $o->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'admin_order') );
        $setting['导航菜单上的栏目'][] = array('title'=>'打开订单主菜单','params'=>array('keyword'=>'1','control'=>array('alt'=>'true')),'arg'=>$url);
        
        $url = $o->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'admin_goods') );
        $setting['导航菜单上的栏目'][] = array('title'=>'打开商品主菜单','params'=>array('keyword'=>'2','control'=>array('alt'=>'true')),'arg'=>$url);
        
        $url = $o->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'admin_member') );
        $setting['导航菜单上的栏目'][] = array('title'=>'打开会员主菜单','params'=>array('keyword'=>'3','control'=>array('alt'=>'true')),'arg'=>$url);
        
        $url = $o->gen_url( array('app'=>'b2c','act'=>'maintenance','ctl'=>'admin_sales_order') );
        $setting['导航菜单上的栏目'][] = array('title'=>'打开营销主菜单','params'=>array('keyword'=>'4','control'=>array('alt'=>'true')),'arg'=>$url);
                
        $this->setting = $setting;
    }
}
