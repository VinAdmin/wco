<?php
namespace wco\forms;

use wco\forms\BuildInput;

/**
 * Класс генерации форм.
 * @property string INPUT_TEXT type="text"
 * @property string INPUT_SUBMIT type="submit"
 */
class Form {
    const INPUT_TEXT = 'text';
    const INPUT_SUBMIT = 'submit';

    public function FormStart(string $name = 'new_form',string $method = 'get', $action = null,$autocomplete = 'on',array $options=array()) {
        $class = $this->Class($options,null);
        $form = '<form name="'.$name.'" id="'.$name.'" method="'.$method.'" action="'.$action.'" autocomplete="'.$autocomplete.'" '
                . 'class="'.$class.'">';
        return $form;
    }
    
    /**
     * Поле <input />
     * @param string $type Тип поля date, text, submit
     * @param string $name Имя поля
     * @param type $value Значение
     * @param array $options array(
     *                          'class' => 'По умолчанию класс form-control',
     *                          'placeholder'=>'value',
     *                          'maxlength'=>'value',
     *                          'atr' => 'autofocus required',
     *                          'checked'=>true)
     * @return BuildInput
     */
    public function Input(string $type,string $name,$value=null,array $options=array()) {
        $input = '<input type="'.$type.'" '
                . 'name="'.$name.'" '
                . 'id="'.$name.'" '.$this->Value($value).' '
                . 'class="'.$this->Class($options).'" '
                . 'placeholder="'.$this->placeholder($options).'" '
                . $this->maxlength($options).' '
                . $this->Atr($options).' '
                . $this->checked($options).' '
                . $this->Disabled($options) .'/>';
        
        return new BuildInput($input);
    }
    
    /**
     * Поле <select></select>
     * @param string $name Имя поля
     * @param array $params [['key','value'],['key','value'],...]
     * @param type $selected Прараметр для выборки default null
     * @param array $options array(
     *                          'class' => 'По умолчанию класс form-control',
     *                          'atr' => 'autofocus required')
     * @return BuildInput
     */
    public function Select(string $name,array $params = array(),$selected = null,array $options=array()) {
        $class = $this->Class($options);
        $select = '<select name="'.$name.'" class="'.$class.'" '.$this->onchange($options).' '.$this->Atr($options).'>';
        $select .= $this->SelectOptionValue($params,$selected);
        $select .= '</select>';
        
        return new BuildInput($select);
    }
    
    /**
     * Поле ввода текста
     * @param string $name
     * @param type $value
     * @param array $options
     */
    public function Textarea(string $name,$value=null,array $options=array()) {
        $textarea = '<textarea name="'.$name.'" '
                . 'class="'.$this->Class($options).'" '
                . $this->maxlength($options).' '
                . $this->Atr($options).' >'.$value;
        $textarea .= '</textarea>';
        return new BuildInput($textarea);
    }
    
    private function Class($options,$default = 'form-control') {
        if(!isset($options['class'])){
            return $default;
        } else {
            return $options['class'];
        }
    }
    
    public function Field($options=array()) {
        $class = $this->Class($options);
        $div = '<div class="'.$class.'">'.$this->input.'</div>';
        $str = $div;
        return $str;
    }
    
    private function Value($value){
        return 'value="'.$value.'"';
    }
    
    /**
     * @param type $options
     * @return type
     */
    private function onchange($options) {
        if(!isset($options['onchange'])){
            return null;
        } else {
            return 'onchange="'.$options['onchange'].'"';
        }
    }
    
    private function SelectOptionValue(array $params = array(),$selected = null) {
        $option = '<option value="">Выбрать</option>';
        foreach ($params AS $value){
            $str_selected = ($selected == $value[0]) ? 'selected=""' : null;
            $option .= '<option '.$this->Value($value[0]).' '.$str_selected.'>'.$value[1].'</option>';
        }
        
        return $option;
    }
    
    private function Atr($options) {
        if(!isset($options['atr'])){
            return null;
        }else{
            return $options['atr'];
        }
    }
    
    private function placeholder($options) {
        if(!isset($options['placeholder'])){
            return null;
        } else {
            return $options['placeholder'];
        }
    }
    
    private function maxlength($options) {
        if(!isset($options['maxlength'])){
            return null;
        } else {
            return 'maxlength="'.$options['maxlength'].'"';
        }
    }
    
    private function checked($options) {
        if(isset($options['checked'])){
            return ($options['checked']) ? 'checked' : null;
        }
        return null;
    }
    
    public function FormEnd(){
        return '</form>';
    }
    
    /**
     * Кнопка.
     * 
     * @param string $type
     * @param string $text
     * @param string $class Клас стилей
     * @return string
     */
    public function Button($type, $text, $class = null) {
        if(is_null($class)){
            $class = null;
        }else{
            $class = 'class="'.$class.'"';
        }
        
        $btn = '<button type="' . $type . '" '.$class.'>'.$text.'</button>';
        return $btn;
    }
    
    private function Disabled($options) {
        if(isset($options['disabled'])){
            return ($options['disabled'] == true) ? 'disabled=""' : null;
        }
        return null;
    }
}