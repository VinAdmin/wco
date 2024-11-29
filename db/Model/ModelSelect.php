<?php
namespace wco\db\Model;

use wco\db\Assembly;
use wco\db\DB;

/**
 * Description of ModelSelect
 *
 * @author vinamin
 */
class ModelSelect extends Assembly{
    private static $table = null;
    private static $collums = null;
    private static $joinLeft = null;
    private static $joinInner = null;
    private static $where = null;
    private static $group_by = null;
    private static $select = 'SELECT t1.*';
    public static $from = null;
    public static $order_by = null;
    public static $limit = null;
    
    function __construct(string $table = null) {
        self::$table = $table;
    }
    
    public function select($param = null) {
        self::$select = (!is_null($param)) ? 'SELECT '.$param : self::$select;
        $sql = $this->sqlString();
        self::setAssembly($sql);
    }
    
    /**
     * FROM
     * @param string $table имя таблицы.
     */
    public function form(string $table = null) {
        $table = (!is_null($table)) ? $table : self::$table;
        self::$from = ' FROM '.$table.' AS t1';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        return new ModelSelect();
    }
    
    public static function joinLeft(array $table, string $on, array $collums = null){
        $key = array_key_first($table);
        self::$collums .= (is_array($collums)) ? ','.self::ArrayToString($collums,$key) : null;
        self::$joinLeft .= ' LEFT JOIN '.$table[$key].' AS '.$key.' ON '.$on;
        return new ModelSelect();
    }
    
    /**
     * @param array $table
     * @param string $on
     * @param array $collums
     * @return \vadc\kernel\Model\ModelSelect
     */
    public static function joinInner(array $table, string $on, array $collums = null){
        $key = array_key_first($table);
        self::$collums .= (is_array($collums)) ? ','.self::ArrayToString($collums,$key) : null;
        self::$joinInner .= ' INNER JOIN '.$table[$key].' AS '.$key.' ON '.$on;
        return new ModelSelect();
    }
    
    /**
     * 
     * @param string $param
     * @return $this
     */
    public function where(string $param) {
        self::$where = ' WHERE '.$param.' ';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        return $this;
    }
    
    public function GroupBy(string $param) {
        self::$group_by = 'GROUP BY '.$param;
        $sql = $this->sqlString();
        self::setAssembly($sql);
    }
    
    public function order_by(string $param) {
        self::$order_by = ' ORDER BY '.$param;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        return new ModelSelect();
    }
    
    public function limit(int $start, $count = null) 
    {
        if(DB::$config_db['default']['db'] == 'postgresql'){
            $offset = ' OFFSET ';
        }else{
            $offset = ',';
        }
        
        $count = (!is_null($count)) ? $offset .$count : null;
        self::$limit = ' LIMIT '.$start.$count;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        return new ModelSelect();
    }
    
    private function sqlString() {
        $sql = self::$select.self::$collums.self::$from
            .self::$joinInner.self::$joinLeft.self::$where.self::$group_by
            .self::$order_by.self::$limit;
        return $sql;
    }
    
    public static function ArrayToString(array $collums = null,$as) {
        $str = null;
        if(is_array($collums)){
            foreach ($collums as $key=>$col){
                if(substr_count($col,'.')){
                    $arr_col = explode('.', $col);
                    $str_col = ''.$arr_col[0].'.'.$arr_col[1].'';
                }else{
                    $str_col = $col;
                }
                
                if(is_string($key)){
                    $str .= ''.$str_col.' AS '.$key.',';
                }else{
                    //$str .= $as.'.'.$col.',';
                    $str .= $str_col.',';
                }
            }

            return (substr($str, 0, -1));
        }
        return null;
    }
    
    public static function Clear() {
        self::$collums = null;
        self::$select = 'SELECT t1.*';
        self::$from = null;
        self::$joinInner = null;
        self::$where = null;
        self::$group_by = null;
        self::$joinLeft = null;
        self::$limit = null;
        self::$order_by = null;
    }
    
    function __destruct() {
        
    }
}
