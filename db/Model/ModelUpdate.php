<?php
namespace wco\db\Model;

/**
 * Description of ModelUpdate
 *
 * @author vinamin
 */
class ModelUpdate extends \vadc\kernel\Assembly{
    private static $table = null;
    private static $set = null;
    private static $where = null;

    public static function setTable($table) {
        self::$table = $table;
    }
    
    /**
     * SET
     * @param array $param
     */
    public static function SET(array $param) {
        self::$set = substr(self::setParams($param), 0,-1);
    }
    
    public static function WHERE(string $param) {
        self::$where = $param;
    }
    
    /**
     * @param array $param
     * @return string `column1`=:value1,`column2`=:value2, ...
     */
    public static function setParams(array $param) {
        $str = null;
        foreach ($param as $key => $values){
            $str .= '`'.$key.'`=:'.$key.',';
        }
        return $str;
    }
    
    public static function Assembly() {
        $sql = "UPDATE `".self::$table."` SET ".self::$set." WHERE ".self::$where;
        self::setAssembly($sql);
    }
}
