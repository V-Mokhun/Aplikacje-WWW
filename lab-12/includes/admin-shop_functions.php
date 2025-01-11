<?php
require_once 'cfg.php';

/**
 * ===============================
 * Product Management Functions
 * ===============================
 */

function showMessages()
{
  $output = '';

  // Show success message
  if (isset($_GET['success'])) {
    $output .= '<div class="alert alert-success">Operacja zakończona pomyślnie</div>';
  }

  // Show errors
  if (isset($_SESSION['product_errors'])) {
    $output .= '<div class="alert alert-error">';
    foreach ($_SESSION['product_errors'] as $error) {
      $output .= htmlspecialchars($error) . '<br>';
    }
    $output .= '</div>';
    unset($_SESSION['product_errors']);
  }

  return $output;
}

function handleProductSubmissions($link)
{
  $errors = [];

  // Handle product update
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product']) && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
      $errors[] = "Nieprawidłowe ID produktu";
    } else {
      try {
        // Validate required fields
        if (empty($_POST['title'])) $errors[] = "Nazwa produktu jest wymagana";
        if (empty($_POST['description'])) $errors[] = "Opis produktu jest wymagany";
        if (!isset($_POST['net_price'])) $errors[] = "Cena netto jest wymagana";
        if (!isset($_POST['vat_rate'])) $errors[] = "Stawka VAT jest wymagana";
        if (!isset($_POST['stock_quantity'])) $errors[] = "Stan magazynowy jest wymagany";
        if (empty($_POST['category_id'])) $errors[] = "Kategoria jest wymagana";

        if (empty($errors)) {
          $title = mysqli_real_escape_string($link, trim($_POST['title']));
          $description = mysqli_real_escape_string($link, trim($_POST['description']));
          $net_price = filter_var($_POST['net_price'], FILTER_VALIDATE_FLOAT);
          $vat_rate = filter_var($_POST['vat_rate'], FILTER_VALIDATE_FLOAT);
          $stock_quantity = filter_var($_POST['stock_quantity'], FILTER_VALIDATE_INT);
          $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
          $dimensions = mysqli_real_escape_string($link, trim($_POST['dimensions']));
          $expiry_date = !empty($_POST['expiry_date']) ?
            mysqli_real_escape_string($link, trim($_POST['expiry_date'])) : null;
          $availability_status = mysqli_real_escape_string($link, trim($_POST['availability_status']));

          // Handle image upload
          $image_data = null;
          $image_type = null;

          if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
              if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $errors[] = "Rozmiar pliku przekracza 2MB";
              } else {
                $image_data = file_get_contents($_FILES['image']['tmp_name']);
                $image_type = $_FILES['image']['type'];
              }
            } else {
              $errors[] = "Niedozwolony format pliku. Dozwolone formaty: JPG, PNG";
            }
          }

          if (empty($errors)) {
            $query = "UPDATE products SET 
                                    title = ?, 
                                    description = ?,
                                    net_price = ?,
                                    vat_rate = ?,
                                    stock_quantity = ?,
                                    category_id = ?,
                                    dimensions = ?,
                                    expiry_date = ?,
                                    availability_status = ?";

            $params = [
              $title,
              $description,
              $net_price,
              $vat_rate,
              $stock_quantity,
              $category_id,
              $dimensions,
              $expiry_date,
              $availability_status
            ];
            $types = "ssddiiiss";

            if ($image_data !== null) {
              $query .= ", image = ?, image_type = ?";
              $params[] = $image_data;
              $params[] = $image_type;
              $types .= "ss";
            }

            $query .= " WHERE id = ? LIMIT 1";
            $params[] = $id;
            $types .= "i";

            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            if (!mysqli_stmt_execute($stmt)) {
              $errors[] = "Błąd podczas aktualizacji produktu: " . mysqli_error($link);
            } else {
              header("Location: admin.php?action=products&success=1");
              exit();
            }
          }
        }
      } catch (Exception $e) {
        $errors[] = "Wystąpił błąd: " . $e->getMessage();
      }
    }
  }

  // Handle new product addition
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    try {
      // Validate required fields
      if (empty($_POST['title'])) $errors[] = "Nazwa produktu jest wymagana";
      if (empty($_POST['description'])) $errors[] = "Opis produktu jest wymagany";
      if (!isset($_POST['net_price'])) $errors[] = "Cena netto jest wymagana";
      if (!isset($_POST['vat_rate'])) $errors[] = "Stawka VAT jest wymagana";
      if (!isset($_POST['stock_quantity'])) $errors[] = "Stan magazynowy jest wymagany";
      if (empty($_POST['category_id'])) $errors[] = "Kategoria jest wymagana";

      if (empty($errors)) {
        $title = mysqli_real_escape_string($link, trim($_POST['title']));
        $description = mysqli_real_escape_string($link, trim($_POST['description']));
        $net_price = filter_var($_POST['net_price'], FILTER_VALIDATE_FLOAT);
        $vat_rate = filter_var($_POST['vat_rate'], FILTER_VALIDATE_FLOAT);
        $stock_quantity = filter_var($_POST['stock_quantity'], FILTER_VALIDATE_INT);
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $dimensions = mysqli_real_escape_string($link, trim($_POST['dimensions']));
        $expiry_date = !empty($_POST['expiry_date']) ?
          mysqli_real_escape_string($link, trim($_POST['expiry_date'])) : null;
        $availability_status = mysqli_real_escape_string($link, trim($_POST['availability_status']));

        // Handle image upload
        $image_data = null;
        $image_type = null;

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
          $errors[] = "Zdjęcie produktu jest wymagane";
        } else {
          $allowed_types = ['image/jpeg', 'image/png'];
          if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Niedozwolony format pliku. Dozwolone formaty: JPG, PNG";
          } else if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Rozmiar pliku przekracza 2MB";
          } else {
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
            $image_type = $_FILES['image']['type'];
          }
        }

        if (empty($errors)) {
          $query = "INSERT INTO products (
                        title, description, net_price, vat_rate, stock_quantity,
                        category_id, dimensions, expiry_date, availability_status, image, image_type
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

          $stmt = mysqli_prepare($link, $query);
          mysqli_stmt_bind_param(
            $stmt,
            "ssddiiissss",
            $title,
            $description,
            $net_price,
            $vat_rate,
            $stock_quantity,
            $category_id,
            $dimensions,
            $expiry_date,
            $availability_status,
            $image_data,
            $image_type
          );

          if (!mysqli_stmt_execute($stmt)) {
            $errors[] = "Błąd podczas dodawania produktu: " . mysqli_error($link);
          } else {
            header("Location: admin.php?action=products&success=1");
            exit();
          }
        }
      }
    } catch (Exception $e) {
      $errors[] = "Wystąpił błąd: " . $e->getMessage();
    }
  }

  // Handle product deletion
  if (isset($_GET['action']) && $_GET['action'] === 'delete_product' && isset($_GET['id'])) {
    try {
      $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
      if ($id === false) {
        $errors[] = "Nieprawidłowe ID produktu";
      } else {
        $query = "DELETE FROM products WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
          $errors[] = "Błąd podczas usuwania produktu: " . mysqli_error($link);
        } else {
          header("Location: admin.php?action=products&success=1");
          exit();
        }
      }
    } catch (Exception $e) {
      $errors[] = "Wystąpił błąd: " . $e->getMessage();
    }
  }

  // If there are any errors, store them in session to display them
  if (!empty($errors)) {
    $_SESSION['product_errors'] = $errors;
    // Redirect back to the form
    $redirect_url = 'admin.php?action=' . ($_GET['action'] ?? 'products');
    if (isset($_GET['id'])) {
      $redirect_url .= '&id=' . $_GET['id'];
    }
    header("Location: $redirect_url");
    exit();
  }
}

function ListaProduktow($link)
{
  $result = showMessages();
  $result .= '<div class="card">';
  $result .= '<h2>Lista Produktów</h2>';
  $result .= '<a href="?action=add_product" class="btn btn-success">Dodaj nowy produkt</a>';
  $result .= '<table class="table">
                <tr>
                    <th>ID</th>
                    <th>Zdjęcie</th>
                    <th>Tytuł</th>
                    <th>Cena</th>
                    <th>Stan magazynowy</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>';

  $query = "SELECT * FROM products ORDER BY id ASC";
  $products = mysqli_query($link, $query);

  while ($row = mysqli_fetch_array($products)) {
    $image_html = '';
    if ($row['image']) {
      $image_data = base64_encode($row['image']);
      $image_type = $row['image_type'];
      $image_html = '<img src="data:' . $image_type . ';base64,' . $image_data . '" class="product-image" alt="">';
    }

    $status_class = 'status-' . $row['availability_status'];
    $status_text = [
      'available' => 'Dostępny',
      'unavailable' => 'Niedostępny',
      'coming_soon' => 'Wkrótce dostępny'
    ][$row['availability_status']] ?? $row['availability_status'];

    $result .= '<tr>
                    <td>' . htmlspecialchars($row['id']) . '</td>
                    <td>' . $image_html . '</td>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . number_format($row['net_price'] * (1 + $row['vat_rate'] / 100), 2) . ' PLN</td>
                    <td>' . htmlspecialchars($row['stock_quantity']) . '</td>
                    <td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>
                    <td class="product-actions">
                        <a href="?action=edit_product&id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary btn-sm">Edytuj</a>
                        <a href="?action=delete_product&id=' . htmlspecialchars($row['id']) . '" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm(\'Czy na pewno chcesz usunąć?\')">Usuń</a>
                    </td>
                </tr>';
  }

  $result .= '</table></div>';
  return $result;
}

function EdytujProdukt($link)
{
  if (!isset($_GET['id'])) {
    return '<div class="alert alert-error">Nie wybrano produktu do edycji</div>';
  }

  $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
  if ($id === false) {
    return '<div class="alert alert-error">Nieprawidłowe ID produktu</div>';
  }

  $query = "SELECT * FROM products WHERE id = ? LIMIT 1";
  $stmt = mysqli_prepare($link, $query);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $product = mysqli_fetch_assoc($result);

  if (!$product) {
    return '<div class="alert alert-error">Nie znaleziono produktu o podanym ID</div>';
  }

  // Add current image preview if exists
  $current_image = '';
  if ($product['image']) {
    $image_data = base64_encode($product['image']);
    $image_type = $product['image_type'];
    $current_image = '<div class="current-image">
            <p>Aktualne zdjęcie:</p>
            <img src="data:' . $image_type . ';base64,' . $image_data . '" alt="Current product image" style="max-width: 200px;">
        </div>';
  }

  $form = '
    <div class="card">
        <h2>Edycja Produktu</h2>
        <form method="POST" class="product-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Nazwa produktu:</label>
                <input type="text" id="title" name="title" value="' . htmlspecialchars($product['title']) . '" required>
            </div>
            
            <div class="form-group">
                <label for="description">Opis:</label>
                <textarea id="description" name="description" rows="5" required>' . htmlspecialchars($product['description']) . '</textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="net_price">Cena netto:</label>
                    <input type="number" id="net_price" name="net_price" step="0.01" value="' . htmlspecialchars($product['net_price']) . '" required>
                </div>
                
                <div class="form-group">
                    <label for="vat_rate">VAT (%):</label>
                    <input type="number" id="vat_rate" name="vat_rate" step="0.01" value="' . htmlspecialchars($product['vat_rate']) . '" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="stock_quantity">Stan magazynowy:</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" value="' . htmlspecialchars($product['stock_quantity']) . '" required>
                </div>
                
                <div class="form-group">
                    <label for="availability_status">Status dostępności:</label>
                    <select id="availability_status" name="availability_status" required>
                        <option value="available" ' . ($product['availability_status'] == 'available' ? 'selected' : '') . '>Dostępny</option>
                        <option value="unavailable" ' . ($product['availability_status'] == 'unavailable' ? 'selected' : '') . '>Niedostępny</option>
                        <option value="coming_soon" ' . ($product['availability_status'] == 'coming_soon' ? 'selected' : '') . '>Wkrótce dostępny</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Kategoria:</label>
                    <select id="category_id" name="category_id" required>
                        ' . getCategoryOptions($link, $product['category_id']) . '
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dimensions">Wymiary:</label>
                    <input type="text" id="dimensions" name="dimensions" value="' . htmlspecialchars($product['dimensions']) . '">
                </div>
            </div>
            
            <div class="form-group">
                <label for="expiry_date">Data ważności:</label>
                <input type="date" id="expiry_date" name="expiry_date" value="' . htmlspecialchars($product['expiry_date'] ?? '') . '">
            </div>
            
            <div class="form-group">
                <label for="image">Zdjęcie produktu:</label>
                ' . $current_image . '
                <input type="file" id="image" name="image" accept="image/jpeg, image/png" class="form-control">
                <small class="form-text">Dozwolone formaty: JPG, PNG. Maksymalny rozmiar: 2MB</small>
                <img id="image-preview" class="image-preview" alt="Image preview">
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="save_product" class="btn btn-success">Zapisz zmiany</button>
                <a href="admin.php?action=products" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById("image").onchange = function() {
        const preview = document.getElementById("image-preview");
        const file = this.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.add("active");
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
            preview.classList.remove("active");
        }
    };
    </script>';

  return $form;
}

function DodajProdukt($link)
{
  $form = '
    <div class="card">
        <h2>Dodaj Nowy Produkt</h2>
        <form method="POST" class="product-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Nazwa produktu:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Opis:</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="net_price">Cena netto:</label>
                    <input type="number" id="net_price" name="net_price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="vat_rate">VAT (%):</label>
                    <input type="number" id="vat_rate" name="vat_rate" step="0.01" value="23" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="stock_quantity">Stan magazynowy:</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" value="0" required>
                </div>
                
                <div class="form-group">
                    <label for="availability_status">Status dostępności:</label>
                    <select id="availability_status" name="availability_status" required>
                        <option value="available">Dostępny</option>
                        <option value="unavailable">Niedostępny</option>
                        <option value="coming_soon">Wkrótce dostępny</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Kategoria:</label>
                    <select id="category_id" name="category_id" required>
                        ' . getCategoryOptions($link, null, null, true) . '
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dimensions">Wymiary:</label>
                    <input type="text" id="dimensions" name="dimensions">
                </div>
            </div>
            
            <div class="form-group">
                <label for="expiry_date">Data ważności:</label>
                <input type="date" id="expiry_date" name="expiry_date">
            </div>
            
            <div class="form-group">
                <label for="image">Zdjęcie produktu:</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png" class="form-control" required>
                <small class="form-text">Dozwolone formaty: JPG, PNG. Maksymalny rozmiar: 2MB</small>
                <img id="image-preview" class="image-preview" alt="Image preview">
            </div>
            
            <div class="form-buttons">
                <button type="submit" name="add_product" class="btn btn-success">Dodaj produkt</button>
                <a href="admin.php?action=products" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById("image").onchange = function() {
        const preview = document.getElementById("image-preview");
        const file = this.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.add("active");
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
            preview.classList.remove("active");
        }
    };
    </script>';

  return $form;
}
