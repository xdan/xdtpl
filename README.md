xdtpl
=====

Простейший шаблонизатор на php

использовать просто

<?php
$tpl = new tpl(); 
$tpl->assign('content','Привет Мир!!!');
$tpl->assign('title','Демо сайт');// простое прсваивание
$tpl->assign(array('news'=>'Новости','users'=>'Список польователей'));
$tpl->parse('index');

файл шаблонов

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title><?php echo $this->title;?></title>
<body>
  <?php echo $this->content;?>
	<div>
		<?php 
			echo $news;
			$this->show('news_block');
		?>
	</div>
	<div><?php echo $this->content;?></div>
</body>
</html>


