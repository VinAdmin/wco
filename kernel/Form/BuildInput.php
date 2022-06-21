<?php
namespace wco\kernel\Form;

class BuildInput{
    private $input = null;
    
    function __construct($input) {
        $this->input = $input;
    }

    public function Field($options=array()) {
        $class = $this->Class($options);
        $div = '<div class="'.$class.'">'.$this->input.'</div>';
        $str = $div;
        return $this->input;
    }
    
    private function Class($options) {
        if(!isset($options['class'])){
            return 'col-md-12 col-lg-12';
        } else {
            return $options['class'];
        }
    }
}