<?php 
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

$searchQuery = trim($_GET['searchQuery'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Products Management System</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
            <a href="core/handleForms.php?logoutUser=1" class="link-delete" style="text-decoration: none; padding: 6px 12px; border-radius: 4px;">Logout</a>
        </div>

        <h1>Digital Products Management System</h1>

        <h2>Add New Designer</h2>
        <div class="card">
            <form action="core/handleForms.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="userName" placeholder="e.g. jdoe_design" required>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstName" placeholder="e.g. Jane" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastName" placeholder="e.g. Doe" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dateOfBirth">
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="specialization" placeholder="e.g. Graphic Design, UI/UX, 3D Assets">
                    </div>
                </div>

                <input type="submit" name="insertDesignerBtn" value="Add Designer" class="btn-submit">
            </form>
        </div>

        <div class="section-header-bar">
            <h2>Designer List</h2>
            <form action="index.php" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="searchQuery" 
                    placeholder="Search username, name, specialization..." 
                    value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <button type="submit" class="btn-search">Search</button>
                <?php if ($searchQuery !== ''): ?>
                    <a href="index.php" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Specialization</th>
                        <th>Date Added</th>
                        <th>Added By</th>
                        <th>Last Updated By</th>
                        <th>Last Updated</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $designers = ($searchQuery !== '') 
                            ? searchDesigners($pdo, $searchQuery) 
                            : getAllDesigners($pdo); 
                    ?>
                    <?php if (!empty($designers)): ?>
                        <?php foreach ($designers as $row): ?>
                        <tr>
                            <td><?php echo (int)$row['designer_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['specialization'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php 
                                    echo !empty($row['date_added']) 
                                        ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['date_added'])), ENT_QUOTES, 'UTF-8') 
                                        : '-'; 
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
                                    <a href="viewProducts.php?designerId=<?php echo (int)$row['designer_id']; ?>" class="link-view">View Products</a>
                                    <a href="editDesigner.php?designerId=<?php echo (int)$row['designer_id']; ?>" class="link-edit">Edit</a>
                                    <a href="deleteDesigner.php?designerId=<?php echo (int)$row['designer_id']; ?>" class="link-delete">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted);">
                                <?php echo ($searchQuery !== '') ? 'No designers matching "' . htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') . '".' : 'No designers found.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>