<?php
namespace vadc\kernel\Grid;

/**
 * Description of PDO
 *
 * @author vinamin
 */
class Inquiries extends \vadc\kernel\DB{
    protected $sql = null;
    protected $table = array();
    protected $joinInner = null;
    protected $joinLeft = null;
    protected $start = null;
    protected $collums = null;
    protected $table_db = null;
    protected $order = null;
    private $col;
    private $sort;
    private $model;
            
    function __construct(object $model){
        $this->model = $model;
    }
    
    public function FatchTable(int $start, int $rows) {
        $data = array();
        if($this->col && $this->sort){
            $this->model->order_by('`'.$this->col.'` '.$this->sort);
        }
        $this->model->limit($start, $rows);
        return $this->fetchAll($data);
    }
    
    public function setSort($col, $sort) {
        $this->col = $col;
        $this->sort = $sort;
    }
}
