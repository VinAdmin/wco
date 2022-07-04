<?php

class DocController extends \wco\kernel\Controller{
    public function actionIndex() {
        $this->generate('/index/index.php');
        return true;
    }
    public function actionForms() {
        $this->generate('/index/forms.php');
        return true;
    }
}
