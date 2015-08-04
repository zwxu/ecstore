<?php


class bdlink_input_helper 
{
    
    function input_refer($params)
    {
        $render = new base_render(app::get('bdlink'));
        if($params['id'] && $params['name']) {
            $filter = array(
                'target_id'   => $params['id'],
                'target_type' => $params['ident'],
            );
            if(empty($params['show'])) {
                $render->pagedata['show'] = array('refer_id', 'refer_url');
            } else {
                $render->pagedata['show'] = explode(',', $params['show']);
            }
            
            $render->pagedata['name'] = $params['name'];
            $params = kernel::single('bdlink_mdl_link')->getList('*', $filter, 0, 1);
            if($params[0]) {
                $render->pagedata['params'] = $params[0];
                return $render->fetch('show_link.html');
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}