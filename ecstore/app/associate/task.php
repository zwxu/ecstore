<?php

class associate_task
{

    public function pre_install()
    {
        kernel::log('Initial associate');
        kernel::single('base_initial', 'associate')->init();
    }//End Function
}//End Class