<?php
namespace wco\kernel\Grid;

use wco\forms\Form;

class Filter extends Form implements \wco\kernel\Grid\Test{
    static private $filer_input_type;
    static public $_get;

    public function setFilter(string $type_input) {
        self::$filer_input_type[] = $type_input;
    }
    
    protected function FilterForm($сol_number, $name) {
        if(!isset(self::$filer_input_type[$сol_number])) { return false; }
        
        if(self::$filer_input_type[$сol_number] == 'text'){
            $value = strip_tags(filter_input(INPUT_GET, $name));
            self::$_get[$name] = $value;
            return $this->Input(self::INPUT_TEXT, $name, $value)->Field();
        }else{
            return null;
        }
    }
    
    static public function GetMethod() {
        return self::$_get;
    }
}

interface Test {
    //public function setColumn($column,$aliace);
}