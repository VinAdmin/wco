<?php
/**
 * Описание класса: Класс генерации заголовка head.
 *
 * @package    kernel
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
    public $siteName = false;

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
        
        $head = $this->getTitle();
        $head .= "\t\t<link data-vue-meta=\"ssr\" href=\"$httpDomain\" rel=\"canonical\" data-vmid=\"canonical\">".PHP_EOL;
        if(isset(WCO::$config['site_name'])){
            $head .= $this->meta("application-name", WCO::$config['site_name']);
            $head .= $this->metaSsr('og:site_name', WCO::$config['site_name']);
        }
        $head .= $this->metaSsr('og:title', $this->title, );
        if(!empty($this->description)){
            $head .= $this->metaSsr('og:description', $this->description);
            $head .= $this->metaSsr('twitter:description', $this->description);
        }
        $head .= ($this->image != false) ? $this->metaProperty('og:image', $this->image) : '';
        $head .= $this->metaProperty('og:url', $httpDomain);
        $head .= "\t\t".WCO::getHeder();
        echo $head;
    }
    
    public function getTitle() {
        $head = '<title>'. $this->title.'</title>'.PHP_EOL;
        $head .= $this->meta('description', $this->description);
        $head .= $this->meta('keywords', $this->keywords);
        return $head;
    }
    
    public function meta($name, $value) {
        $head = "\t\t<meta name=\"$name\" content=\"$value\">".PHP_EOL;
        return $head;
    }
    
    public function metaSsr($meta, $value) {
        return "\t\t<meta data-vue-meta=\"ssr\" content=\"$value\" data-vmid=\"$meta\">".PHP_EOL;
    }
    
    public function metaProperty($meta, $value) {
        return "\t\t<meta property=\"$meta\" content=\"$value\">".PHP_EOL;
    }
}
?>