<?php
namespace wco\db\Model;

use wco\db\Assembly;
use wco\db\DB;

/**
 * Description of ModelSelect
 *
 * @author Olkhin Vitaliy <ovvitalik@gmail.com>
 * @copyright (c) 2022 - 2026, Olkhin Vitaliy
 */
class ModelSelect extends Assembly{
    private $table = null;
    private $collums = null;
    private $joinLeft = [];
    private $joinInner = [];
    private $where = null;
    private $group_by = null;
    private $select = 'SELECT t1.*';
    public $from = null;
    public $order_by = null;
    public $limit = null;
    
    function __construct(string $table = null) {
        $this->table = $table;
    }
    
    public function select($param = null) {
        $this->select = (!is_null($param)) ? 'SELECT '.$param : $this->select;
        $sql = $this->sqlString();
        self::setAssembly($sql);
    }
    
    /**
     * FROM
     * @param string $table
     * @return ModelSelect
     */
    public function from(string $table = null): ModelSelect {
        $table = (!is_null($table)) ? $table : $this->table;
        $this->from = ' FROM '.$table.' AS t1';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    public function joinLeft(array $table, string $on, array $collums = null){
        $key = array_key_first($table);
        $this->collums .= (is_array($collums)) ? ','.self::ArrayToString($collums,$key) : null;
        $this->joinLeft[] = ' LEFT JOIN '.$table[$key].' AS '.$key.' ON '.$on;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * @param array $table
     * @param string $on
     * @param array $collums
     * @return \vadc\kernel\Model\ModelSelect
     */
    public function joinInner(array $table, string $on, array $collums = null){
        $key = array_key_first($table);
        $this->collums .= (is_array($collums)) ? ','.self::ArrayToString($collums,$key) : null;
        $this->joinInner[] = ' INNER JOIN '.$table[$key].' AS '.$key.' ON '.$on;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * 
     * @param string $param
     * @return $this
     */
    public function where(string $param) {
        $this->where = ' WHERE '.$param.' ';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    public function having(string $param) {
        $this->where = ' HAVING '.$param.' ';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    public function GroupBy(string $param) {
        $this->group_by = 'GROUP BY '.$param;
        $sql = $this->sqlString();
        self::setAssembly($sql);
    }
    
    public function order_by(string $param) {
        $this->order_by = ' ORDER BY '.$param;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    public function limit(int $start, $count = null) 
    {
        if(DB::$config_db['default']['db'] == 'postgresql'){
            $offset = ' OFFSET ';
        }else{
            $offset = ',';
        }
        
        $count = (!is_null($count)) ? $offset .$count : null;
        $this->limit = ' LIMIT '.$start.$count;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    private function sqlString() {
        $joinInner = implode(' ', $this->joinInner);
        $joinLeft = implode(' ', $this->joinLeft);
        
        $sql = $this->select.$this->collums.$this->from
            .$joinInner.$joinLeft.$this->where.$this->group_by
            .$this->order_by.$this->limit;
        
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
    
    function __destruct() {
        
    }
}
