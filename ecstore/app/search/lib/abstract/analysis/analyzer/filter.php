<?php

require_once('Zend/Search/Lucene/Analysis/Analyzer.php');

abstract class search_abstract_analysis_analyzer_filter 
{
    
    abstract public function normalize($input);

}//End Class