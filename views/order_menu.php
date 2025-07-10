<?php
session_start();
include_once '../controllers/AuthController.php';
$auth = new AuthController();
$auth->checkRole(['user']);

include_once '../config/database.php';
include_once '../models/Menu.php';
include_once '../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$menu = new Menu($db);

if($_POST && isset($_POST['action']) && $_POST['action'] == 'place_order') {
    $order = new Order($db);
    $order->user_id = $_SESSION['user_id'];
    $order->customer_name = $_POST['customer_name'];
    $order->customer_phone = $_POST['customer_phone'];
    $order->total_amount = $_POST['total_amount'];
    $order->status = 'pending';
    
    $order_id = $order->create();
    if($order_id) {
        $cart_items = json_decode($_POST['cart_items'], true);
        foreach($cart_items as $item) {
            $query = "INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }
        $success = "Pesanan berhasil dibuat!";
    } else {
        $error = "Gagal membuat pesanan!";
    }
}

$stmt = $menu->readAvailable();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Makanan - Aneka Rasa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg,rgb(102, 234, 131) 0%,rgb(75, 162, 162) 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            margin: 0.25rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .menu-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .cart-sidebar {
            position: fixed;
            right: -300px;
            top: 0;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            transition: right 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        .cart-sidebar.show {
            right: 0;
        }
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        .cart-overlay.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-utensils text-white" style="font-size: 2rem;"></i>
                        <h4 class="text-white mt-2">Aneka Rasa</h4>
                        <small class="text-white-50"><?php echo $_SESSION['full_name']; ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link active" href="order_menu.php">
                                <i class="fas fa-shopping-cart me-2"></i>Pesan Makanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_orders.php">
                                <i class="fas fa-history me-2"></i>Pesanan Saya
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="../controllers/AuthController.php?action=logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Menu Makanan</h1>
                    <button class="btn btn-primary" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart me-2"></i>Keranjang (<span id="cart-count">0</span>)
                    </button>
                </div>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php 
                    $current_category = '';
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        if($current_category != $row['category_name']):
                            if($current_category != '') echo '</div>';
                            $current_category = $row['category_name'];
                    ?>
                        <div class="col-12 mb-3">
                            <h3 class="text-primary"><?php echo htmlspecialchars($current_category); ?></h3>
                        </div>
                        <div class="row">
                    <?php endif; ?>
                    
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card menu-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($row['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="text-success mb-0">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></h6>
                                    <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php endwhile; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="cart-overlay" id="cart-overlay" onclick="toggleCart()"></div>

    <div class="cart-sidebar" id="cart-sidebar">
        <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Keranjang Belanja</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleCart()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="p-3">
            <div id="cart-items">
                <p class="text-muted text-center">Keranjang kosong</p>
            </div>
            
            <div id="cart-summary" style="display: none;">
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Total: Rp <span id="cart-total">0</span></strong>
                </div>
                <button class="btn btn-success w-100 mt-3" onclick="checkout()">
                    <i class="fas fa-check me-2"></i>Checkout
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Checkout Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="checkoutForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="place_order">
                        <input type="hidden" name="cart_items" id="checkout_cart_items">
                        <input type="hidden" name="total_amount" id="checkout_total">
                        
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" name="customer_phone" required>
                        </div>
                        
                        <div class="border p-3 rounded">
                            <h6>Ringkasan Pesanan:</h6>
                            <div id="checkout_summary"></div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total: Rp <span id="checkout_total_display">0</span></strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Pesan Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];

        function addToCart(item) {
            const existingItem = cart.find(cartItem => cartItem.id === item.id);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: item.id,
                    name: item.name,
                    price: parseFloat(item.price),
                    quantity: 1
                });
            }
            
            updateCartDisplay();
        }

        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateCartDisplay();
        }

        function updateQuantity(itemId, change) {
            const item = cart.find(cartItem => cartItem.id === itemId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromCart(itemId);
                } else {
                    updateCartDisplay();
                }
            }
        }

        function updateCartDisplay() {
            const cartCount = document.getElementById('cart-count');
            const cartItems = document.getElementById('cart-items');
            const cartSummary = document.getElementById('cart-summary');
            const cartTotal = document.getElementById('cart-total');

            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            cartCount.textContent = totalItems;

            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-muted text-center">Keranjang kosong</p>';
                cartSummary.style.display = 'none';
            } else {
                let itemsHtml = '';
                cart.forEach(item => {
                    itemsHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <h6 class="mb-0">${item.name}</h6>
                                <small class="text-muted">Rp ${item.price.toLocaleString('id-ID')}</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, -1)">-</button>
                                <span class="mx-2">${item.quantity}</span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, 1)">+</button>
                                <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                cartItems.innerHTML = itemsHtml;
                cartTotal.textContent = totalAmount.toLocaleString('id-ID');
                cartSummary.style.display = 'block';
            }
        }

        function toggleCart() {
            const cartSidebar = document.getElementById('cart-sidebar');
            const cartOverlay = document.getElementById('cart-overlay');
            
            cartSidebar.classList.toggle('show');
            cartOverlay.classList.toggle('show');
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Keranjang kosong!');
                return;
            }

            const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            document.getElementById('checkout_cart_items').value = JSON.stringify(cart);
            document.getElementById('checkout_total').value = totalAmount;
            document.getElementById('checkout_total_display').textContent = totalAmount.toLocaleString('id-ID');

            let summaryHtml = '';
            cart.forEach(item => {
                summaryHtml += `
                    <div class="d-flex justify-content-between">
                        <span>${item.name} x${item.quantity}</span>
                        <span>Rp ${(item.price * item.quantity).toLocaleString('id-ID')}</span>
                    </div>
                `;
            });
            document.getElementById('checkout_summary').innerHTML = summaryHtml;

            toggleCart();
            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
        }

        <?php if(isset($success)): ?>
        cart = [];
        updateCartDisplay();
        <?php endif; ?>
    </script>
</body>
</html>
