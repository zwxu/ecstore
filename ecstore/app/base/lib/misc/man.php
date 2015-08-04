<?php

 
class base_misc_man{

    function show($target){
        list($class,$func) = explode(':',$target);
        $ReflectionClass = new ReflectionClass($class);

        echo "\nClass ".$class."\n";
        $this->show_class_file($ReflectionClass);
        $this->show_doc($ReflectionClass);

        echo "\n";

        foreach($ReflectionClass->getProperties() as $property){
            $this->show_property($property);
        }

        echo "\n";

        foreach($ReflectionClass->getMethods() as $method){
            $this->show_method($method);
        }
    }

    function show_class_file($ReflectionClass){
        $file = $ReflectionClass->getFileName();
//        $sline = $ReflectionClass->getStartLine();
//        $eline = $ReflectionClass->getEndLine();
        echo "<$file>\n\n";
    }

    function show_property($ReflectionProperty){
        if($ReflectionProperty->isPublic()){
            echo $this->show_doc($ReflectionProperty);
            echo "  ",
                $ReflectionProperty->getName(),
                ' = ',
                str_replace("\n","\n           ",var_export($ReflectionProperty->getValue(),1)),
                ";\n\n";
        }
    }

    function show_method($ReflectionMethod){
        if($ReflectionMethod->isPublic()){
            echo $this->show_doc($ReflectionMethod);
            echo "  function ",
                $ReflectionMethod->getName(),
                ' (';
            foreach($ReflectionMethod->getParameters() as $ReflectionParameter){
                $this->show_parameter($ReflectionParameter);
            }
            echo ")\n\n";
        }
    }

    function show_parameter($ReflectionParameter){
    }

    function show_doc($object){
        $document = $object->getDocComment();
        if($document){
            echo $document."\n";
        }
    }
}
