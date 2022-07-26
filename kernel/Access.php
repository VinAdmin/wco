<?php
namespace wco\kernel;

class Access {
    static $user_id = false;
    static $login = false;

    public function __construct() {
        $session_user_id = filter_input(INPUT_SESSION, 'user_id');
        
        if($session_user_id){
            self::$user_id = $session_user_id;
            self::$login = filter_input(INPUT_SESSION, 'login');
        }
    }
    
    /**
     * Возващает true если пользователь авторизирован
     * 
     * @return boolean
     */
    static public function Authorized() {
        if(self::$user_id){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Создании сессии.
     * 
     * @param type $user_id
     * @param type $login
     */
    static public function CreateSessionUser($user_id, $login) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['login'] = $login;
        self::$user_id = $user_id;
        self::$login = $login;
    }
    
    /**
     * Очистка сессии для выхода из профиля.
     */
    static public function LogOut() {
        session_destroy();
    }
}
