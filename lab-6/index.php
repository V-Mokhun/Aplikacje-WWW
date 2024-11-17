<?php
include_once './cfg.php';

// $pages = [
//   'home' => [
//     'title' => "World's Biggest Bridges",
//     'heading' => "World's Biggest Bridges"
//   ],
//   'js-test' => [
//     'title' => "JS Test - World's Biggest Bridges",
//     'heading' => "JavaScript Testing Page"
//   ],
//   'jquery-test' => [
//     'title' => "jQuery Test - World's Biggest Bridges",
//     'heading' => "jQuery Testing Page"
//   ],
//   'bridge1' => [
//     'title' => "Danyang–Kunshan Grand Bridge - World's Biggest Bridges",
//     'heading' => "Danyang–Kunshan Grand Bridge"
//   ],
//   'bridge2' => [
//     'title' => "Changhua–Kaohsiung Viaduct - World's Biggest Bridges",
//     'heading' => "Changhua–Kaohsiung Viaduct"
//   ],
//   'bridge3' => [
//     'title' => "Tianjin Grand Bridge - World's Biggest Bridges",
//     'heading' => "Tianjin Grand Bridge"
//   ],
//   'bridge4' => [
//     'title' => "Weinan Weihe Grand Bridge - World's Biggest Bridges",
//     'heading' => "Weinan Weihe Grand Bridge"
//   ],
//   'bridge5' => [
//     'title' => "Bang Na Expressway - World's Biggest Bridges",
//     'heading' => "Bang Na Expressway"
//   ],
//   'filmy' => [
//     'title' => "Filmy - World's Biggest Bridges",
//     'heading' => "Filmy"
//   ]
// ];
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// $title = isset($pages[$page]) ? $pages[$page]['title'] : "World's Biggest Bridges";
// $heading = isset($pages[$page]) ? $pages[$page]['heading'] : "World's Biggest Bridges";

// $contentFile = "./html/{$page}.html";

include_once './showpage.php';
list($title, $heading, $content) = showPage($page);
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
          <li><a href="index.php?page=home">Home</a></li>
          <li><a href="index.php?page=js-test">JS Test</a></li>
          <li><a href="index.php?page=jquery-test">JQuery Test</a></li>
          <li><a href="index.php?page=filmy">Filmy</a></li>
          <li><a href="index.php?page=bridge1">Danyang–Kunshan Grand Bridge</a></li>
          <li><a href="index.php?page=bridge2">Changhua–Kaohsiung Viaduct</a></li>
          <li><a href="index.php?page=bridge3">Tianjin Grand Bridge</a></li>
          <li><a href="index.php?page=bridge4">Weinan Weihe Grand Bridge</a></li>
          <li><a href="index.php?page=bridge5">Bang Na Expressway</a></li>
          <li><a href="mailto:example.com">Contact</a></li>
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
