<?php

/**
 * ===============================
 * Page Management Functions
 * ===============================
 */

/**
 * Processes page-related form submissions
 * 
 * @param mysqli $link Database connection object
 */
function handlePageSubmissions($link)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_page']) && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false) {
      die("Invalid page ID");
    }

    // Sanitize all input fields
    $title = mysqli_real_escape_string($link, trim($_POST['page_title']));
    $alias = mysqli_real_escape_string($link, trim($_POST['page_alias']));
    $heading = mysqli_real_escape_string($link, trim($_POST['page_heading']));
    $content = mysqli_real_escape_string($link, trim($_POST['page_content']));
    $active = isset($_POST['page_active']) ? 1 : 0;

    $query = "UPDATE page_list SET 
                 page_title = ?, 
                 page_alias = ?, 
                 page_heading = ?, 
                 page_content = ?, 
                 status = ? 
                 WHERE id = ? LIMIT 1";

    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ssssii", $title, $alias, $heading, $content, $active, $id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: admin.php");
      exit();
    }
  }

  // Handle new page addition
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_page'])) {
    $title = mysqli_real_escape_string($link, trim($_POST['page_title']));
    $alias = mysqli_real_escape_string($link, trim($_POST['page_alias']));
    $heading = mysqli_real_escape_string($link, trim($_POST['page_heading']));
    $content = mysqli_real_escape_string($link, trim($_POST['page_content']));
    $active = isset($_POST['page_active']) ? 1 : 0;

    $query = "INSERT INTO page_list (page_title, page_alias, page_heading, page_content, status) 
                 VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ssssi", $title, $alias, $heading, $content, $active);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: admin.php");
      exit();
    }
  }

  // Handle page deletion
  if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false) {
      die("Invalid page ID");
    }

    $query = "DELETE FROM page_list WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: admin.php");
      exit();
    }
  }
}

/**
 * Displays the list of all pages in the system
 * 
 * @param mysqli $link Database connection object
 * @return string HTML content of the page list
 */
function ListaPodstron($link)
{
  $result = '<div class="card">';
  $result .= '<h2>Lista Podstron</h2>';
  $result .= '<a href="?action=add" class="btn btn-success">Dodaj nową stronę</a>';
  $result .= '<table class="table">
                <tr>
                    <th>ID</th>
                    <th>Tytuł</th>
                    <th>Nagłówek</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>';

  $query = "SELECT * FROM page_list ORDER BY id ASC LIMIT 100";
  $pages = mysqli_query($link, $query);

  while ($row = mysqli_fetch_array($pages)) {
    $result .= '<tr>
                    <td>' . htmlspecialchars($row['id']) . '</td>
                    <td>' . htmlspecialchars($row['page_title']) . '</td>
                    <td>' . htmlspecialchars($row['page_heading']) . '</td>
                    <td>' . ($row['status'] ? 'Aktywna' : 'Nieaktywna') . '</td>
                    <td>
                        <a href="?action=edit&id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary">Edytuj</a>
                        <a href="?action=delete&id=' . htmlspecialchars($row['id']) . '" 
                           class="btn btn-danger"
                           onclick="return confirm(\'Czy na pewno chcesz usunąć?\')">Usuń</a>
                    </td>
                </tr>';
  }

  $result .= '</table></div>';
  return $result;
}

/**
 * Displays the edit form for a specific page
 * 
 * @param mysqli $link Database connection object
 * @return string HTML content of the edit form
 */
function EdytujPodstrone($link)
{
  if (!isset($_GET['id'])) {
    return '<div class="alert alert-error">Nie wybrano strony do edycji</div>';
  }

  $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

  if ($id === false) {
    return '<div class="alert alert-error">Nieprawidłowe ID strony</div>';
  }

  // Use prepared statement to prevent SQL injection
  $query = "SELECT * FROM page_list WHERE id = ? LIMIT 1";
  $stmt = mysqli_prepare($link, $query);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $page = mysqli_fetch_assoc($result);

  if (!$page) {
    return '<div class="alert alert-error">Nie znaleziono strony o podanym ID</div>';
  }

  $form = '
    <div class="card">
        <h2>Edycja Podstrony</h2>
        <form method="POST">
            <div class="form-group">
                <label for="page_title">Tytuł strony:</label>
                <input type="text" class="form-control" id="page_title" name="page_title" 
                       value="' . htmlspecialchars($page['page_title']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="page_alias">Alias strony (do URL):</label>
                <input type="text" class="form-control" id="page_alias" name="page_alias" 
                       value="' . htmlspecialchars($page['page_alias']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="page_heading">Nagłówek strony:</label>
                <input type="text" class="form-control" id="page_heading" name="page_heading" 
                       value="' . htmlspecialchars($page['page_heading']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="page_content">Treść strony:</label>
                <textarea class="form-control" id="page_content" name="page_content" 
                          rows="10" required>' . htmlspecialchars($page['page_content']) . '</textarea>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="page_active" ' . ($page['status'] ? 'checked' : '') . '>
                    Strona aktywna
                </label>
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="save_page" class="btn btn-success">Zapisz zmiany</button>
                <a href="admin.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>';

  return $form;
}

function DodajNowaPodstrone($link)
{
  $form = '
    <div class="card">
        <h2>Dodaj Nową Podstronę</h2>
        <form method="POST">
            <div class="form-group">
                <label for="page_title">Tytuł strony:</label>
                <input type="text" class="form-control" id="page_title" name="page_title" required>
            </div>
            
            <div class="form-group">
                <label for="page_alias">Alias strony (do URL):</label>
                <input type="text" class="form-control" id="page_alias" name="page_alias" required>
                <small>Np. "moja-strona" (bez spacji, tylko małe litery i myślniki)</small>
            </div>
            
            <div class="form-group">
                <label for="page_heading">Nagłówek strony:</label>
                <input type="text" class="form-control" id="page_heading" name="page_heading" required>
            </div>
            
            <div class="form-group">
                <label for="page_content">Treść strony:</label>
                <textarea class="form-control" id="page_content" name="page_content" rows="10" required></textarea>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="page_active" checked>
                    Strona aktywna
                </label>
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="add_page" class="btn btn-success">Dodaj stronę</button>
                <a href="admin.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>';

  return $form;
}
