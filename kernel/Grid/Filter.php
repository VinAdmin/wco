<?php
namespace wco\kernel\Grid;

use wco\forms\Form;

class Filter extends Form implements \wco\kernel\Grid\Test{
    static private $filer_input_type;
    static public $_get;
    protected static $_column;
    private static $key_column;
    
    protected static function setColumnFilter($column,$aliace) {
        self::$key_column = $column;
        self::$_column[$column]['name'] = $aliace;
    }

    public function setFilter(string $type_input) {
        self::$_column[self::$key_column]['type_input'] = $type_input;
        self::$filer_input_type[] = $type_input;
    }
    
    protected function FilterForm($name) {
        if(!isset(self::$_column[$name]['type_input'])) { return false; }
        
        if(self::$_column[$name]['type_input'] == 'text'){
            $value = strip_tags(filter_input(INPUT_GET, $name));
            self::$_get[$name] = $value;
            return $this->Input(self::$_column[$name]['type_input'], $name, $value)->Field();
        }
    }
    
    static public function GetMethod() {
        return self::$_get;
    }
}

interface Test {
    //public function setColumn($column,$aliace);
}