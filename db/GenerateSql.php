<?php
namespace wco\db;

use wco\db\Model\ModelSelect;
use wco\db\Model\ModelInsert;
use wco\db\Model\ModelDelete;
use wco\db\Model\ModelUpdate;

/**
 * Абстрактный класс для генерации SQL-запросов и работы с PDO
 * 
 * Предоставляет базовую реализацию CRUD-операций (SELECT, INSERT, UPDATE, DELETE)
 * с использованием паттерна Repository. Класс инкапсулирует логику выполнения
 * запросов и обработку исключений PDO.
 * 
 * Класс наследуется от Assembly для сборки SQL-запросов и реализует interfaceDB.
 * 
 * @package wco\db
 * @author VinAdmin
 * @abstract
 * @see Assembly
 * @see interfaceDB
 * 
 * @example Пример использования:
 * ```
 * // Создание класса repository
 * class UserRepository extends GenerateSql {
 *     public function init() {
 *         return 'users';
 *     }
 * }
 * 
 * // Выборка данных
 * $users = (new UserRepository())->select('*')->fetchAll();
 * 
 * // Вставка данных
 * $user = new UserRepository();
 * $user->insert(['name' => 'John', 'email' => 'john@example.com']);
 * ```
 */
abstract class GenerateSql extends Assembly implements interfaceDB{
    /**
     * Экземпляр модели для построения SELECT-запросов
     * @var ModelSelect|null
     */
    private $modelSelect = null;

    /**
     * Строка для преобразования объекта в строку
     * @var string|null
     */
    protected $toString = null;

    /**
     * Последний вставленный ID (для SQLite и MySQL)
     * @var int|null
     */
    static public $lastId = null;
            
    /**
     * Конструктор класса
     * 
     * Инициализирует модель Select с именем таблицы из метода init()
     */
    function __construct() {
        $this->modelSelect = new ModelSelect($this->init());
    }
    
    /**
     * Инициализация имени таблицы
     * 
     * Должен быть переопределен в дочерних классах для указания имени таблицы.
     * 
     * @return string|null Имя таблицы или null
     */
    public function init() {
        return null;
    }
        
    /**
     * Формирует SELECT-запрос
     * 
     * Устанавливает выбираемые колонки для SQL-запроса.
     * 
     * @param string|null $param Колонки для выборки (например, '*', 'id, name')
     * @return ModelSelect Экземпляр модели для построения запроса
     * 
     * @example
     * ```
     * $query = $repo->select('id, name, email');
     * $query = $repo->select('*');
     * ```
     */
    public function select($param = null) {
        $this->modelSelect->select($param);
        return $this->modelSelect;
    }
    
    /**
     * Устанавливает таблицу для SELECT-запроса
     * 
     * Использует имя таблицы из метода init()
     * 
     * @return ModelSelect Экземпляр модели для построения запроса
     */
    public function from() {
        $this->modelSelect->from($this->init());
        return $this->modelSelect;
    }
    
    /**
     * Получение одной записи из результата запроса
     * 
     * Выполняет подготовленный запрос и возвращает первую запись
     * в виде ассоциативного массива.
     * 
     * @param array $params Параметры для привязки к запросу ['name' => 'value']
     * @return array|false Ассоциативный массив с данными или false если нет результатов
     * 
     * @throws \PDOException При ошибке выполнения запроса
     * 
     * @example
     * ```
     * $user = $repo->select('*')->where('id = :id')->fetch([':id' => 1]);
     * ```
     */
    public function fetch(array $params=array()) {
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($params);
            $return = $prepare->fetch(\PDO::FETCH_ASSOC);
            $prepare = null;
            
            return $return;
        } catch (\PDOException $ex) {
            echo "<span style=\"color: blue\">".self::getAssembly().'</span>';
            echo $ex;
            exit();
        }
    }
    
    /**
     * Получение всех записей из результата запроса
     * 
     * Выполняет подготовленный запрос и возвращает все записи
     * в виде ассоциативного массива.
     * 
     * @param array $params Параметры для привязки к запросу ['name' => 'value']
     * @param int $pdo Режим выборки PDO (по умолчанию FETCH_ASSOC)
     * @return array Массив записей
     * 
     * @throws \PDOException При ошибке выполнения запроса
     * 
     * @example
     * ```
     * $users = $repo->select('*')->fetchAll();
     * $users = $repo->select('*')->where('active = :active')->fetchAll([':active' => 1]);
     * ```
     */
    public function fetchAll(array $params=array(), $pdo = \PDO::FETCH_ASSOC) {
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($params);
            $return = $prepare->fetchAll($pdo);
            $prepare = null;
            
            return $return;
        } catch (\PDOException $ex) {
            echo '<div style="color: blue;">'.self::getAssembly().'</div>';
            echo $ex;
            exit();
        }
    }
    
    /**
     * Подсчет количества записей
     * 
     * Выполняет запрос COUNT и возвращает количество записей.
     * 
     * @param array $params Параметры для привязки к запросу ['name' => 'value']
     * @return int Количество записей
     * 
     * @throws \PDOException При ошибке выполнения запроса
     * 
     * @example
     * ```
     * $count = $repo->select('COUNT(*) as count')->Count();
     * ```
     */
    public function Count(array $params=array()) {
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($params);
            $return = $prepare->fetchColumn();
            $prepare = null;
            
            return $return;
        } catch (\PDOException $ex) {
            echo '<div style="color: blue;">'.self::getAssembly().'</div>';
            echo $ex;
            exit();
        }
    }
    
    /**
     * Обновление записей в таблице
     * 
     * Формирует и выполняет UPDATE-запрос для обновления данных.
     * 
     * @param array $columns Массив колонок для обновления ['column' => 'value']
     * @param string $where УсловиеWHERE (например, 'id = :id')
     * @param string|null $from Имя таблицы (по умолчанию из init())
     * @return bool Результат выполнения (true - успех, false - ошибка)
     * 
     * @throws Exception При ошибке выполнения запроса
     * 
     * @example
     * ```
     * $repo->Update(
     *     ['name' => 'New Name', 'email' => 'new@email.com'],
     *     'id = :id',
     *     'users'
     * );
     * ```
     */
    public function Update(array $columns, string $where, string $from=null) {
        $modelUpdate = new ModelUpdate();
        if(empty($from)){
            $from = $this->init();
        }
        $modelUpdate::setTable($from);
        $modelUpdate::SET($columns);
        $modelUpdate::WHERE($where);
        $modelUpdate::Assembly();
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($columns);
            $prepare = null;

            return true;
        } catch (Exception $ex) {
            echo $ex;
            return false;
        }
        
    }
    
    /**
     * Вставка новой записи в таблицу
     * 
     * Формирует и выполняет INSERT-запрос для добавления новой записи.
     * После вставки автоматически определяет последний вставленный ID
     * в зависимости от типа БД (SQLite, MySQL).
     * 
     * @param array $param Ассоциативный массив данных для вставки ['column' => 'value']
     * @param string|null $table Имя таблицы (по умолчанию из init())
     * @param int $pdo Режим PDO (не используется)
     * @return bool|object Результат выполнения
     * 
     * @throws \PDOException При ошибке выполнения запроса
     * 
     * @example
     * ```
     * $repo->insert([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     *     'created_at' => date('Y-m-d H:i:s')
     * ]);
     * 
     * // Получение последнего ID
     * $lastId = GenerateSql::$lastId;
     * ```
     */
    public function insert(array $param, $table=null, $pdo = 0) {
        $connect = DB::connect();
        $insert = new ModelInsert();
        $table = empty($table) ? $this->init() : $table;
        $insert->Insert($table, $param);
        
        try{
            $prepare = $connect->prepare(self::getAssembly());
            $prepare->execute($insert->par);
            if(isset(DB::$config_db[DB::$connect_type_db])){
                if(DB::$config_db[DB::$connect_type_db]['db'] == 'sqlite'){
                    $this->LastId($table);
                }elseif(DB::$config_db[DB::$connect_type_db]['db'] == 'mysql'){
                    $stmt = $connect->query("SELECT LAST_INSERT_ID()");
                    self::$lastId = $stmt->fetchColumn();
                    $result = $prepare;
                }else {
                    $result = $prepare->fetch();
                }
            }
            
            $prepare = true;
            return $result;
        } catch (\PDOException $ex) {
            echo  $ex;
            return false;
        }
    }
    
    /**
     * Получение последнего вставленного ID для SQLite
     * 
     * Выполняет запрос к системной таблице sqlite_sequence
     * для получения последнего автоинкрементного значения.
     * 
     * @param string $table Имя таблицы
     * @return void
     * 
     * @internal Используется только для SQLite
     */
    private function LastId(string $table) {
        $stmt = DB::connect()->query("select seq from sqlite_sequence where name='".$table."'");
        self::$lastId = $stmt->fetchColumn();
        $stmt = null;
    }
    
    /**
     * Удаление записей из таблицы
     * 
     * Формирует DELETE-запрос для удаления записей по условию.
     * Возвращает объект подготовленного запроса для последующего выполнения.
     * 
     * @param string|null $where Условие WHERE (например, 'id = :id')
     * @param string|null $from Имя таблицы (по умолчанию из init())
     * @return \PDOStatement|null Объект подготовленного запроса
     * 
     * @throws Exception При ошибке выполнения запроса
     * 
     * @example
     * ```
     * $stmt = $repo->delete('id = :id');
     * $stmt->execute([':id' => 1]);
     * ```
     */
    public function delete($where = null, string $from = null) {
        $modelDelete = new ModelDelete();
        if(empty($from)){
            $from = $this->init();
        }
        $modelDelete->setTable($where, $from);
        
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $return = $prepare;
            $prepare = null;
            return $return;
        } catch (Exception $ex) {
            echo  $ex;
        }
    }
    
    /**
     * Вставка или обновление записи (upsert)
     * 
     * Выполняет INSERT с обновлением при конфликте по ключу.
     * Аналогично INSERT OR REPLACE в SQLite или ON DUPLICATE KEY UPDATE в MySQL.
     * 
     * @param array $param Ассоциативный массив данных для вставки ['column' => 'value']
     * @param string $key Имя колонки для определения конфликта
     * @param string|null $table Имя таблицы (по умолчанию из init())
     * @return bool Результат выполнения (true - успех, false - ошибка)
     * 
     * @throws \PDOException При ошибке выполнения запроса
     * 
     * @example
     * ```
     * $repo->InsertToUpdate(
     *     ['email' => 'user@example.com', 'name' => 'John'],
     *     'email'
     * );
     * ```
     */
    public function InsertToUpdate(array $param,string $key, $table=null) {
        $insert = new ModelInsert();
        $table = empty($table) ? $this->init() : $table;
        $insert->InsertToUpdate($table, $param, $key);
        
        try{
            $prepare = DB::connect()->prepare(self::getAssembly());
            $prepare->execute($insert->par);
            $prepare = null;
            
            if(isset(DB::$config_db[DB::$connect_type_db])){
                if(DB::$config_db[DB::$connect_type_db] == 'sqlite'){
                    $this->LastId($table);
                }
            }
            
            return true;
        } catch (\PDOException $ex) {
            echo  $ex;
            return false;
        }
    }
}

/**
 * Интерфейс для классов, работающих с базой данных
 * 
 * Определяет полный контракт CRUD-операций для классов repository,
 * которые наследуются от GenerateSql.
 * 
 * @package wco\db
 */
interface interfaceDB{
    /**
     * Установка таблицы для SELECT-запроса
     * @return ModelSelect Экземпляр модели
     */
    public function from();

    /**
     * Формирует SELECT-запрос
     * @param string|null $param Колонки для выборки
     * @return ModelSelect Экземпляр модели для построения запроса
     */
    public function select($param = null);

    /**
     * Получение одной записи из результата запроса
     * @param array $params Параметры для привязки к запросу
     * @return array|false Ассоциативный массив или false
     */
    public function fetch(array $params = array());

    /**
     * Получение всех записей из результата запроса
     * @param array $params Параметры для привязки к запросу
     * @param int $pdo Режим выборки PDO
     * @return array Массив записей
     */
    public function fetchAll(array $params = array(), $pdo = \PDO::FETCH_ASSOC);

    /**
     * Подсчет количества записей
     * @param array $params Параметры для привязки к запросу
     * @return int Количество записей
     */
    public function Count(array $params = array());

    /**
     * Обновление записей в таблице
     * @param array $columns Массив колонок для обновления
     * @param string $where Условие WHERE
     * @param string|null $from Имя таблицы
     * @return bool Результат выполнения
     */
    public function Update(array $columns, string $where, string $from = null);

    /**
     * Вставка новой записи в таблицу
     * @param array $param Ассоциативный массив данных для вставки
     * @param string|null $table Имя таблицы
     * @param int $pdo Режим PDO
     * @return bool|object Результат выполнения
     */
    public function insert(array $param, $table = null, $pdo = 0);

    /**
     * Удаление записей из таблицы
     * @param string|null $where Условие WHERE
     * @param string|null $from Имя таблицы
     * @return \PDOStatement|null Объект подготовленного запроса
     */
    public function delete($where = null, string $from = null);

    /**
     * Вставка или обновление записи (upsert)
     * @param array $param Ассоциативный массив данных
     * @param string $key Имя колонки для конфликта
     * @param string|null $table Имя таблицы
     * @return bool Результат выполнения
     */
    public function InsertToUpdate(array $param, string $key, $table = null);
}
