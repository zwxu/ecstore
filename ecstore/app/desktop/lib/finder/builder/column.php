<?php


class desktop_finder_builder_column extends desktop_finder_builder_prototype{

    function main(){
        $finder_aliasname = $_GET['finder_aliasname']?$_GET['finder_aliasname']:$_POST['finder_aliasname'];
        if($_POST['col']){
            $finder_aliasname = $finder_aliasname.'.'.$this->controller->user->user_id;
            $cols = $this->app->setConf('view.'.$this->object_name.'.'.$finder_aliasname,implode(',',$_POST['col']));
            if($_POST['allcol']){
                $this->app->setConf('listorder.'.$this->object_name.'.'.$finder_aliasname,implode(',',$_POST['allcol']));
            }
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('desktop')->_('设置成功').'"}';
        }else{
           $in_use = array_flip($this->getColumns());
            $all_columns = &$this->all_columns();

            $listorder = explode(',',$this->app->getConf('listorder.'.$this->object_name.'.'.$finder_aliasname.'.'.$this->controller->user->user_id));
            if($listorder){
                $ordered_columns = array();
                foreach($listorder as $col){
                    if(isset($all_columns[$col])){
                        $ordered_columns[$col] = $all_columns[$col];
                        unset($all_columns[$col]);
                    }
                }
                $all_columns = array_merge((array)$ordered_columns,(array)$all_columns);
                $ordered_columns = null;
            }

            $domid = $this->ui->new_dom_id();
            $html = '<div class="gridlist">';
            $html .= '<form id="'.$domid.'" method="post" action="index.php?'.$_SERVER['QUERY_STRING'].'">';
            $mv_handler = $this->ui->img(array('src'=>'bundle/grippy.gif', 'class'=>'move-handler'));
            $i=0;
            foreach($all_columns as $key=>$col){
                $i++;
                $html .= '<div class="row">';
                $html .= '<div class="row-line item"><input type="hidden" value="'.$key.'" name="allcol[]" />'.$mv_handler.'<input type="checkbox" '.(isset($in_use[$key])?' checked="checked" ':'').' value="'.$key.'" name="col[]" id="finder-col-set-'.$i.'" />
                    <label for="finder-col-set-'.$i.'">'.app::get('desktop')->_($col['label']).'</label></div>';
                $html .= '</div>';
            }
            $finder_id=$_GET['_finder']['finder_id'];
            $html .= '<!-----.mainHead-----&darr;&nbsp;'.app::get('desktop')->_('拖动改变顺序').'-----.mainHead----->';
            $html .= '<!-----.mainFoot-----<div class="table-action"><button class="btn btn-primary" onclick="$(\''.$domid.'\').fireEvent(\'submit\',{stop:$empty})"><span><span>'.app::get('desktop')->_('保存提交').'</span></span></button></div>-----.mainFoot----->';
            $html .= '<input type="hidden" name="finder_aliasname" value="'.$finder_aliasname.'"/>';
            $html .= '</form>';
            $html .= '</div>';

            $html.=<<<EOF
            <script>
              (function(){
                var scrollAuto =  new Scroller($('{$domid}').getContainer());
                new Sortables($('{$domid}'),{clone:false,opacity:.5,handle:'.move-handler',onStart:function(){
                    $('{$domid}').addClass('move-active');
                    scrollAuto.start();
                },onComplete:function(){
                    scrollAuto.stop();
                    $('{$domid}').removeClass('move-active');
                }});
                $('{$domid}').store('target',{onComplete:function(){
                    if(!$('{$domid}')) alert('请至少选择一列');
                    else $('{$domid}').getParent('.dialog').retrieve('instance').close();
                    window.finderGroup['{$finder_id}'].refresh();
                }});
              })();
            </script>
EOF;

            echo $html;
        }
    }
}
