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
    private $options;
    
    protected $paginations;
    protected array $list = [];

    /**
     * 
     * @param type $options
     */
    function __construct($options=array()) {
        $this->paginations = new Paginations();
        $this->page = WCO::safe_strip_tags(filter_input(INPUT_GET,'page'));
        
        if(!isset($options['edit'])){
            $this->options['edit'] = true;
        }
        
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
        $this->paginations->uri = '/'.$this->uri;
        $this->htmlHeader = "\t\t<thead class=\"thead-dark\">";
        $this->htmlHeader .= "\n\t\t\t<tr>";
        if($this->page){
            $params_url['page'] = $this->page;
        }
        if(!$col){return;}
        foreach ($col as $col_num => $arr){
            $this->list[$arr['col']] = $arr['list'];
            $params_url['col'] = $arr['col'];
            
            if($this->sort == 'DESC'){
                $params_url['sort'] ='ASC';
            }else{
                $params_url['sort'] = 'DESC';
            }
            
            $url = WCO::Url('/'.$this->uri, $params_url);
            $width = ($arr['width']) ? "width='" . $arr['width']."'" : "";
            
            $this->htmlHeader .= "\n\t\t\t\t<th class='coll'" . $width . ">\n"
                . "\t\t\t\t\t<a href=\"".$url."\">".$arr['name'].'</a>'
                . $this->FilterForm($arr['col'])
                . "\t\t\t\t</th>\n";
            $this->col[] = $arr['col'];
        }
        
        $this->htmlHeader .= "\t\t\t\t<th>".$this->Button(self::INPUT_SUBMIT, '<i class="fa fa-filter" aria-hidden="true"></i>','btn btn-success')."</th>";
        $this->htmlHeader .= "\n\t\t\t</tr>";
        $this->htmlHeader .= "\n\t\t</thead>\n\t\t";
    }
    
    /**
     * Генерирует содержимое таблицы.
     * @param array $params
     */
    public function setBody(array $params, $options) {
        $this->htmlBody = null;
        foreach ($params as $kk => $arr){
            if(!$this->col){return;}
            $this->htmlBody .= '<tr>';
            foreach ($this->col as $k => $key_col){
                $col = explode('.', $key_col);
                $additional = (isset(self::$additional_text[$key_col])) ? self::$additional_text[$key_col] : '';
                $end = end($col);
                $tdText = (isset($this->list[$key_col][$arr[$end]])) ? $this->list[$key_col][$arr[$end]] : $arr[$end];
                
                $this->htmlBody .= '<td '.self::getValign().' class="td_grid">'  . $additional . $tdText . '</td>';
            }
            $this->htmlBody .= '<td valign="top" width="100">'.$this->Link(array_shift($arr), $options).'</td>';
            $this->htmlBody .= '</tr>';
        }
    }
    
    private function Link($id, $options = array()) {
        if($this->options['edit'] == true){
            if($this->action_edit == false){
                $link = '';
            }else{
                $link = '<a href="'.WCO::Url('/?option='.$this->uri.'&action='.$this->action_edit, [$this->id =>$id]).'"'
                . ' title="Редактировать"'
                . ' style="margin-right:5px;"'
                . ' class="btn btn-primary">'
                . '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>'
                . '</a>';
            }
            $url_delete = '';
            
            if(isset($options['delete'])){ $url_delete = '&'.http_build_query($options['delete']); }
            if($this->action_delete == true){
                $link .= '<a onClick="return confirm(\'Вы подтверждаете удаление?\');"'
                    . ' href="'.WCO::Url('/?option='.$this->uri.'&action='.$this->action_delete, [$this->id => $id]).$url_delete.'" title="Удалить" '
                    . 'style="margin-right:5px;"'
                    . ' class="btn btn-danger">'
                    . '<i class="fa fa-trash" aria-hidden="true"></i></a>';
            }
        }else{ $link = null; }
        
        return  $link;
    }
    
    public function RenderTable() {
        $html = $this->FormStart('grid');
        $html .= "<table class=\"\">\n\t\t";
        $html .= $this->htmlHeader;
        $html .= $this->htmlBody;
        $html .= '</table>'. $this->FormEnd();
        return $html.$this->paginations->Paging();
    }
    
    public function getPaging() {
        $this->paginations->uri = $this->uri;
        return $this->paginations->Paging();
    }
}
