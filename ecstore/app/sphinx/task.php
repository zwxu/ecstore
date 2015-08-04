<?php

class sphinx_task
{

    public function pre_install()
    {
        kernel::log('Initial sphinx');
        kernel::single('base_initial', 'sphinx')->init();
        //mkdir_p(DATA_DIR.'/search/sphinx');
    }//End Function
}//End Class