<?php

/**
 * ===============================
 * Category Management Functions
 * ===============================
 */

/**
 * Handles category-related form submissions
 * 
 * @param mysqli $link Database connection object
 */
function handleCategorySubmissions($link)
{
  // Handle category update
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category']) && isset($_GET['category_id'])) {
    $id = filter_var($_GET['category_id'], FILTER_VALIDATE_INT);

    if ($id === false) {
      die("Invalid category ID");
    }

    $name = mysqli_real_escape_string($link, trim($_POST['category_name']));
    $parent_id = !empty($_POST['parent_category']) ?
      mysqli_real_escape_string($link, trim($_POST['parent_category'])) : null;

    $query = "UPDATE category SET name = ?, category_id = ? WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $name, $parent_id, $id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: admin.php?action=categories");
      exit();
    }
  }

  // Handle new category addition
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($link, trim($_POST['category_name']));
    $parent_id = !empty($_POST['parent_category']) ?
      mysqli_real_escape_string($link, trim($_POST['parent_category'])) : null;

    $query = "INSERT INTO category (name, category_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ss", $name, $parent_id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: admin.php?action=categories");
      exit();
    }
  }

  // Handle category deletion
  if (isset($_GET['action']) && $_GET['action'] === 'delete_category' && isset($_GET['category_id'])) {
    $id = filter_var($_GET['category_id'], FILTER_VALIDATE_INT);

    if ($id === false) {
      die("Invalid category ID");
    }

    $query = "DELETE FROM category WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: admin.php?action=categories");
      exit();
    }
  }
}

/**
 * ===============================
 * Page Management Functions
 * ===============================
 */

/**
 * Displays the list of all categories
 * 
 * @param mysqli $link Database connection object
 * @return string HTML content of the category list
 */
function ListaKategorii($link)
{
  $result = '<div class="card">';
  $result .= '<h2>Lista Kategorii</h2>';
  $result .= '<a href="?action=add_category" class="btn btn-success">Dodaj nową kategorię</a>';

  // Get all categories and organize them into a hierarchical array
  $categories = [];
  $query = "SELECT * FROM category ORDER BY name ASC";
  $categoriesResult = mysqli_query($link, $query);

  while ($row = mysqli_fetch_array($categoriesResult)) {
    $parentId = $row['category_id'] === NULL ? '0' : $row['category_id'];
    $categories[$parentId][] = $row;
  }

  $result .= '<div class="category-tree">';
  $result .= displayCategoryTree($categories);
  $result .= '</div>';

  // Add JavaScript for tree interaction
  $result .= '
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Initialize all toggle buttons
      document.querySelectorAll(".toggle-btn").forEach(btn => {
        btn.addEventListener("click", function(e) {
          e.preventDefault();
          const li = this.closest(".category-item");
          li.classList.toggle("expanded");
          const icon = this.querySelector(".toggle-icon");
          icon.textContent = li.classList.contains("expanded") ? "▼" : "▶";
        });
      });
    });
  </script>';

  $result .= '</div>';
  return $result;
}

function displayCategoryTree($categories, $parentId = '0', $level = 0)
{
  if (!isset($categories[$parentId])) {
    return '';
  }

  $output = '<ul class="category-list' . ($level === 0 ? ' root-level' : '') . '">';
  foreach ($categories[$parentId] as $category) {
    $hasChildren = isset($categories[$category['id']]);

    $output .= '<li class="category-item' . ($hasChildren ? ' has-children' : '') . '">';
    $output .= '<div class="category-content">';
    $output .= '<div class="category-main">';
    
    if ($hasChildren) {
      $output .= '<button type="button" class="toggle-btn">
                   <span class="toggle-icon">▶</span>
                 </button>';
    } else {
      $output .= '<span class="toggle-btn" style="visibility: hidden">
                   <span class="toggle-icon">▶</span>
                 </span>';
    }
    
    $output .= '<span class="category-name">' . htmlspecialchars($category['name']) . '</span>';
    $output .= '</div>';

    $output .= '<div class="category-actions">';
    $output .= '<a href="?action=edit_category&category_id=' . htmlspecialchars($category['id']) . '" 
                  class="btn btn-primary btn-sm">Edytuj</a>';
    $output .= '<a href="?action=delete_category&category_id=' . htmlspecialchars($category['id']) . '" 
                  class="btn btn-danger btn-sm" 
                  onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">Usuń</a>';
    $output .= '</div>';
    $output .= '</div>';

    if ($hasChildren) {
      $output .= displayCategoryTree($categories, $category['id'], $level + 1);
    }

    $output .= '</li>';
  }
  $output .= '</ul>';
  return $output;
}

/**
 * Displays the edit form for a specific category
 * 
 * @param mysqli $link Database connection object
 * @return string HTML content of the category edit form
 */
function EdytujKategorie($link)
{
  if (!isset($_GET['category_id'])) {
    return '<div class="alert alert-error">Nie wybrano kategorii do edycji</div>';
  }

  $id = filter_var($_GET['category_id'], FILTER_VALIDATE_INT);

  if ($id === false) {
    return '<div class="alert alert-error">Nieprawidłowe ID kategorii</div>';
  }

  $query = "SELECT * FROM category WHERE id = ? LIMIT 1";
  $stmt = mysqli_prepare($link, $query);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $category = mysqli_fetch_assoc($result);

  if (!$category) {
    return '<div class="alert alert-error">Nie znaleziono kategorii o podanym ID</div>';
  }

  $form = '
    <div class="card">
        <h2>Edycja Kategorii</h2>
        <form method="POST">
            <div class="form-group">
                <label for="category_name">Nazwa kategorii:</label>
                <input type="text" class="form-control" id="category_name" name="category_name" 
                       value="' . htmlspecialchars($category['name']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="parent_category">Kategoria nadrzędna:</label>
                <select class="form-control" id="parent_category" name="parent_category">
                    ' . getCategoryOptions($link, $category['category_id'], $category['id']) . '
                </select>
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="save_category" class="btn btn-success">Zapisz zmiany</button>
                <a href="admin.php?action=categories" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>';

  return $form;
}

/**
 * Displays the form for adding a new category
 * 
 * @param mysqli $link Database connection object
 * @return string HTML content of the category add form
 */
function DodajNowaKategorie($link)
{
  $form = '
    <div class="card">
        <h2>Dodaj Nową Kategorię</h2>
        <form method="POST">
            <div class="form-group">
                <label for="category_name">Nazwa kategorii:</label>
                <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            
            <div class="form-group">
                <label for="parent_category">Kategoria nadrzędna:</label>
                <select class="form-control" id="parent_category" name="parent_category">
                    ' . getCategoryOptions($link) . '
                </select>
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="add_category" class="btn btn-success">Dodaj kategorię</button>
                <a href="admin.php?action=categories" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>';

  return $form;
}

function getCategoryOptions($link, $selectedId = null, $excludeId = null, $isRequired = false)
{
  $query = "SELECT * FROM category ORDER BY name ASC";
  $result = mysqli_query($link, $query);
  $options = '';

  if (!$isRequired) {
    $options .= '<option value="">-- Kategoria nadrzędna (opcjonalnie) --</option>';
  }

  while ($row = mysqli_fetch_assoc($result)) {
    if ($excludeId !== null && $row['id'] == $excludeId) {
      continue;
    }

    $selected = ($selectedId !== null && $row['id'] == $selectedId) ? 'selected' : '';
    $options .= '<option value="' . htmlspecialchars($row['id']) . '" ' . $selected . '>';
    $options .= htmlspecialchars($row['name']);
    $options .= '</option>';
  }
  return $options;
}
