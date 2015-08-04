<?php


class desktop_finder_builder_detail extends desktop_finder_builder_prototype{

    function main(){
        if (isset($this->detail_pages))
        {
            foreach ((array)$this->detail_pages as $k=>$detail_func)
            {
                $str_detail_order = 'detail_' . $detail_func[1] . '_order';
                if (isset($detail_func[0]->$str_detail_order) && $detail_func[0]->$str_detail_order)
                {
                    switch ($detail_func[0]->$str_detail_order)
                    {
                        case COLUMN_IN_HEAD:
                            $tmp = $this->detail_pages[$k];
                            unset($this->detail_pages[$k]);
                            $this->detail_pages = array_reverse($this->detail_pages);
                            $this->detail_pages[$k] = $tmp;
                            $this->detail_pages = array_reverse($this->detail_pages);
                            break;
                        case COLUMN_IN_TAIL:
                            $tmp = $this->detail_pages[$k];
                            unset($this->detail_pages[$k]);
                            $this->detail_pages[$k] = $tmp;
                            break;
                    }
                }
            }
        }
        if(kernel::single('base_component_request')->is_ajax()&&$_GET['singlepage']!='true'){
            //$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
            if(isset($this->detail_pages)){
                $tab_header = '<div class="tabs-wrap finder-tabs-wrap"><div class="tabs-items clearfix"><ul>';
                foreach($this->detail_pages as $k=>$detail_func){
                    if($k==$_GET['finderview']){
                        $tab_header.='<li class="tab current" item-id="'.$_GET['id'].'" url="'.$this->url.'&action=detail&finderview='.$k.'"><span>';
                        $tab_header.= $detail_func[0]->$detail_func[1];
                        $detail_html = $detail_func[0]->$detail_func[1]($_GET['id']);
                    }else{
                        //if($_GET['view'])unset($_GET['view']);
                        $tab_action = $this->url.'&action=detail&finderview='.$k;
                        $tab_header.='<li class="tab"><span>';
                        $tab_header.='<a target="{update:\'finder-detail-'.$this->name.'\'}" href="'.$tab_action.'">'.$detail_func[0]->$detail_func[1].'</a>';
                    }

                    $tab_header.='</span></li>';
                }
				$tab_header.='</ul></div>'
						   .'<div class="scroll-handle l"><span>&laquo;</span></div>'
						   .'<div class="scroll-handle r"><span>&raquo;</span></div></div>';
            }
            if(count($this->detail_pages)>1){
                echo $tab_header;
            }
            echo $detail_html;
        }else{
            if(kernel::single('base_component_request')->is_ajax()&&$_GET['singlepage']=='true'){
                 foreach($this->detail_pages as $k=>$detail_func){
                    if($_GET['finderview']==$k){
                        $html = $detail_func[0]->$detail_func[1]($_GET['id']);
                        $label = $detail_func[0]->$detail_func[1];
echo <<<EOF
<h3>{$label}</h3>
{$html}
EOF;
exit;
                    }
                 }
            }else{
                foreach($this->detail_pages as $k=>$detail_func){

                    $detail_html = $detail_func[0]->$detail_func[1]($_GET['id']);

                    $this->controller->pagedata['_detail_func'][$k] = array(
                            'label' => $detail_func[0]->$detail_func[1],
                            'html' => $detail_html,
                        );
                }

                $this->controller->singlepage('common/detail-in-one.html', 'desktop');
            }
        }
    }

}
