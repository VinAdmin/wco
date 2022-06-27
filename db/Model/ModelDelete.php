<?php
namespace wco\db\Model;

/**
 * Description of ModelDelete
 *
 * @author vinamin
 */
class ModelDelete extends wco\db\Assembly{
    public function setTable(string $where = null, string $table) {
        $sql = 'DELETE FROM '.$table.' ';
        if(!empty($where)){
            $sql .= 'WHERE '.$where;
        }
        self::setAssembly($sql);
    }
}
