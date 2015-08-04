<?php 

class b2c_data_clean
{
    
    /*
     * 清楚初始化数据
     */
    public function clean()
    {
        app::get('b2c')->model('goods')->delete( array() );
        app::get('b2c')->model('goods_cat')->delete( array() );
        app::get('b2c')->model('goods_cat')->cat2json();
        app::get('b2c')->model('goods_type')->delete( array() );
        app::get('b2c')->model('brand')->delete( array() );
        app::get('b2c')->model('brand')->brand2json();
        kernel::single("base_initial", "b2c")->init();
    }
    #End Func
}