<?php

class business_theme_widget_style
{
    public function prefix($html='',$prefix_class=''){
        if(empty($html))return '';
        if(empty($prefix_class))return $html;
        $ldq = preg_quote("{",'!');
        $rdq = preg_quote("}",'!');
        preg_match_all('/<\s*style.*?>(.*?)<\s*\/\s*style\s*>/is', $html, $matchs);
        if(isset($matchs[0][0]) && !empty($matchs[0][0])){
            foreach($matchs[0] AS $matchcontent){
                $html = str_replace($matchcontent, '', $html);
            }
        }
        //没有样式则返回原html
        if(empty($matchs[1])){
            return $html;
        }
        $styles=implode('',$matchs[1]);
        $styles = preg_replace("!/\*.*?\*/!",'',$styles);//去掉注释
        $styles = str_replace('<!--', '', $styles);
        $styles = str_replace('-->', '', $styles);
        //preg_replace("!<\!-{2}.*?-{2}>/!",'',$styles);//去掉注释
        if(empty($styles)){
           return $html;
        }
        $result=preg_split('!\{(.*?)\}!',$styles,-1,PREG_SPLIT_DELIM_CAPTURE);
        $arr=array();
        foreach($result as $i=>$V){
           $i = $i%2;
           if(trim($V)){
               if($i==0){
                   $V='.'.$prefix_class.' '.$V;
               }else{
                   $V='{'.$V."}\n";
               }
           }
           $arr[]=$V;
        }
        $styles=implode('',$arr);
        $html='<style>'.$styles.'</style><div class="'.$prefix_class.'">'.$html.'</div>';
        return $html;
    }

}//End Class
