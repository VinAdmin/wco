<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace wco\kernel\Grid;

use wco\kernel\Grid\TableProperties;
use wco\kernel\Grid\Paginations;
use wco\kernel\WCO;

/**
 * Description of Table
 *
 * @author vinamin
 */
class Table extends TableProperties{
    const VALIGN_TOP = 'top';
    const VALIGN_BOTTOM = 'bottom';
    const VALIGN_MIDDLE = 'middle';
    
    public $table = null;
    public $uri = null;
    public $id = 'id';
    public $action_edit = 'edit';
    public $action_delete = 'delete';
    public $column;
    public $sort;
    
    private $col = null;
    private $htmlHeader = null;
    private $htmlBody = null;
    private $page;
    
    protected $paginations;
    
    function __construct($options=array()) {
        $this->paginations = new Paginations();
        $this->page = strip_tags(filter_input(INPUT_GET,'page'));
        
        if(isset($options['get'])){
            $this->id = $options['get'];
        }
        if(isset($options['action_edit'])){
            $this->action_edit = $options['action_edit'];
        }
        if(isset($options['action_delete'])){
            $this->action_delete = $options['action_delete'];
        }
    }
    
    /**
     * Генерирует заголовок таблицы
     * @param type $col
     */
    public function setHeader($col) {
        $this->paginations->uri = $this->uri;
        
        $this->htmlHeader = '<thead class="thead-dark">';
        $this->htmlHeader .= '<tr>';
        if($this->page){
            $params_url['page'] = $this->page;
        }
        foreach ($col as $arr){
            $params_url['col'] = $arr['col'];
            if($this->sort == 'DESC'){
                $params_url['sort'] ='ASC';
            }else{
                $params_url['sort'] = 'DESC';
            }
            $url = WCO::Url(''.$this->uri, $params_url);
            
            $this->htmlHeader .= '<th><a href="'.$url.'">'.$arr['name'].'</a></th>';
            $this->col[] = $arr['col'];
        }
        $this->htmlHeader .= '<th>--</th>';
        $this->htmlHeader .= '</tr>';
        $this->htmlHeader .= '</thead>';
    }
    
    /**
     * Генерирует содержимое таблицы.
     * @param array $params
     */
    public function setBody(array $params) {
        $this->htmlBody = null;
        foreach ($params as $arr){
            $this->htmlBody .= '<tr>';
            foreach ($this->col as $key_col){
                $this->htmlBody .= '<td '.self::getValign().' class="td_grid">'.$arr[$key_col].'</td>';
            }
            $this->htmlBody .= '<td valign="top" width="100">'.$this->Link(array_shift($arr)).'</td>';
            $this->htmlBody .= '</tr>';
        }
    }
    
    private function Link($id) {
        $link = '<a href="'.WCO::Url('/?option='.$this->uri.'&action='.$this->action_edit, [$this->id =>$id]).'"'
            . ' title="Редактировать"'
            . ' style="margin-right:5px;"'
            . ' class="btn btn-primary">'
            . '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>'
            . '</a>';
        $link .= '<a onClick="return confirm(\'Вы подтверждаете удаление?\');"'
            . ' href="'.WCO::Url('/?option='.$this->uri.'&action='.$this->action_delete, [$this->id => $id]).'" title="Удалить" '
            . 'style="margin-right:5px;"'
            . ' class="btn btn-danger">'
            . '<i class="fa fa-trash" aria-hidden="true"></i></a>';
        return  $link;
    }
    
    public function RenderTable() {
        $html = '<table class="">';
        $html .= $this->htmlHeader;
        $html .= $this->htmlBody;
        $html .= '</table>';
        return $html.$this->paginations->Paging();
    }
}
