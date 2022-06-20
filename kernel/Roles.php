<?php
/**
 * Описание класса: Класс Roles управления ролями доступа.
 *
 * @package    Access
 * @subpackage Route
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2016-2019
 */
namespace vadc\kernel;

use vadc\kernel\DB;

class Roles extends DB
{
    public function __construct() {
        parent::__construct();
    }
    
    static function setAcces($roleName)
    {
        if(!isset($_SESSION['user']['id'])){
            return 0;
        }
        
        $cache_id = 'setAcces'.$_SESSION['user']['id'].$roleName;
        
        if(!\vadc::$_cache->has($cache_id)){
            $stmt = DB::connect()->query("SELECT r.id FROM roles r
            INNER JOIN access a ON a.role_id = r.id AND a.user_id = " .$_SESSION['user']['id']. "
            INNER JOIN users u ON u.id = a.user_id
            WHERE r.name = '" . $roleName . "'");
            $result = $stmt->fetch();
            $stmt = null;
            
            \vadc::$_cache->set($cache_id, $result, 300);// 5 minutes
        }else{
            $result = \vadc::$_cache->get($cache_id);
        }
        
        if(!empty($result['id'])){
            return 1;
        }
        else{
            return 0;
        }
    }
}
?>