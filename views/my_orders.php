<?php
session_start();
include_once '../controllers/AuthController.php';
$auth = new AuthController();
$auth->checkRole(['user']);

include_once '../config/database.php';
include_once '../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$stmt = $order->readByUser($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Aneka Rasa</title>
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
                            <a class="nav-link" href="order_menu.php">
                                <i class="fas fa-shopping-cart me-2"></i>Pesan Makanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="my_orders.php">
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
                    <h1 class="h2">Pesanan Saya</h1>
                    <a href="order_menu.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Pesan Lagi
                    </a>
                </div>

                <div class="row">
                    <?php if($stmt->rowCount() == 0): ?>
                        <div class="col-12">
                            <div class="card text-center">
                                <div class="card-body py-5">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <h4>Belum Ada Pesanan</h4>
                                    <p class="text-muted">Anda belum pernah melakukan pesanan. Mulai pesan makanan favorit Anda!</p>
                                    <a href="order_menu.php" class="btn btn-primary">
                                        <i class="fas fa-utensils me-2"></i>Mulai Pesan
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Pesanan #<?php echo $row['id']; ?></h6>
                                    <span class="badge bg-<?php 
                                        echo $row['status'] == 'ditunda' ? 'warning' : 
                                            ($row['status'] == 'dikonfirmasi' ? 'info' : 
                                            ($row['status'] == 'disiapkan' ? 'primary' : 
                                            ($row['status'] == 'siap' ? 'success' : 
                                            ($row['status'] == 'diantar' ? 'success' : 'danger')))); 
                                    ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Nama:</strong> <?php echo htmlspecialchars($row['customer_name']); ?><br>
                                        <strong>Telepon:</strong> <?php echo htmlspecialchars($row['customer_phone']); ?><br>
                                        <strong>Total:</strong> Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?><br>
                                        <strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?>
                                    </p>
                                    
                                    <?php
                                    $order_items_query = "SELECT oi.quantity, oi.price, m.name 
                                                         FROM order_items oi 
                                                         JOIN menu m ON oi.menu_id = m.id 
                                                         WHERE oi.order_id = ?";
                                    $items_stmt = $db->prepare($order_items_query);
                                    $items_stmt->execute([$row['id']]);
                                    ?>
                                    
                                    <div class="mt-3">
                                        <h6>Detail Pesanan:</h6>
                                        <ul class="list-unstyled small">
                                            <?php while($item = $items_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                            <li>• <?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?> 
                                                (Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>)</li>
                                            <?php endwhile; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
