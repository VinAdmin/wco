<?php
namespace wco\kernel\Grid;

use wco\kernel\Grid\Inquiries;
use wco\kernel\Grid\Table;
use wco\kernel\Grid\TableProperties;
use wco\kernel\WCO;
use wco\db\Model\ModelSelect;
use wco\db\DB;

/**
 * Grid вспомогательная библиотека для PDO позволяющая вывести список записей из таблицы.
 * @author Olkhin Vitaliy <ovvitalik@gmail.com>
 * @version 0.0.3 beta
 * @property int $rows выводить количество строк из таблицы, по умолчанию 25 строк
 * @property string $uri Пока для контроллера
 */
class Grid extends Table{
    public int $rows = 25;

    private $count = null;
    private $render_table = null;
    protected $columns = null;
    private $Inquiries = null;
    private $getCol;
    private $getSort;
    public int $offset = 0;
    
    /**
     * @param array $options array('get'=>'','action_edit'=>'name')
     */
    function __construct($options=array()) {
        WCO::setCss([
            '/default/css/grid.css',
            '/default/font-awesome-4.7.0/css/font-awesome.css'
        ]);
        
        $this->getCol = WCO::safe_strip_tags(filter_input(INPUT_GET, 'col'));
        $this->getSort = WCO::safe_strip_tags(filter_input(INPUT_GET, 'sort'));
        if($this->getSort == 'DESC' || $this->getSort == 'ASC') {
            $this->column = $this->getCol;
            $this->sort = $this->getSort;
        }
        
        parent::__construct($options);
    }
    
    /**
     * Выборка записей из таблицы.
     * @param string $count Записей в таблице
     * @param string $model Модель формирования запроса.
     */
    public function FromTable($count, ModelSelect $model) {
        if(is_array(self::$_column)){
            foreach (self::$_column as $key => $filt){
                $search = str_replace(['+'], [''],
                    strip_tags(filter_input(INPUT_GET, $this->FixForm($key))));
                if(!empty($search)){
                    $where[] = " ". strip_tags($key) . " LIKE '%" . $search . "%'";
                }
            }
            
            if(isset($where)){
                $str_where = implode(' AND ', $where);
                $model->where($str_where);
            }
        }
        
        $this->Inquiries = new Inquiries($model);
        $this->Inquiries->offset = $this->offset;
        
        
        $this->Inquiries->setSort($this->getCol, $this->getSort);
        
        $this->count = $count;
        if(DB::$config_db['default']['db'] == 'postgresql'){
            $start = $this->paginations->Start($this->count,$this->rows);
            
            $this->render_table = $this->Inquiries->FatchTable(
                $this->rows,
                $start
            );
        }else{
            $this->render_table = $this->Inquiries->FatchTable(
                $this->paginations->Start($this->count,$this->rows),
                $this->rows
            );
        }
        
        $this->paginations->num = $this->rows;
    }
    
    /**
     * Устанавливает вывод одного указаного столбца а также алиас столбца.
     * @param string $column Запись столбца таблицы
     * @param string $aliace Альтернативное насвоние столбца.
     * @param string $width Ширина столбца.
     * @param array  $list Список
     * @return TableProperties
     */
    public function setColumn(string $column, string $aliace=null, $width = false, array $list = []) {
        if(is_null($aliace)){
            $aliace = $column;
        }
        $this->columns[] = [
            'col'   => $column,
            'name'  => $aliace,
            'width' => $width,
            'list'  => $list,
        ];
        
        return new TableProperties($column, $aliace);
    }
    
    /**
     * Генерирует таблицу, из полученых параметров.
     * @return string 
     */
    public function Render($option = array()) {
        $this->setHeader($this->columns);
        $this->setBody($this->render_table, $option);
        
        $html_table = $this->RenderTable();
        
        return $html_table;
    }
    
    public function getRows() {
        return $this->render_table;
    }
    
    public function setSort($column, $sorting) {
        $this->getCol = $column;
        $this->getSort = $sorting;
    }
}
