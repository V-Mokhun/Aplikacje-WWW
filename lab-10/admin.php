<?php
session_start();

require_once('cfg.php');
require_once('admin-pages.php');
require_once('admin-categories.php');

/**
 * ===============================
 * Authentication Functions
 * ===============================
 */

/**
 * Handles user logout by destroying the session
 * and redirecting to the admin page
 */
function Logout()
{
  session_destroy();
  header("Location: admin.php");
  exit();
}

function AdminHeader()
{
  $current_page = $_GET['action'] ?? '';
  $pages_active = empty($current_page) || in_array($current_page, ['add', 'edit', 'delete']) ? 'active' : '';
  $categories_active = strpos($current_page, 'category') !== false || $current_page === 'categories' ? 'active' : '';

  return '
    <div class="admin-header">
        <h1>Panel Administracyjny CMS</h1>
        <div class="admin-nav">
            <a href="admin.php" class="nav-link ' . $pages_active . '">Strony</a>
            <a href="?action=categories" class="nav-link ' . $categories_active . '">Kategorie</a>
            <a href="?action=logout" class="logout-button">Wyloguj się</a>
        </div>
    </div>';
}

function LoginForm()
{
  $result = '
    <div class="login-form">
      <h1 class="login-title">CMS Panel</h1>
      <form action="' . $_SERVER['REQUEST_URI'] . '" method="POST" name="LoginForm" enctype="multipart/form-data" class="login-form-content">
        <input type="text" name="login_email" placeholder="Email" required>
        <input type="password" name="login_password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
    </div>
  ';

  return $result;
}

function LoginCheck()
{
  include('cfg.php');

  if (isset($_POST['login_email']) && isset($_POST['login_password'])) {
    if ($_POST['login_email'] == $login && $_POST['login_password'] == $password) {
      $_SESSION['logged_in'] = true;
      return true;
    } else {
      return "Nieprawidłowy email lub hasło";
    }
  }

  return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}


?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administracyjny CMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="content-container">
    <?php
    $login_status = LoginCheck();

    if ($login_status === true) {
      if (strpos($_GET['action'] ?? '', 'category') !== false) {
        handleCategorySubmissions($link);
      } else {
        handlePageSubmissions($link);
      }

      echo AdminHeader();

      $action = $_GET['action'] ?? '';

      echo '<div class="card">';
      if ($action === 'categories') {
        echo ListaKategorii($link);
      } else if ($action === 'add_category') {
        echo DodajNowaKategorie($link);
      } else if ($action === 'edit_category') {
        echo EdytujKategorie($link);
      } else if ($action === 'add') {
        echo DodajNowaPodstrone($link);
      } else if ($action === 'edit') {
        echo EdytujPodstrone($link);
      } else {
        echo ListaPodstron($link);
      }
      echo '</div>';
    } else {
      echo '<div class="login-container">';
      if ($login_status !== false) {
        echo '<div class="alert alert-error">' . $login_status . '</div>';
      }
      echo LoginForm();
      echo '</div>';
    }
    ?>
  </div>
</body>

</html>
