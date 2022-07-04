<?php
use wco\kernel\WCO;
use wco\forms\Form;

$this->title = 'Класс построения форм';

$form = new Form();
?>
<nav aria-label="breadcrumb" class="nav_breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=WCO::Url('/')?>">Главна</a></li>
        <li class="breadcrumb-item"><a href="<?=WCO::Url('/wco/doc')?>">Документация</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$this->title?></li>
    </ol>
</nav>

<h1><?=$this->title?></h1>

<p>Пример:</p>
<code>
    <p>use wco\forms\Form;</p>
    <p>$form->FormStart()</p>
    <p>$form->Input(Form::INPUT_TEXT, 'email')->Field()</p>
    <p>$form->Input(Form::INPUT_SUBMIT, 'send','Отправить')->Field()</p>
    <p>$form->FormEnd()</p>
</code>

<p>Результат:</p>
<div>
    <?=$form->FormStart()?>
    <?=$form->Input(Form::INPUT_TEXT, 'email')->Field()?>
    <?=$form->Input(Form::INPUT_SUBMIT, 'send','Отправить')->Field()?>
    <?=$form->FormEnd()?>
</div>