<?php
use wco\kernel\WCO;

$this->title = 'Документация';
?>

<nav aria-label="breadcrumb" class="nav_breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?=WCO::Url('/')?>">Главна</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?=$this->title?></li>
  </ol>
</nav>

<h1><?=$this->title?></h1>

<div>
    <a href="<?=WCO::Url('/wco/doc/forms')?>">Класс построения форм</a>
    <p>
        Интегрированная в ядро построитель форм.
    </p>
</div>

<div>
    <a href="https://tretyakov.net/post/phinx-migracii-bazy-dannyh/" target="_blank">Phinx — миграции базы данных для вашего приложения</a>
    <p>
        Миграция— создание/изменение структуры базы данных от одной версии до другой (не перенос данных, а именно работа со структурой), другими словами это что-то вроде системы контроля версий для вашей БД. Это очень удобный инструмент, позволяющий всем участникам команды разработки оставаться в курсе изменений в структуре БД используя Git.
    </p>
</div>
