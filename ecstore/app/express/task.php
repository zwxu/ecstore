<?php



class express_task
{

    public function post_install()
    {
        kernel::log('Initial express');
        kernel::single('base_initial', 'express')->init();
    }//End Function
}//End Class
