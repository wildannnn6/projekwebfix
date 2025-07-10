<?php
session_start();
include_once '../controllers/AuthController.php';
$auth = new AuthController();
$auth->checkAuth();

include_once '../config/database.php';
include_once '../models/Menu.php';
include_once '../models/Order.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);
$order = new Order($db);
$user = new User($db);

$total_menu = $menu->readAll()->rowCount();
$total_orders = $order->readAll()->rowCount();
$total_users = $user->readAll()->rowCount();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aneka Rasa</title>
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
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
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
                        <small class="text-white-50">Selamat datang, <?php echo $_SESSION['full_name']; ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        
                        <?php if($_SESSION['role'] == 'super_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Kelola User
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'staff'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags me-2"></i>Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="menu.php">
                                <i class="fas fa-utensils me-2"></i>Menu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i>Pesanan
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if($_SESSION['role'] == 'user'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="order_menu.php">
                                <i class="fas fa-shopping-cart me-2"></i>Pesan Makanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_orders.php">
                                <i class="fas fa-history me-2"></i>Pesanan Saya
                            </a>
                        </li>
                        <?php endif; ?>
                        
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="badge bg-primary"><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <?php if($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'staff'): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card stats-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_menu; ?></h4>
                                        <p class="mb-0">Total Menu</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-utensils fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card stats-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_orders; ?></h4>
                                        <p class="mb-0">Total Pesanan</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-shopping-cart fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($_SESSION['role'] == 'super_admin'): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card stats-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_users; ?></h4>
                                        <p class="mb-0">Total User</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3>Selamat Datang di Sistem Aneka Rasa!</h3>
                                <p class="lead">Anda login sebagai: <strong><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?></strong></p>
                                
                                <?php if($_SESSION['role'] == 'super_admin'): ?>
                                    <p>Sebagai Super Admin, Anda memiliki akses penuh untuk mengelola semua data sistem.</p>
                                <?php elseif($_SESSION['role'] == 'staff'): ?>
                                    <p>Sebagai Staff, Anda dapat mengelola menu, kategori, dan pesanan pelanggan.</p>
                                <?php else: ?>
                                    <p>Sebagai Customer, Anda dapat memesan makanan dan melihat riwayat pesanan Anda.</p>
                                <?php endif; ?>
                                
                                <div class="mt-4">
                                    <?php if($_SESSION['role'] == 'user'): ?>
                                        <a href="order_menu.php" class="btn btn-primary btn-lg">
                                            <i class="fas fa-shopping-cart me-2"></i>Mulai Pesan
                                        </a>
                                    <?php else: ?>
                                        <a href="menu.php" class="btn btn-primary btn-lg">
                                            <i class="fas fa-utensils me-2"></i>Kelola Menu
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
