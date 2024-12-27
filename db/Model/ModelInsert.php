<?php
namespace wco\db\Model;

use wco\db\Assembly;
use wco\db\DB;

/**
 * Класс подготавливает запрос для добавления записи в таблицу
 * @package    Vadac
 * @subpackage DB
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2016-2021
 */
class ModelInsert extends Assembly{
    
    public $par = array();
    public $returning = null;

    /**
     * Генерирует строку для запроса INSERT
     * @param string $table Таблица
     * @param array $columns [clumn1=>value1,clumn2=>value2] ключь массива имя 
     * столбща, параметр массива содержимое столбца
     */
    public function Insert(string $table, array $columns) {
        if(!isset(DB::$config_db[DB::$connect_type_db]['db'])){ 'Не указан тип базы в конфигурации db' .die();}
        if (DB::$config_db[DB::$connect_type_db]['db'] == 'postgresql') { $this->returning = 'RETURNING id'; }
        
        $str_insert = implode(',', array_keys($columns));
        $str_values = implode(',:', array_keys($columns));
        $sql = "INSERT INTO $table ($str_insert) VALUES (:$str_values) $this->returning";
        $this->arrayParams($columns);
        
        self::setAssembly($sql); //Задаем строку
    }
    
    /**
     * Подготовка массива для PDO
     * @param array $params
     */
    private function arrayParams(array $params) {
        foreach ($params AS $kay=>$val){
            $this->par[':'.$kay] = $val;
        }
    }
    
    public function ShieldsPillars($columns) {
        foreach($columns as $key=>$row){
            $new_key[$key] = $row;
        }
        return $new_key;
    }
    
    /**
     * 
     * @param string $table
     * @param array $columns
     * @param array $key
     */
    public function InsertToUpdate(string $table, array $columns, string $key) {
        $str_insert = implode(',', array_keys($columns));
        $str_values = implode(',:', array_keys($columns));
        
        $sql = 'INSERT INTO '.$table.' ('.$str_insert.') VALUES (:'.$str_values.') ON DUPLICATE KEY UPDATE '.$key;
        $this->arrayParams($columns);
        
        self::setAssembly($sql); //Задаем строку
    }
}
