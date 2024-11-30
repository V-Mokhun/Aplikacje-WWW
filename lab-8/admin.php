<?php
session_start();

require_once('cfg.php');

function Logout() {
    session_destroy();
    header("Location: admin.php");
    exit();
}

function handleFormSubmissions($link) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_page']) && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $title = mysqli_real_escape_string($link, $_POST['page_title']);
        $alias = mysqli_real_escape_string($link, $_POST['page_alias']);
        $heading = mysqli_real_escape_string($link, $_POST['page_heading']);
        $content = mysqli_real_escape_string($link, $_POST['page_content']);
        $active = isset($_POST['page_active']) ? 1 : 0;
        
        $query = "UPDATE page_list SET 
                 page_title = '$title',
                 page_alias = '$alias',
                 page_heading = '$heading',
                 page_content = '$content',
                 status = $active 
                 WHERE id = $id";
                 
        if (mysqli_query($link, $query)) {
            header("Location: admin.php");
            exit();
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_page'])) {
        $title = mysqli_real_escape_string($link, $_POST['page_title']);
        $alias = mysqli_real_escape_string($link, $_POST['page_alias']);
        $heading = mysqli_real_escape_string($link, $_POST['page_heading']);
        $content = mysqli_real_escape_string($link, $_POST['page_content']);
        $active = isset($_POST['page_active']) ? 1 : 0;
        
        $query = "INSERT INTO page_list (page_title, page_alias, page_heading, page_content, status) 
                 VALUES ('$title', '$alias', '$heading', '$content', $active)";
                 
        if (mysqli_query($link, $query)) {
            header("Location: admin.php");
            exit();
        }
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $query = "DELETE FROM page_list WHERE id = $id LIMIT 1";
        
        if (mysqli_query($link, $query)) {
            header("Location: admin.php");
            exit();
        }
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        Logout();
    }
}

function ListaPodstron($link) {
    $result = '<div class="page-list">';
    $result .= '<h2>Lista Podstron</h2>';
    $result .= '<a href="?action=add" class="add-button">Dodaj nową stronę</a>';
    $result .= '<table border="1">
                <tr>
                    <th>ID</th>
                    <th>Tytuł</th>
                    <th>Nagłówek</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>';
    
    $query = "SELECT * FROM page_list ORDER BY id ASC";
    $pages = mysqli_query($link, $query);
    
    while ($row = mysqli_fetch_array($pages)) {
        $result .= '<tr>
                    <td>'.$row['id'].'</td>
                    <td>'.$row['page_title'].'</td>
                    <td>'.$row['page_heading'].'</td>
                    <td>'.($row['status'] ? 'Aktywna' : 'Nieaktywna').'</td>
                    <td>
                        <a href="?action=edit&id='.$row['id'].'">Edytuj</a>
                        <a href="?action=delete&id='.$row['id'].'" onclick="return confirm(\'Czy na pewno chcesz usunąć?\')">Usuń</a>
                    </td>
                </tr>';
    }
    
    $result .= '</table></div>';
    return $result;
}

function EdytujPodstrone($link) {
    if (!isset($_GET['id'])) {
        return '<div class="error">Nie wybrano strony do edycji</div>';
    }
    
    $id = (int)$_GET['id'];
    
    $query = "SELECT * FROM page_list WHERE id = $id LIMIT 1";
    $result = mysqli_query($link, $query);
    $page = mysqli_fetch_assoc($result);
    
    if (!$page) {
        return '<div class="error">Nie znaleziono strony o podanym ID</div>';
    }
    
    $form = '
    <div class="edit-form">
        <h2>Edycja Podstrony</h2>
        <form method="POST" class="edit-form-content">
            <div class="form-group">
                <label for="page_title">Tytuł strony:</label>
                <input type="text" id="page_title" name="page_title" value="' . htmlspecialchars($page['page_title']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="page_alias">Alias strony (do URL):</label>
                <input type="text" id="page_alias" name="page_alias" value="' . htmlspecialchars($page['page_alias']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="page_heading">Nagłówek strony:</label>
                <input type="text" id="page_heading" name="page_heading" value="' . htmlspecialchars($page['page_heading']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="page_content">Treść strony:</label>
                <textarea id="page_content" name="page_content" rows="10" required>' . htmlspecialchars($page['page_content']) . '</textarea>
            </div>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="page_active" ' . ($page['status'] ? 'checked' : '') . '>
                    Strona aktywna
                </label>
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="save_page" class="save-button">Zapisz zmiany</button>
                <a href="admin.php" class="cancel-button">Anuluj</a>
            </div>
        </form>
    </div>';

    return $form;
}

function DodajNowaPodstrone($link) {
    include('cfg.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_page'])) {
      $title = mysqli_real_escape_string($link, $_POST['page_title']);
      $alias = mysqli_real_escape_string($link, $_POST['page_alias']);
      $heading = mysqli_real_escape_string($link, $_POST['page_heading']);
      $content = mysqli_real_escape_string($link, $_POST['page_content']);
      $active = isset($_POST['page_active']) ? 1 : 0;

      $query = "INSERT INTO page_list (page_title, page_alias, page_heading, page_content, status) 
                 VALUES ('$title', '$alias', '$heading', '$content', $active)";

      if (mysqli_query($link, $query)) {
        header("Location: admin.php");
        exit();
      } else {
        return '<div class="error">Błąd podczas dodawania strony: ' . mysqli_error($link) . '</div>';
      }
    }

    $form = '
    <div class="edit-form">
        <h2>Dodaj Nową Podstronę</h2>
        <form method="POST" class="edit-form-content">
            <div class="form-group">
                <label for="page_title">Tytuł strony:</label>
                <input type="text" id="page_title" name="page_title" required>
            </div>
            
            <div class="form-group">
                <label for="page_alias">Alias strony (do URL):</label>
                <input type="text" id="page_alias" name="page_alias" required>
                <small>Np. "moja-strona" (bez spacji, tylko małe litery i myślniki)</small>
            </div>
            
            <div class="form-group">
                <label for="page_heading">Nagłówek strony:</label>
                <input type="text" id="page_heading" name="page_heading" required>
            </div>
            
            <div class="form-group">
                <label for="page_content">Treść strony:</label>
                <textarea id="page_content" name="page_content" rows="10" required></textarea>
            </div>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="page_active" checked>
                    Strona aktywna
                </label>
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="add_page" class="save-button">Dodaj stronę</button>
                <a href="admin.php" class="cancel-button">Anuluj</a>
            </div>
        </form>
    </div>';

    return $form;
}

function AdminHeader() {
    return '
    <div class="admin-header">
        <h1>Panel Administracyjny CMS</h1>
        <a href="?action=logout" class="logout-button">Wyloguj się</a>
    </div>';
}

$login_status = LoginCheck();

if ($login_status === true) {
    handleFormSubmissions($link);
}

?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administracyjny CMS</title>
  <style>
    :root {
      --primary-color: #2c3e50;
      --secondary-color: #3498db;
      --error-color: #e74c3c;
      --success-color: #2ecc71;
      --background-color: #f5f6fa;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 20px;
      background-color: var(--background-color);
    }

    .login-form {
      max-width: 400px;
      margin: 50px auto;
      padding: 20px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .login-title {
      color: var(--primary-color);
      text-align: center;
      margin-bottom: 30px;
    }

    .login-form-content {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    input[type="text"],
    input[type="password"] {
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
    }

    button {
      background-color: var(--secondary-color);
      color: white;
      padding: 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #2980b9;
    }

    .error {
      background-color: var(--error-color);
      color: white;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
      text-align: center;
    }

    .page-list {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .page-list h2 {
      color: var(--primary-color);
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: var(--primary-color);
      color: white;
    }

    tr:hover {
      background-color: #f5f5f5;
    }

    a {
      color: var(--secondary-color);
      text-decoration: none;
      margin-right: 10px;
      padding: 5px 10px;
      border-radius: 4px;
      transition: background-color 0.3s;
    }

    a:hover {
      background-color: #eef2f7;
    }

    a[href*="delete"] {
      color: var(--error-color);
    }

    @media (max-width: 600px) {
      .login-form {
        margin: 20px auto;
        padding: 15px;
      }

      table {
        font-size: 14px;
      }

      th,
      td {
        padding: 8px;
      }
    }

    .edit-form {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: 20px auto;
    }

    .edit-form h2 {
      color: var(--primary-color);
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      color: var(--primary-color);
      font-weight: 500;
    }

    .form-group input[type="text"],
    .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
      font-family: inherit;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
    }

    .checkbox-group input[type="checkbox"] {
      margin-right: 10px;
    }

    .form-buttons {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .save-button {
      background-color: var(--success-color);
    }

    .save-button:hover {
      background-color: #27ae60;
    }

    .cancel-button {
      background-color: #95a5a6;
      color: white;
      padding: 12px 20px;
      border-radius: 4px;
      text-decoration: none;
    }

    .cancel-button:hover {
      background-color: #7f8c8d;
    }

    .add-button {
      display: inline-block;
      background-color: var(--success-color);
      color: white;
      padding: 10px 20px;
      border-radius: 4px;
      margin-bottom: 20px;
      text-decoration: none;
    }

    .add-button:hover {
      background-color: #27ae60;
      color: white;
    }

    .admin-header {
        background: var(--primary-color);
        color: white;
        padding: 15px 20px;
        margin: -20px -20px 20px -20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .admin-header h1 {
        margin: 0;
        font-size: 24px;
    }
    
    .logout-button {
        background-color: var(--error-color);
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s;
    }
    
    .logout-button:hover {
        background-color: #c0392b;
        color: white;
    }
  </style>
</head>

<body>

  <?php
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

  if ($login_status === true) {
    if (isset($_GET['action'])) {
      switch ($_GET['action']) {
        case 'add':
          echo AdminHeader();
          echo DodajNowaPodstrone($link);
          break;
        case 'edit':
          echo AdminHeader();
          echo EdytujPodstrone($link);
          break;
        default:
          echo AdminHeader();
          echo ListaPodstron($link);
      }
    } else {
      echo AdminHeader();
      echo ListaPodstron($link);
    }
  } else {
    if ($login_status !== false) {
      echo '<div class="error">' . $login_status . '</div>';
    }
    echo LoginForm();
  }
  ?>

</body>

</html>
