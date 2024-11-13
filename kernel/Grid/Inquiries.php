<?php
namespace wco\kernel\Grid;

use wco\kernel\Grid\Filter;

/**
 * Description of PDO
 *
 * @author vinamin
 */
class Inquiries extends \wco\db\DB{
    protected $sql = null;
    protected $table = array();
    protected $joinInner = null;
    protected $joinLeft = null;
    protected $start = null;
    protected $collums = null;
    protected $table_db = null;
    protected $order = null;
    public $offset = null;
    private $col;
    private $sort;
    private $model;
            
    function __construct($model){
        $this->model = $model;
    }
    
    public function FatchTable(int $start, int $rows, $data = array()) {
        //var_dump($start);
        if($this->col && $this->sort){
            $this->model->order_by(''.$this->col.' '.$this->sort);
        }
        
        $offset = $this->offset;
        if($this->offset == true){
            $offset = 'offset';
        }
        if($start >= $rows){
            $rows = $rows;
        }else{
            $rows=null;
        }
        
        $this->model->limit($start, $rows, $offset);
        return $this->fetchAll($data);
    }
    
    public function setSort($col, $sort) {
        $this->col = $col;
        $this->sort = $sort;
    }
}
