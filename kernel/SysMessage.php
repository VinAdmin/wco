<?php
/**
 * Описание класса: Системные сообщения.
 *
 * @package    SysMessage
 * @subpackage SysMessage
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2016-2021
 */
namespace vadc\kernel;

class SysMessage
{
    static $msg;
    const PRIMARY = 'primary';
    const DANGER = 'danger';

    /**
     * @param type $msg
     * @param string $type primary, danger
     */
    public function setMessage($msg, string $type=null)
    {
        switch ($type){
            case 'primary':
                $msg = '<div class="alert alert-primary" role="alert">'.$msg.'</div>';
                break;
            case 'danger':
                $msg = '<div class="alert alert-danger" role="alert">'.$msg.'</div>';
            break;
        }
        
        self::$msg = $msg;
    }

    static function getMessage()
    {
        return self::$msg;
    }
}
?>