<?php
/**
 * Описание класса: Класс генерации заголовка.
 *
 * @package    NoName
 * @subpackage Heder
 * @author     Ольхин Виталий <volkhin@texnoblog.uz>
 * @copyright  (C) 2019
 */
namespace wco\kernel;
use app\Assets\AssetsCite;
use wco\kernel\WCO;

class Heder
{
    public $title = null;
    public $description = null;
    public $keywords = null;
    public $image = false;

    static public function Link()
    {
        $assets = new AssetsCite();
        $arrayAssets = $assets->IncludeAssets();
        
        foreach($arrayAssets AS $css)
        {
            echo '<link rel="stylesheet" type="text/css"  href="'.$css['link'].'">'.PHP_EOL;
        }
    }
    
    public function Seo()
    {
        $httpDomain = WCO::$config['protocol'] . filter_input(INPUT_SERVER, 'SERVER_NAME') . filter_input(INPUT_SERVER, 'REQUEST_URI');
        $search = array("\r\n", "\r", "\n"); 
        $this->title = strip_tags(str_replace($search,'',strip_tags($this->title)));
        $this->description = strip_tags(str_replace($search,'',strip_tags($this->description)));
        $this->keywords = strip_tags(str_replace($search,'',strip_tags($this->keywords)));
        
        echo '<title>'. $this->title.'</title>'.PHP_EOL;
        echo "\t\t".'<meta name="description" content="'.$this->description.'">'.PHP_EOL;
        echo "\t\t".'<meta name="keywords" content="'.$this->keywords.'">'.PHP_EOL;
        echo "\t\t<link data-vue-meta=\"ssr\" href=\"$httpDomain\" rel=\"canonical\" data-vmid=\"canonical\">".PHP_EOL;
        if(isset(WCO::$config['site_name'])){
            echo "\t\t<meta data-vue-meta=\"ssr\" property=\"og:site_name\" "
                . "content=\"".\WCO::$config['site_name']."\" data-vmid=\"og:site_name\">".PHP_EOL;
        }
        echo "\t\t<meta data-vue-meta=\"ssr\" property=\"og:title\" "
            . "content=\"".$this->title."\" data-vmid=\"og:title\">".PHP_EOL;
        if(!empty($this->description)){
            echo "\t\t<meta data-vue-meta=\"ssr\" property=\"og:description\" "
                . "content=\"".$this->description."\" data-vmid=\"og:description\">".PHP_EOL;
            echo "\t\t<meta data-vue-meta=\"ssr\" name=\"twitter:description\" "
                . "content=\"".$this->description."\" data-vmid=\"twitter:description\">".PHP_EOL;
        }
        echo ($this->image != false) ? "\t\t<meta property=\"og:image\" content=\"$this->image\">".PHP_EOL : '';
        echo "\t\t<meta property=\"og:url\" content=\"$httpDomain\">".PHP_EOL;
        echo "\t\t".WCO::getHeder();
    }
}
?>