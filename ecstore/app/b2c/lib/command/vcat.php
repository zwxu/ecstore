<?php

class b2c_command_vcat extends base_shell_prototype 
{

    var $command_build = '创建虚拟分类数据';
    function command_build(){
        $this->model = app::get('b2c')->model('goods_virtual_cat');
        $obj_vcat = app::get('b2c')->model('goods_virtual_cat');

        $oSearch = &app::get('b2c')->model('search');
        $vcatList = $obj_vcat->getList('virtual_cat_id,filter');
        if( $vcatList ){
            foreach( $vcatList as $k => $v ){
                $filters=$obj_vcat->_mkFilter($v['filter']);
                $v['url']=app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array(null,null,0,'','',$v['virtual_cat_id']) ));
                $obj_vcat->save( $v );
            }
        }
    }

}//End Class
