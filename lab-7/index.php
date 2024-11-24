<?php
include_once './cfg.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

include_once './showpage.php';
list($title, $heading, $content) = showPage($page);
$pageList = showPageList($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="Author" content="Volodymyr Mokhun" />
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="./css/styles.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
  <table id="layout">
    <tr>
      <td colspan="2" id="header">
        <h1><?php echo $heading; ?></h1>
      </td>
    </tr>
    <tr>
      <td id="menu">
        <ul>
          <?php

          while ($row = mysqli_fetch_array($pageList)) {
            echo '<li><a href="index.php?page=' . htmlspecialchars($row['page_alias']) . '">'
              . htmlspecialchars($row['page_heading']) . '</a></li>';
          }

          echo '<li><a href="mailto:example.com">Contact</a></li>';
          ?>
          <!-- <li><a href="index.php?page=home">Home</a></li>
          <li><a href="index.php?page=js-test">JS Test</a></li>
          <li><a href="index.php?page=jquery-test">JQuery Test</a></li>
          <li><a href="index.php?page=filmy">Filmy</a></li>
          <li><a href="index.php?page=bridge1">Danyang–Kunshan Grand Bridge</a></li>
          <li><a href="index.php?page=bridge2">Changhua–Kaohsiung Viaduct</a></li>
          <li><a href="index.php?page=bridge3">Tianjin Grand Bridge</a></li>
          <li><a href="index.php?page=bridge4">Weinan Weihe Grand Bridge</a></li>
          <li><a href="index.php?page=bridge5">Bang Na Expressway</a></li>
          <li><a href="mailto:example.com">Contact</a></li> -->
        </ul>
      </td>
      <td id="content">
        <?php
        echo $content;
        ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" id="footer">
        <p>&copy; 2024 World's Biggest Bridges. All rights reserved.</p>
        <?php
        $nr_indeksu = '169404';
        $nrGrupy = '3';
        echo 'Autor: Volodymyr Mokhun ' . $nr_indeksu . ' grupa ' . $nrGrupy . ' <br /><br />';
        ?>
      </td>
    </tr>
  </table>
  <script src="./js/clock.js"></script>
  <script src="./js/change-bg.js"></script>
  <script src="./js/jquery-test.js"></script>
</body>

</html>
