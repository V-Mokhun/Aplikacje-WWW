<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('cfg.php');
require_once('includes/cart_functions.php');

/**
 * Filter products based on search criteria
 */
function getFilteredProducts($link)
{
  $where_clauses = [];
  $params = [];
  $types = "";

  // Search by title/description
  if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where_clauses[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
  }

  // Filter by category
  if (!empty($_GET['category'])) {
    $where_clauses[] = "category_id = ?";
    $params[] = $_GET['category'];
    $types .= "i";
  }

  // Filter by price range
  if (!empty($_GET['min_price'])) {
    $where_clauses[] = "net_price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
  }
  if (!empty($_GET['max_price'])) {
    $where_clauses[] = "net_price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
  }

  // Only show available products by default
  if (!isset($_GET['show_unavailable'])) {
    $where_clauses[] = "availability_status = 'available'";
  }

  $query = "SELECT * FROM products";
  if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
  }

  // Add sorting
  $sort = $_GET['sort'] ?? 'title_asc';
  switch ($sort) {
    case 'price_asc':
      $query .= " ORDER BY net_price ASC";
      break;
    case 'price_desc':
      $query .= " ORDER BY net_price DESC";
      break;
    case 'title_desc':
      $query .= " ORDER BY title DESC";
      break;
    default:
      $query .= " ORDER BY title ASC";
  }

  $stmt = mysqli_prepare($link, $query);
  if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }
  mysqli_stmt_execute($stmt);
  return mysqli_stmt_get_result($stmt);
}

/**
 * Display filter form
 */
function showFilterForm($link)
{
  $categories = mysqli_query($link, "SELECT * FROM category ORDER BY name");

  $output = '<div class="filter-form">
        <form method="GET" class="filters">
            <div class="form-group">
                <label for="search">Wyszukaj</label>
                <input type="text" id="search" name="search" placeholder="Szukaj produktów..." 
                       value="' . htmlspecialchars($_GET['search'] ?? '') . '">
            </div>
            
            <div class="form-group">
                <label for="category">Kategoria</label>
                <select id="category" name="category">
                    <option value="">Wszystkie kategorie</option>';

  while ($cat = mysqli_fetch_assoc($categories)) {
    $selected = ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : '';
    $output .= '<option value="' . $cat['id'] . '" ' . $selected . '>'
      . htmlspecialchars($cat['name']) . '</option>';
  }

  $output .= '</select>
            </div>
            
            <div class="form-group">
                <label for="sort">Sortowanie</label>
                <select id="sort" name="sort">
                    <option value="title_asc">Nazwa A-Z</option>
                    <option value="title_desc">Nazwa Z-A</option>
                    <option value="price_asc">Cena rosnąco</option>
                    <option value="price_desc">Cena malejąco</option>
                </select>
            </div>
            
            <div class="price-range">
                <div class="form-group">
                    <label for="min_price">Cena od</label>
                    <input type="number" id="min_price" name="min_price" placeholder="0.00" step="0.01" 
                           value="' . htmlspecialchars($_GET['min_price'] ?? '') . '">
                </div>
                <div class="form-group">
                    <label for="max_price">Cena do</label>
                    <input type="number" id="max_price" name="max_price" placeholder="0.00" step="0.01" 
                           value="' . htmlspecialchars($_GET['max_price'] ?? '') . '">
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="show_unavailable" name="show_unavailable" value="1" 
                           ' . (isset($_GET['show_unavailable']) ? 'checked' : '') . '>
                    <label for="show_unavailable">Pokaż niedostępne produkty</label>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filtruj</button>
                <a href="shop.php" class="btn btn-secondary">Resetuj filtry</a>
            </div>
        </form>
    </div>';

  return $output;
}

/**
 * Display products grid
 */
function showProducts($link)
{
  $products = getFilteredProducts($link);

  $output = '<div class="products-grid">';

  while ($product = mysqli_fetch_assoc($products)) {
    $image_html = '';
    if ($product['image']) {
      $image_data = base64_encode($product['image']);
      $image_type = $product['image_type'];
      $image_html = '<img src="data:' . $image_type . ';base64,' . $image_data . '" 
                               class="product-image" alt="' . htmlspecialchars($product['title']) . '">';
    }

    $gross_price = $product['net_price'] * (1 + $product['vat_rate'] / 100);

    $output .= '<div class="product-card">
            ' . $image_html . '
            <div class="product-details">
                <h3>' . htmlspecialchars($product['title']) . '</h3>
                <p class="product-description">' . htmlspecialchars($product['description']) . '</p>
                <div class="product-price">
                    <span class="price">' . number_format($gross_price, 2) . ' PLN</span>
                    <span class="vat-info">zawiera ' . $product['vat_rate'] . '% VAT</span>
                </div>
                <div class="product-status">
                    <span class="status-badge status-' . $product['availability_status'] . '">
                        ' . ($product['availability_status'] == 'available' ? 'Dostępny' : ($product['availability_status'] == 'unavailable' ? 'Niedostępny' : 'Wkrótce')) . '
                    </span>
                </div>';

    if ($product['availability_status'] == 'available') {
      $output .= '<form method="POST" action="cart.php" class="add-to-cart-form">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="' . $product['id'] . '">
                    <div class="quantity-input">
                        <input type="number" name="quantity" value="1" min="1" 
                               max="' . $product['stock_quantity'] . '">
                        <button type="submit" class="btn btn-primary">Dodaj do koszyka</button>
                    </div>
                </form>';
    }

    $output .= '</div></div>';
  }

  if (mysqli_num_rows($products) == 0) {
    $output .= '<p class="no-results">Nie znaleziono produktów spełniających kryteria.</p>';
  }

  $output .= '</div>';

  return $output;
}

// Display cart status if not empty
function showCartStatus()
{
  initCart();
  $totals = calculateCartTotal();
  $item_count = array_sum(array_column($_SESSION['cart'], 'quantity'));

  return '<div class="cart-status">
            <a href="cart.php" class="cart-link">
                <span class="cart-icon">🛒</span>
                <span class="cart-count">' . $item_count . '</span>
                <span class="cart-total">' . number_format($totals['gross'], 2) . ' PLN</span>
            </a>
        </div>';
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sklep</title>
  <link rel="stylesheet" href="css/admin.css">
  <link rel="stylesheet" href="css/shop.css">
</head>

<body>
  <div class="shop-container">
    <header class="shop-header">
      <a href="index.php" class="btn btn-secondary">Powrót do strony głównej</a>
      <div class="shop-header-content">
        <h1>Sklep</h1>
        <?php echo showCartStatus(); ?>
      </div>
    </header>

    <?php
    echo showFilterForm($link);
    echo showProducts($link);
    ?>
  </div>
</body>

</html>
