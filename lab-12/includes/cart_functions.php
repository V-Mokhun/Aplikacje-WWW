<?php
if (!isset($_SESSION)) {
    session_start();
}

/**
 * Initialize cart in session if it doesn't exist
 */
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add product to cart
 */
function addToCart($link, $product_id, $quantity = 1) {
    initCart();
    
    // Validate product
    $query = "SELECT * FROM products WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    
    if (!$product) {
        return ['status' => 'error', 'message' => 'Produkt nie istnieje'];
    }
    
    // Check stock
    if ($product['stock_quantity'] < $quantity) {
        return ['status' => 'error', 'message' => 'Niewystarczająca ilość produktu w magazynie'];
    }
    
    // Add to cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'title' => $product['title'],
            'quantity' => $quantity,
            'net_price' => $product['net_price'],
            'vat_rate' => $product['vat_rate']
        ];
    }
    
    return ['status' => 'success', 'message' => 'Produkt dodany do koszyka'];
}

/**
 * Remove product from cart
 */
function removeFromCart($product_id) {
    initCart();
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return ['status' => 'success', 'message' => 'Produkt usunięty z koszyka'];
    }
    
    return ['status' => 'error', 'message' => 'Produkt nie znajduje się w koszyku'];
}

/**
 * Update product quantity in cart
 */
function updateCartQuantity($link, $product_id, $quantity) {
    initCart();
    
    if (!isset($_SESSION['cart'][$product_id])) {
        return ['status' => 'error', 'message' => 'Produkt nie znajduje się w koszyku'];
    }
    
    // Validate quantity
    if ($quantity < 1) {
        return removeFromCart($product_id);
    }
    
    // Check stock
    $query = "SELECT stock_quantity FROM products WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    
    if ($product['stock_quantity'] < $quantity) {
        return ['status' => 'error', 'message' => 'Niewystarczająca ilość produktu w magazynie'];
    }
    
    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    return ['status' => 'success', 'message' => 'Ilość produktu została zaktualizowana'];
}

/**
 * Calculate total cart value
 */
function calculateCartTotal() {
    initCart();
    
    $total_net = 0;
    $total_vat = 0;
    $total_gross = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $item_net = $item['net_price'] * $item['quantity'];
        $item_vat = $item_net * ($item['vat_rate'] / 100);
        
        $total_net += $item_net;
        $total_vat += $item_vat;
    }
    
    $total_gross = $total_net + $total_vat;
    
    return [
        'net' => $total_net,
        'vat' => $total_vat,
        'gross' => $total_gross
    ];
}

/**
 * Display shopping cart HTML
 */
function showCart($link) {
    initCart();
    
    $totals = calculateCartTotal();
    
    $output = '<div class="card">';
    $output .= '<h2>Koszyk</h2>';
    
    if (empty($_SESSION['cart'])) {
        $output .= '<p>Twój koszyk jest pusty</p>';
        return $output . '</div>';
    }
    
    $output .= '<table class="table">
                <tr>
                    <th>Produkt</th>
                    <th>Cena netto</th>
                    <th>VAT</th>
                    <th>Cena brutto</th>
                    <th>Ilość</th>
                    <th>Suma</th>
                    <th>Akcje</th>
                </tr>';
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $item_net = $item['net_price'] * $item['quantity'];
        $item_vat = $item_net * ($item['vat_rate'] / 100);
        $item_gross = $item_net + $item_vat;
        
        $output .= '<tr>
                    <td>' . htmlspecialchars($item['title']) . '</td>
                    <td>' . number_format($item['net_price'], 2) . ' PLN</td>
                    <td>' . $item['vat_rate'] . '%</td>
                    <td>' . number_format($item['net_price'] * (1 + $item['vat_rate']/100), 2) . ' PLN</td>
                    <td>
                        <form method="POST" class="quantity-form">
                            <input type="hidden" name="action" value="update_quantity">
                            <input type="hidden" name="product_id" value="' . $product_id . '">
                            <input type="number" name="quantity" value="' . $item['quantity'] . '" min="1" 
                                   onchange="this.form.submit()" style="width: 60px">
                        </form>
                    </td>
                    <td>' . number_format($item_gross, 2) . ' PLN</td>
                    <td>
                        <form method="POST" style="display: inline">
                            <input type="hidden" name="action" value="remove_from_cart">
                            <input type="hidden" name="product_id" value="' . $product_id . '">
                            <button type="submit" class="btn btn-danger btn-sm">Usuń</button>
                        </form>
                    </td>
                </tr>';
    }
    
    $output .= '</table>';
    
    $output .= '<div class="cart-summary">
                    <p><strong>Suma netto:</strong> ' . number_format($totals['net'], 2) . ' PLN</p>
                    <p><strong>Suma VAT:</strong> ' . number_format($totals['vat'], 2) . ' PLN</p>
                    <p><strong>Suma brutto:</strong> ' . number_format($totals['gross'], 2) . ' PLN</p>
                </div>';
    
    $output .= '</div>';
    
    return $output;
} 
