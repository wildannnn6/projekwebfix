<?php
session_start();
include_once '../controllers/AuthController.php';
$auth = new AuthController();
$auth->checkRole(['super_admin', 'staff']);

include_once '../config/database.php';
include_once '../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

if($_POST && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $order->id = $_POST['order_id'];
    $order->status = $_POST['status'];
    
    if($order->updateStatus()) {
        $success = "Status pesanan berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate status pesanan!";
    }
}

$stmt = $order->readAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Aneka Rasa</title>
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
                        
                        <?php if($_SESSION['role'] == 'super_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Kelola User
                            </a>
                        </li>
                        <?php endif; ?>
                        
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
                            <a class="nav-link active" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i>Pesanan
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
                    <h1 class="h2">Kelola Pesanan</h1>
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

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Customer</th>
                                        <th>No. Telepon</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>User</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                        <td>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $row['status'] == 'ditunda' ? 'warning' : 
                                                ($row['status'] == 'dikonfirmasi' ? 'info' : 
                                                ($row['status'] == 'disiapkan' ? 'primary' : 
                                                ($row['status'] == 'siap' ? 'success' : 
                                                ($row['status'] == 'diantar' ? 'success' : 'danger')))); 
                                            ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="updateStatus(<?php echo $row['id']; ?>, '<?php echo $row['status']; ?>')">
                                                <i class="fas fa-edit"></i> Status
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" id="order_id">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="ditunda">ditunda</option>
                                <option value="dikonfirmasi">dikonfirmasi</option>
                                <option value="disiapkan">disiapkan</option>
                                <option value="siap">siap</option>
                                <option value="diantar">diantar</option>
                                <option value="batal">batal</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(orderId, currentStatus) {
            document.getElementById('order_id').value = orderId;
            document.getElementById('status').value = currentStatus;
            
            var modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        }
    </script>
</body>
</html>
