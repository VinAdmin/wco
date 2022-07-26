<?php
namespace wco\kernel\Grid;

use wco\kernel\Grid\Inquiries;
use wco\kernel\Grid\Table;
use wco\kernel\Grid\TableProperties;
use wco\kernel\WCO;

/**
 * Grid вспомогательная библиотека для PDO позволяющая вывести список записей из таблицы.
 * @author Olkhin Vitaliy <ovvitalik@gmail.com>
 * @version 0.0.3 beta
 * @property int $rows выводить количество строк из таблицы, по умолчанию 25 строк
 * @property string $uri Пока для контроллера
 */
class Grid extends Table{
    public $rows = 25;
    
    private $count = null;
    private $render_table = null;
    private $columns = null;
    private $Inquiries = null;
    private $getCol;
    private $getSort;
    
    /**
     * @param array $options array('get'=>'','action_edit'=>'name')
     */
    function __construct($options=array()) {
        WCO::setCss([
            '/default/css/grid.css',
            '/default/font-awesome-4.7.0/css/font-awesome.css'
        ]);
        
        $this->getCol = strip_tags(filter_input(INPUT_GET, 'col'));
        $this->getSort = strip_tags(filter_input(INPUT_GET, 'sort'));
        $this->column = $this->getCol;
        $this->sort = $this->getSort;
        parent::__construct($options);
    }
    
    /**
     * Выборка записей из таблицы.
     * @param string $count Записей в таблице
     * @param string $model Модель формирования запроса.
     */
    public function FromTable($count,$model) {
        $this->Inquiries = new Inquiries($model);
        $this->Inquiries->setSort($this->getCol, $this->getSort);
        $this->count = $count;
        $this->render_table = $this->Inquiries->FatchTable(
            $this->paginations->Start($this->count,$this->rows),
            $this->rows
        );
        $this->paginations->num = $this->rows;
    }
    
    /**
     * Устанавливает вывод одного указаного столбца а также алиас столбца.
     * @param string $column Запись столбца таблицы
     * @param string $aliace Альтернативное насвоние столбца.
     * @return TableProperties
     */
    public function setColumn(string $column, string $aliace=null) {
        if(is_null($aliace)){
            $aliace = $column;
        }
        $this->columns[] = ['col'=> $column, 'name' => $aliace];
        
        return new TableProperties();
    }
    
    /**
     * Генерирует таблицу, из полученых параметров.
     * @return string 
     */
    public function Render() {
        $this->setHeader($this->columns);
        $this->setBody($this->render_table);
        
        $html_table = $this->RenderTable();
        
        return $html_table;
    }
}
