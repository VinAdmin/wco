<?php

namespace wco\kernel\Grid;

use wco\kernel\WCO;

/**
 * Пагинация.
 */
class Paginations {
    public $url = null;
    protected $page = null;
    protected $total = null;
    public $uri = null;
    private $pervpage = null;
    public int $num = 0;
            
    function __construct() {
        $this->page = filter_input(INPUT_GET, 'page');
    }
    
    public function Start($count,$rows) {
        // Находим общее число страниц
        $this->total = intval(($count - 1) / $rows) + 1;
        $this->page = intval($this->page);
        // Если значение $page меньше единицы или отрицательно
        // переходим на первую страницу
        // А если слишком большое, то переходим на последнюю
        if(empty($this->page) or $this->page < 0) $this->page = 1;
        if($this->page > $this->total) $this->page = $this->total;
        // Вычисляем начиная к какого номера
        // следует выводить сообщения
        $start = $this->page * $rows - $rows;
        
        return $start;
    }
    
    public function Paging() {
        $page1right = null;
        $page2right = null;
        $nextpage = null;
        $page2left = null;
        $page1left = null;
        $getCol = WCO::safe_strip_tags(filter_input(INPUT_GET, 'col'));
        $getSort = WCO::safe_strip_tags(filter_input(INPUT_GET, 'sort'));
        
        $filtered = array_filter($_GET);
        $data = [];
        foreach ($filtered as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        
        if($getSort == 'DESC' || $getSort == 'ASC') {
            if($getCol && $getSort){
                $data['col'] = $getCol;
                $data['sort'] = $getSort;
            }
        }
        
        // Проверяем нужны ли стрелки назад
        if ($this->page != 1){
            $data['page']=1;
            $this->pervpage = '<li class="page-item">'
                    . '<a href="'.WCO::Url($this->uri, $data).'" class="page-link"><<</a></li>'
                    . '<li class="page-item">';
            $data['page']= $this->page - 1;
            $this->pervpage .='<a href="'.WCO::Url($this->uri, $data).'" class="page-link"><</a></li>';
        }
        // Проверяем нужны ли стрелки вперед
        if ($this->page != $this->total){
            $data['page']=$this->page + 1;
            $nextpage = '<li class="page-item">'
                    . '<a href="'.WCO::Url($this->uri, $data).'" class="page-link">></a></li>'
                    . '<li class="page-item">';
            $data['page'] = $this->total;
            $nextpage .= '<a href="'.WCO::Url($this->uri, $data).'" class="page-link">>></a></li>';
        }

        // Находим две ближайшие станицы с обоих краев, если они есть
        if($this->page - 2 > 0){
            $data['page'] = $this->page - 2;
            $page2left = '<li class="page-item">'
                    . '<a href="'.WCO::Url($this->uri, $data).'" class="page-link">'. ($this->page - 2) .'</a></li>';
        }
        if($this->page - 1 > 0){
            $data['page'] = $this->page - 1;
            $page1left = '<li class="page-item">'
                    . '<a href="'.WCO::Url($this->uri, $data).'" class="page-link">'. ($this->page - 1) .'</a></li>';
        }
        if($this->page + 2 <= $this->total){
            $data['page'] = $this->page + 2;
            $page2right = '<li class="page-item">'
                    . '<a href="'.WCO::Url($this->uri, $data).'" class="page-link">'. ($this->page + 2) .'</a></li>';
        }
        if($this->page + 1 <= $this->total){
            $data['page'] = $this->page + 1;
            $page1right = '<li class="page-item">'
                . '<a href="'.WCO::Url($this->uri, $data).'" class="page-link">'. ($this->page + 1) .'</a></li>';
        }
        
        $html = '<nav aria-label="" style="margin-top:5px;">';
        $html .= '<ul class="pagination">';
        $html .= $this->pervpage.$page2left.$page1left.
                '<li class="page-item">'
                . '<b class="page-link">'. $this->page.'</b></li>'.$page1right.$page2right.$nextpage;
        $html .= '</ul>';
        $html .= '</nav>';
        // Вывод меню
        return $html;
    }
}
