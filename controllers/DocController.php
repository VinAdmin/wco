<?php

class DocController extends \wco\kernel\Controller{
    public function actionIndex() {
        $this->generate('/index/index.php');
        return true;
    }
}
