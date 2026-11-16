<?php 
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

$designerId = $_GET['designerId'] ?? null;

if (!$designerId) {
    header("Location: index.php");
    exit();
}

$designer = getDesignerById($pdo, $designerId);
$searchQuery = trim($_GET['searchQuery'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Designer Products</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="container">
        <a href="index.php" class="back-link">← Back to Designer List</a>
        <h1>Digital Products of <?php echo htmlspecialchars(($designer['first_name'] ?? '') . ' ' . ($designer['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>

        <h2>Add New Digital Product</h2>
        <div class="card">
            <form action="core/handleForms.php?designerId=<?php echo (int)$designerId; ?>" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="productName" placeholder="e.g. Canva Planner Template" required>
                    </div>
                    <div class="form-group">
                        <label>Product Type</label>
                        <input type="text" name="productType" placeholder="e.g. Template, Font, Preset" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Sale Price ($)</label>
                        <input type="number" step="0.01" name="salePrice" placeholder="0.00">
                    </div>
                </div>

                <input type="submit" name="insertProductBtn" value="Add Product" class="btn-submit">
            </form>
        </div>

        <div class="section-header-bar">
            <h2>Product List</h2>
            <form action="viewProducts.php" method="GET" class="search-form">
                <input type="hidden" name="designerId" value="<?php echo (int)$designerId; ?>">
                <input 
                    type="text" 
                    name="searchQuery" 
                    placeholder="Search product name, type, price..." 
                    value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <button type="submit" class="btn-search">Search</button>
                <?php if ($searchQuery !== ''): ?>
                    <a href="viewProducts.php?designerId=<?php echo (int)$designerId; ?>" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Product Type</th>
                        <th>Price</th>
                        <th>Sale Price</th>
                        <th>Date Added</th>
                        <th>Added By</th>
                        <th>Last Updated By</th>
                        <th>Last Updated</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $products = ($searchQuery !== '') 
                            ? searchProductsByDesigner($pdo, $designerId, $searchQuery) 
                            : getProductsByDesigner($pdo, $designerId); 
                    ?>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?php echo (int)$row['digital_products_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['product_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['product_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>$<?php echo number_format((float)($row['price'] ?? 0), 2); ?></td>
                            <td>$<?php echo !empty($row['sale_price']) ? number_format((float)$row['sale_price'], 2) : '-'; ?></td>
                            <td>
                                <?php 
                                    echo !empty($row['date_added']) 
                                        ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['date_added'])), ENT_QUOTES, 'UTF-8') 
                                        : (!empty($row['order_created']) ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['order_created'])), ENT_QUOTES, 'UTF-8') : '-'); 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['added_by'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['last_updated_by'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php 
                                    echo !empty($row['last_updated']) 
                                        ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['last_updated'])), ENT_QUOTES, 'UTF-8') 
                                        : '-'; 
                                ?>
                            </td>
                            <td style="text-align: center;">
                                <div class="action-links">
                                    <a href="editProduct.php?digitalProductId=<?php echo (int)$row['digital_products_id']; ?>&designerId=<?php echo (int)$designerId; ?>" class="link-edit">Edit</a>
                                    <button 
                                        type="button" 
                                        class="link-delete" 
                                        data-id="<?php echo (int)$row['digital_products_id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($row['product_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        onclick="openDeleteModal(this)"
                                    >Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; color: var(--text-muted);">
                                <?php echo ($searchQuery !== '') ? 'No products matching "' . htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') . '".' : 'No products found for this designer.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CUSTOM DELETE CONFIRMATION MODAL -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Delete Product</h3>
            <p>Are you sure you want to delete <strong id="modalProductName" style="color: var(--text-dark);"></strong>? This action cannot be undone.</p>
            
            <form id="deleteForm" action="" method="POST">
                <div class="modal-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" name="deleteProductBtn" class="btn-modal-delete">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(button) {
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const productNameSpan = document.getElementById('modalProductName');

            productNameSpan.textContent = productName;
            form.action = `core/handleForms.php?digitalProductId=${encodeURIComponent(productId)}&designerId=<?php echo (int)$designerId; ?>`;
            
            modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        };
    </script>

</body>
</html>