<?php

 
class service implements Iterator{
    
    function __construct($service_define,$filter=null){
        $this->iterator = new ArrayIterator($service_define['list']);
        $this->interface = $service_define['interface'];
        $this->filter = $filter;
        $this->valid();
    }

    function current(){
        return $this->current_object;
    }
    
    public function rewind() {
        $this->iterator->rewind();
    }

    public function key() {
        return $this->iterator->current();
    }

    public function next() {
        return $this->iterator->next();
    }

    public function valid() {
        while($this->iterator->valid()){
            if($this->filter()){
                return true;
            }else{
                $this->iterator->next();
            }
        };
        return false;
    }
    
    private function filter(){
		if ($this->filter){
			$current = $this->iterator->current();
			if (is_array($this->filter) && !in_array($current,$this->filter)) $this->iterator->next();
			if (!is_array($this->filter) && $this->filter != $current) $this->iterator->next();
		}
		$current = $this->iterator->current();
        if($current){
            $this->current_object = kernel::single($current);
            if($this->current_object){
                if($this->interface && $this->current_object instanceof $this->interface){
                    return false;
                }
                return true;
            }
        }
        return false;
    }

}


