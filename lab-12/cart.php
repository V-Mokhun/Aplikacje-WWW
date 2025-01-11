<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('cfg.php');
require_once('includes/cart_functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $product_id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);

  switch ($action) {
    case 'add_to_cart':
      $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
      $result = addToCart($link, $product_id, $quantity);
      break;

    case 'remove_from_cart':
      $result = removeFromCart($product_id);
      break;

    case 'update_quantity':
      $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
      $result = updateCartQuantity($link, $product_id, $quantity);
      break;
  }

  // Redirect back to cart page
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit();
}

?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Koszyk</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="content-container">
    <?php echo showCart($link); ?>
    <div class="cart-actions">
      <a href="shop.php" class="btn btn-secondary">Kontynuuj zakupy</a>
    </div>
  </div>
</body>

</html>
