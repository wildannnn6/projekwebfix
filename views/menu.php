<?php
session_start();
include_once '../controllers/AuthController.php';
$auth = new AuthController();
$auth->checkRole(['super_admin', 'staff']);

include_once '../config/database.php';
include_once '../models/Menu.php';
include_once '../models/Category.php';

$database = new Database();
$db = $database->getConnection();
$menu = new Menu($db);
$category = new Category($db);

if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $menu->name = $_POST['name'];
                $menu->description = $_POST['description'];
                $menu->price = $_POST['price'];
                $menu->category_id = $_POST['category_id'];
                $menu->status = $_POST['status'];
                
                if($menu->create()) {
                    $success = "Menu berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan menu!";
                }
                break;
                
            case 'update':
                $menu->id = $_POST['id'];
                $menu->name = $_POST['name'];
                $menu->description = $_POST['description'];
                $menu->price = $_POST['price'];
                $menu->category_id = $_POST['category_id'];
                $menu->status = $_POST['status'];
                
                if($menu->update()) {
                    $success = "Menu berhasil diupdate!";
                } else {
                    $error = "Gagal mengupdate menu!";
                }
                break;
                
            case 'delete':
                $menu->id = $_POST['id'];
                if($menu->delete()) {
                    $success = "Menu berhasil dihapus!";
                } else {
                    $error = "Gagal menghapus menu!";
                }
                break;
        }
    }
}

$stmt = $menu->readAll();
$categories = $category->readAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Aneka Rasa</title>
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
                            <a class="nav-link active" href="menu.php">
                                <i class="fas fa-utensils me-2"></i>Menu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
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
                    <h1 class="h2">Kelola Menu</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuModal">
                        <i class="fas fa-plus me-2"></i>Tambah Menu
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

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Menu</th>
                                        <th>Deskripsi</th>
                                        <th>Harga</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['status'] == 'tersedia' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editMenu(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteMenu(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                                                <i class="fas fa-trash"></i>
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

    <div class="modal fade" id="addMenuModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Harga</label>
                            <input type="number" class="form-control" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-control" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php 
                                $categories->execute();
                                while($cat = $categories->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="tersedia">tersedia</option>
                                <option value="tidak_tersedia">tidak tersedia</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editMenuModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editMenuForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Harga</label>
                            <input type="number" class="form-control" name="price" id="edit_price" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category_id" class="form-label">Kategori</label>
                            <select class="form-control" name="category_id" id="edit_category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php 
                                $categories->execute();
                                while($cat = $categories->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-control" name="status" id="edit_status" required>
                                <option value="tersedia">tersedia</option>
                                <option value="tidak_tersedia">tidak_tersedia</option>
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

    <div class="modal fade" id="deleteMenuModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus menu <strong id="delete_menu_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editMenu(menu) {
            document.getElementById('edit_id').value = menu.id;
            document.getElementById('edit_name').value = menu.name;
            document.getElementById('edit_description').value = menu.description;
            document.getElementById('edit_price').value = menu.price;
            document.getElementById('edit_category_id').value = menu.category_id;
            document.getElementById('edit_status').value = menu.status;
            
            var editModal = new bootstrap.Modal(document.getElementById('editMenuModal'));
            editModal.show();
        }

        function deleteMenu(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_menu_name').textContent = name;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteMenuModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
