<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace vadc\kernel;

use vadc\kernel\DB;

/**
 * Description of Paginations
 *
 * @author vinamin
 */
class Paginations extends ModelSqlPagination{
    function __construct() {
        $this->page = filter_input(INPUT_GET, 'page');
    }
    
    public function Paging() {
        $pervpage = null;
        $page1right = null;
        $page2right = null;
        $nextpage = null;
        $page2left = null;
        $page1left = null;
        $search = $this->uri;
        
        // Проверяем нужны ли стрелки назад
        if ($this->page != 1) $pervpage = '<a href='.$search.'page=1><<</a>
                        <a href='.$search.'page='. ($this->page - 1) .'><</a> ';
        // Проверяем нужны ли стрелки вперед
        if ($this->page != $this->total) 
            $nextpage = ' <a href='.$search.'page='. ($this->page + 1) .'>></a>
                <a href= '.$search.'page=' . $this->total. '>>></a>';

        // Находим две ближайшие станицы с обоих краев, если они есть
        if($this->page - 2 > 0) $page2left = ' <a href= '.$search.'page='. ($this->page - 2) .'>'. ($this->page - 2) .'</a> | ';
        if($this->page - 1 > 0) $page1left = '<a href= '.$search.'page='. ($this->page - 1) .'>'. ($this->page - 1) .'</a> | ';
        if($this->page + 2 <= $this->total) $page2right = ' | <a href='.$search.'page='. ($this->page + 2) .'>'. ($this->page + 2) .'</a>';
        if($this->page + 1 <= $this->total) $page1right = ' | <a href='.$search.'page='. ($this->page + 1) .'>'. ($this->page + 1) .'</a>';

        // Вывод меню
        return $pervpage.$page2left.$page1left.'<b>'. $this->page.'</b>'.$page1right.$page2right.$nextpage;
    }
    
    public function Run($params) {
        $html = '<table class="table table-sm">';
        $html .= '<thead class="thead-dark">
            <tr>
                <th></th>
                <th>
                    chat_id
                </th>
                <th>
                    Имя
                </th>
                <th>
                    First Name
                </th>
                
                <th>
                    Связь с профилем сайта Логин
                </th>
                
                <th>
                    Сообщение
                </th>
            </tr>
        </thead>';
        $count = 1;
        foreach ($this->table as $arr){
            $html .= '<tr>';
            $html .= '<td>'.$count.'</td>';
            foreach ($params as $str){
                $html .= '<td>'.$arr[$str].'</td>';
            }
            $html .= '</tr>';
            $count++;
        }
        $html .= '</table>';
        $html .= $this->Paging();
        return $html;
    }
}

class ModelSqlPagination{
    public int $num = 25;
    public $uri = null;
    protected $sql = null;
    protected $total = null;
    protected $page = null;
    protected $table = array();
    protected $joinInner = null;
    protected $joinLeft = null;
    protected $start = null;
    protected $collums = null;
    protected $table_db = null;
    protected $order = null;
    
    /**
     * FROM table_name
     * @param type $table table_name
     * @param type $collums array ['col1',['col2']]
     */
    public function from($table, $collums) {
        $this->start = $this->Count($table);
        $this->table_db = $table;
        $this->collums = $this->Callums($collums);
    }
    
    private function Callums(array $collums) {
        $col = null;
        if(!is_array($collums)){
            $col = 't1.*';
        }else{
            $str = null;
            foreach ($collums as $row){
               $str .= 't1.'.$row.',';
            }
            $col .= substr($str, 0, -1);
        }
        
        return $col;
    }
    
    public function Count(string $table) {
        $res = DB::connect()->query("SELECT COUNT(*) as con FROM `".$table."` ORDER BY `id` ASC");
	$row = $res->fetch(\PDO::FETCH_ASSOC);
        $res = null;
	$count = $row['con']; // всего записей
        
        // Находим общее число страниц
        $this->total = intval(($count - 1) / $this->num) + 1;
        $this->page = intval($this->page);
        // Если значение $page меньше единицы или отрицательно
        // переходим на первую страницу
        // А если слишком большое, то переходим на последнюю
        if(empty($this->page) or $this->page < 0) $this->page = 1;
        if($this->page > $this->total) $this->page = $this->total;
        // Вычисляем начиная к какого номера
        // следует выводить сообщения
        $start = $this->page * $this->num - $this->num;
        return $start;
    }
    
    public function joinInner(array $arr, string $on=null, $collums=array()) {
        if(is_array($arr)){
            foreach ($arr as $key => $table){
                $this->joinInner .= 'INNER JOIN '.$table . ' AS '.$key.' ON '.$on.' ';
            }
        }
        
        if(is_array($collums)){
            foreach ($collums as $as => $col){
                $this->collums .= ', '.$col;
            }
        }
        return $this->collums;
    }
    
    public function joinLeft(array $arr, string $on=null, $collums=array()) {
        if(is_array($arr)){
            foreach ($arr as $key => $table){
                $this->joinLeft .= ' LEFT JOIN '.$table . ' AS '.$key.' ON '.$on.' ';
            }
        }
        
        if(is_array($collums)){
            foreach ($collums as $as => $col){
                $this->collums .= ', '.$col;
            }
        }
        return $this->collums;
    }
    
    public function order(string $param) {
        $this->order = $param;
    }
    
    public function sqlString() {
        $this->sql .= 'SELECT '.$this->collums.' FROM '. $this->table_db. ' AS t1 ';
        $this->sql .= $this->joinInner;
        $this->sql .= $this->joinLeft;
        $this->sql .= 'ORDER BY '.$this->order.' LIMIT '. $this->start.','.$this->num;
        
        return $this->sql;
    }
    
    public function fetch() {
        $prepare = DB::connect()->prepare($this->sqlString());
        $prepare->execute();
        $this->table = $prepare->fetchAll(\PDO::FETCH_ASSOC);
        $prepare = null;
    }
}