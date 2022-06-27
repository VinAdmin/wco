<?php
namespace vadc\kernel\Model;

/**
 * Description of ModelDelete
 *
 * @author vinamin
 */
class ModelDelete extends \vadc\kernel\Assembly{
    public function setTable(string $where = null, string $table) {
        $sql = 'DELETE FROM '.$table.' ';
        if(!empty($where)){
            $sql .= 'WHERE '.$where;
        }
        self::setAssembly($sql);
    }
}
