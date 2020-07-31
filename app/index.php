<?php 
  include "settings.php";
  session_start();
  if (isset($_POST['key']) ) {
    $_SESSION['key'] = $_POST['key'];
  }
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Импорт в ВК</title>
</head>
<body>
  <?php if ((isset($_SESSION['key']) && ($_SESSION['key'] == 'Iddqd2011'))): ?>
    <div style="width:1000px; margin:0 auto;">
      <h2>Инфо</h2>
      <p>Для запуска кроном: </p>
        <ul>
          <li><em>yaml2vk.php?key='Iddqd2011'</em></li>
          <li><em>vk_truncate.php?key='Iddqd2011'</em></li>
        </ul>
      <?php if (file_exists(PROC)): ?>
        <?php $proc = json_decode(file_get_contents(PROC)) ?>
        <p><span style="text-decoration: underline; font-size: 1.4em;">Текущий процесс <?= $proc->pid ?></span></p>
        <p>
          <a style="font-size: 1.2em; color: red;" href="stop.php">Остановить</a>|
          <a style="font-size: 1.2em; color: red;" href="pause.php">Пауза</a>
        </p>
        <?php else: ?>
      <h3>Управление</h3>
        <p><a style="font-size: 1.2em; color: red;" href="yaml2vk.php?key=Iddqd2011">Запустить</a> | <a style="font-size: 1.2em; color: red;" href="vk_truncate.php?key=Iddqd2011">Очистить</a></p>
      <?php endif ?>

    <ul>
      <li>yaml2vk.php - Основной скрипт для запуска кроном</li>
      <li>auth.php - Для начала работы необходимо получить токен для сервера</li>
      <li>vk_truncate.php - очистка каталога ВК от товаров</li>
    </ul>
    <h3>Временные файлы</h3>
    <ul>
      <li>/token.dat - сам токен хранится в </li>
      <li>/local_db.json - Локальная база товаров</li>
      <li>/img_cover и /img_good - Временные файлы рендеров изображений</li>
      <li>/proc - файл ID процесса</li>
    </ul>
    <h2>Лог за <?= date("d m Y") ?></h2>
    <?php if (file_exists("logs/" . date("d_m_y") . "-log.txt")): ?>
      <iframe height=500px width=100% src="<?= $log_name = "logs/" . date("d_m_y") . "-log.txt"; ?>" frameborder="0"></iframe>
    <?php endif ?>
    <em>Лог через фрейм поэтому shift+f5 для обновления</em>
    </div>
  <?php else: ?>
  <form action="." method="POST">
    <input type="text" name="key">
    <input type="submit" value="Далее">
  </form>
  <?php endif ?>
</body>
</html>