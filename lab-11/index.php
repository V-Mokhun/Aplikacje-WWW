<?php

/**
 * ===============================
 * Main Page Controller
 * ===============================
 */

// Include required files
include_once './cfg.php';
include_once './showpage.php';

// Get requested page or default to home
$page = $_GET['page'] ?? 'home';

// Get page content
list($title, $heading, $content) = showPage($page);
$pageList = showPageList($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="Author" content="Volodymyr Mokhun" />
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="./css/styles.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
  <table id="layout">
    <tr>
      <td colspan="2" id="header">
        <h1><?php echo htmlspecialchars($heading); ?></h1>
      </td>
    </tr>
    <tr>
      <td id="menu">
        <ul>
          <?php
          // Generate menu items from database
          while ($row = mysqli_fetch_array($pageList)) {
            echo '<li><a href="index.php?page=' . htmlspecialchars($row['page_alias']) . '">'
              . htmlspecialchars($row['page_heading']) . '</a></li>';
          }
          echo '<li><a href="contact.php">Contact</a></li>';
          ?>
        </ul>
      </td>
      <td id="content">
        <?php echo $content; ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" id="footer">
        <p>&copy; 2024 World's Biggest Bridges. All rights reserved.</p>
        <?php
        $nr_indeksu = '169404';
        $nrGrupy = '3';
        echo 'Autor: Volodymyr Mokhun ' . htmlspecialchars($nr_indeksu) . ' grupa ' . htmlspecialchars($nrGrupy) . ' <br /><br />';
        ?>
      </td>
    </tr>
  </table>
  <script src="./js/clock.js"></script>
  <script src="./js/change-bg.js"></script>
  <script src="./js/jquery-test.js"></script>
</body>

</html>
