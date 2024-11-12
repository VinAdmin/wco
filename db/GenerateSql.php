<?php
namespace wco\db;

use wco\db\Model\ModelSelect;
use wco\db\Model\ModelInsert;
use wco\db\Model\ModelDelete;
use wco\db\Model\ModelUpdate;

/**
 * Библиотека по упрощению работы с PDO
 *
 * @author vinamin
 */
abstract class GenerateSql extends Assembly implements interfaceDB{
    private $modelSelect = null;
    protected $toString = null;
    static public $lastId = null;
            
    function __construct() {
        //Мдель выборки Select
        $this->modelSelect = new ModelSelect();
    }
    
    /**
     * Инцылизация таблицы.
     * @return NULL
     */
    public function init() {
        return null;
    }
        
    /**
     * Собирает запрос SELECT
     * @param string $param
     * @return ModelSelect
     */
    public function select($param = null) {
        $this->modelSelect->select($param);
        return new ModelSelect($this->init());
    }
    
    /**
     * Собирает запрос SELECT
     */
    public function from() {
        $this->modelSelect->form($this->init());
        return new ModelSelect();
    }
    
    /**
     * Вывод всех записей
     * @param array $params array(':id'=>'id')
     * @return array
     */
    public function fetch(array $params=array()) {
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($params);
            $return = $prepare->fetch(\PDO::FETCH_ASSOC);
            $prepare = null;
            ModelSelect::Clear();
            return $return;
        } catch (\PDOException $ex) {
            echo "<span style=\"color: blue\">".self::getAssembly().'</span>';
            echo $ex;
            exit();
        }
    }
    
    /**
     * Вывод всех записей
     * @param array $params array(':id'=>'id')
     * @return array
     */
    public function fetchAll(array $params=array(), $pdo = \PDO::FETCH_ASSOC) {
        //var_dump(self::getAssembly());
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($params);
            $return = $prepare->fetchAll($pdo);
            $prepare = null;
            ModelSelect::Clear();
            return $return;
        } catch (\PDOException $ex) {
            echo '<div style="color: blue;">'.self::getAssembly().'</div>';
            echo $ex;
            exit();
        }
    }
    
    public function Count(array $params=array()) {
        //var_dump(self::getAssembly());
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($params);
            $return = $prepare->fetchColumn();
            $prepare = null;
            ModelSelect::Clear();
            return $return;
        } catch (\PDOException $ex) {
            echo '<div style="color: blue;">'.self::getAssembly().'</div>';
            echo $ex;
            exit();
        }
    }
    
    /**
     * @param array $columns [':id'=>'value']
     * @param string $where
     * @param string $from default null
     * @return type
     */
    public function Update(array $columns, string $where, string $from=null) {
        $modelUpdate = new ModelUpdate();
        if(empty($from)){
            $from = $this->init();
        }
        $modelUpdate::setTable($from);
        $modelUpdate::SET($columns);
        $modelUpdate::WHERE($where);
        $modelUpdate::Assembly();
        //var_dump(self::getAssembly());
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($columns);
            $prepare = null;

            return true;
        } catch (Exception $ex) {
            echo $ex;
            return false;
        }
        
    }
    
    /**
     * Insert
     * 
     * @param array $param
     * @param type $table
     * @return boolean
     */
    public function insert(array $param, $table=null) {
        $insert = new ModelInsert();
        $table = empty($table) ? $this->init() : $table;
        $insert->Insert($table, $param);
        //var_dump(self::getAssembly());
        //var_dump($insert->par);
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($insert->par);
            $prepare = null;
            
            if(isset(DB::$config_db[DB::$connect_type_db])){
                if(DB::$config_db[DB::$connect_type_db] == 'sqlite'){
                    $this->LastId($table);
                }
            }
            
            return true;
        } catch (\PDOException $ex) {
            echo  $ex;
            return false;
        }
    }
    
    /**
     * @param type $table
     */
    private function LastId(string $table) {
        $stmt = DB::connect()->query("select seq from sqlite_sequence where name='".$table."'");
        self::$lastId = $stmt->fetchColumn();
        $stmt = null;
    }
    
    public function delete($where = null, string $from = null) {
        $modelDelete = new ModelDelete();
        if(empty($from)){
            $from = $this->init();
        }
        $modelDelete->setTable($where, $from);
        //var_dump(self::getAssembly());
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute();
            $prepare = null;
            return true;
        } catch (Exception $ex) {
            echo  $ex;
        }
    }
    
    /**
     * Insert
     * 
     * @param array $param
     * @param type $table
     * @return boolean
     */
    public function InsertToUpdate(array $param,string $key, $table=null) {
        $insert = new ModelInsert();
        $table = empty($table) ? $this->init() : $table;
        $insert->InsertToUpdate($table, $param, $key);
        
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($insert->par);
            $prepare = null;
            
            if(isset(DB::$config_db[DB::$connect_type_db])){
                if(DB::$config_db[DB::$connect_type_db] == 'sqlite'){
                    $this->LastId($table);
                }
            }
            
            return true;
        } catch (\PDOException $ex) {
            echo  $ex;
            return false;
        }
    }
}

interface interfaceDB{
    public function init();
    public function from();
    public function fetchAll(array $params=array());
}
