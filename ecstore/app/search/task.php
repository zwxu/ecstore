<?php

class search_task
{

    public function pre_install()
    {
        kernel::log('Initial search');
        kernel::single('base_initial', 'search')->init();
    }//End Function
}//End Class