<?php
namespace vadc\kernel;

/**
 * Абстрактный класс принимает и возвращает собранную строку.
 * @package    Vadac
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2016-2021
 * @abstract
 */
abstract class Assembly {
    static private $assembly = null;
    
    /**
     * @param string $str
     */
    static protected function setAssembly(string $str) {
        self::$assembly = $str;
    }
    
    /**
     * @return string
     */
    static public function getAssembly() {
        return self::$assembly;
    } 
}
