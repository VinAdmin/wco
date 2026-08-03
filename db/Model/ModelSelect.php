<?php
namespace wco\db\Model;

use wco\db\Assembly;
use wco\db\DB;

/**
 * Класс ModelSelect строит SQL-запрос SELECT по частям и передаёт
 * собранную строку в Assembly для дальнейшего выполнения.
 *
 * Используется как строитель (builder) выборки данных из БД:
 * позволяет задать колонки, таблицы (FROM/JOIN), условия (WHERE/HAVING),
 * группировку, сортировку и ограничение количества записей.
 *
 * @author Olkhin Vitaliy <ovvitalik@gmail.com>
 * @copyright (c) 2022 - 2026, Olkhin Vitaliy
 */
class ModelSelect extends Assembly{
    private $table = null;
    private $columns = null;
    private $joinLeft = [];
    private $joinInner = [];
    private $where = null;
    private $group_by = null;
    private $select = 'SELECT t1.*';
    public $from = null;
    public $order_by = null;
    public $limit = null;
    
    /**
     * Конструктор задаёт имя основной таблицы выборки.
     *
     * @param string|null $table Имя таблицы (может быть задано позже через from()).
     */
    function __construct(?string $table = null) {
        $this->table = $table;
    }
    
    /**
     * Задаёт список колонок для SELECT и собирает SQL.
     *
     * @param string|null $param Колонки выборки (например, 't1.id, t1.name').
     *                           Если null — остаётся стандартное 'SELECT t1.*'.
     * @return void
     */
    public function select($param = null) {
        $this->select = (!is_null($param)) ? 'SELECT '.$param : $this->select;
        $sql = $this->sqlString();
        self::setAssembly($sql);
    }
    
    /**
     * Задаёт основную таблицу выборки (FROM ... AS t1).
     *
     * @param string|null $table Имя таблицы. Если null — берётся таблица из конструктора.
     * @return ModelSelect
     */
    public function from(?string $table = null): self {
        $table = (!is_null($table)) ? $table : $this->table;
        $this->from = ' FROM '.$table.' AS t1';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * Добавляет LEFT JOIN к запросу и при необходимости колонки присоединённой таблицы.
     *
     * @param array $table Ассоциативный массив вида [псевдоним => имя_таблицы].
     * @param string $on Условие соединения (ON ...).
     * @param array|null $columns Список колонок присоединённой таблицы для выборки.
     * @return ModelSelect
     */
    public function joinLeft(array $table, string $on, ?array $columns = null): self{
        $key = array_key_first($table);
        $this->$columns .= (is_array($columns)) ? ','.self::ArrayToString($columns, $key) : null;
        $this->joinLeft[] = ' LEFT JOIN '.$table[$key].' AS '.$key.' ON '.$on;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * Добавляет INNER JOIN к запросу и при необходимости колонки присоединённой таблицы.
     *
     * @param array $table Ассоциативный массив вида [псевдоним => имя_таблицы].
     * @param string $on Условие соединения (ON ...).
     * @param array|null $columns Список колонок присоединённой таблицы для выборки.
     * @return ModelSelect
     */
    public function joinInner(array $table, string $on, ?array $columns = null): self{
        $key = array_key_first($table);
        $this->columns .= (is_array($columns)) ? ',' . self::ArrayToString($columns,$key) : null;
        $this->joinInner[] = ' INNER JOIN '.$table[$key].' AS '.$key.' ON '.$on;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * Добавляет условие WHERE в запрос.
     *
     * @param string $param Условие фильтрации (например, 't1.id = 5').
     * @return $this
     */
    public function where(string $param) {
        $this->where = ' WHERE '.$param.' ';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * Добавляет условие HAVING в запрос.
     *
     * @param string $param Условие фильтрации по агрегированным значениям.
     * @return $this
     */
    public function having(string $param) {
        $this->where = ' HAVING '.$param.' ';
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * Добавляет группировку GROUP BY в запрос.
     *
     * @param string $param Колонки для группировки (например, 't1.category_id').
     * @return void
     */
    public function GroupBy(string $param) {
        $this->group_by = 'GROUP BY '.$param;
        $sql = $this->sqlString();
        self::setAssembly($sql);
    }
    
    /**
     * Добавляет сортировку ORDER BY в запрос.
     *
     * @param string $param Колонки и направление сортировки (например, 't1.id DESC').
     * @return $this
     */
    public function order_by(string $param) {
        $this->order_by = ' ORDER BY '.$param;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * Добавляет ограничение LIMIT (с учётом синтаксиса выбранной СУБД).
     *
     * @param int $start Количество записей (LIMIT) или OFFSET для postgresql.
     * @param int|null $count Количество записей (для MySQL — через запятую, для postgresql — OFFSET).
     * @return $this
     */
    public function limit(int $start, $count = null) 
    {
        if(DB::$config_db['default']['db'] == 'postgresql'){
            $offset = ' OFFSET ';
        }else{
            $offset = ',';
        }
        
        $count = (!is_null($count)) ? $offset .$count : null;
        $this->limit = ' LIMIT '.$start.$count;
        $sql = $this->sqlString();
        self::setAssembly($sql);
        
        return $this;
    }
    
    /**
     * Собирает полную SQL-строку из накопленных частей запроса.
     *
     * @return string Собранный SQL-запрос.
     */
    private function sqlString() {
        $joinInner = implode(' ', $this->joinInner);
        $joinLeft = implode(' ', $this->joinLeft);
        
        $sql = $this->select.$this->columns.$this->from
            .$joinInner.$joinLeft.$this->where.$this->group_by
            .$this->order_by.$this->limit;
        
        return $sql;
    }
    
    /**
     * Преобразует список колонок в строку для SELECT.
     *
     * Поддерживает псевдонимы (ключ => значение) и колонки с точкой
     * (например, 't1.id'). Последняя запятая в строке удаляется.
     *
     * @param array $columns Список колонок [псевдоним => колонка].
     * @return string|null Строка колонок или null, если массив пуст.
     */
    public static function ArrayToString(array $columns) {
        $str = null;
        if(is_array($columns)){
            foreach ($columns as $key=>$col){
                if(substr_count($col,'.')){
                    $arr_col = explode('.', $col);
                    $str_col = ''.$arr_col[0].'.'.$arr_col[1].'';
                }else{
                    $str_col = $col;
                }
                
                if(is_string($key)){
                    $str .= ''.$str_col.' AS '.$key.',';
                }else{
                    $str .= $str_col.',';
                }
            }

            return (substr($str, 0, -1));
        }
        return null;
    }
    
    /**
     * Деструктор (без действий).
     */
    function __destruct() {
        
    }
}
