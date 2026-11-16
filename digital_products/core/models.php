<?php
require_once 'dbConfig.php';

// =========================================
// USER AUTHENTICATION FUNCTIONS
// =========================================

function registerUser($pdo, $userName, $firstName, $lastName, $password) {
    $checkSql = "SELECT * FROM user_passwords WHERE user_name = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$userName]);
    
    if ($checkStmt->rowCount() > 0) {
        return 'taken';
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user_passwords (user_name, first_name, last_name, password) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$userName, $firstName, $lastName, $hashedPassword]);
}

function loginUser($pdo, $userName, $password) {
    $sql = "SELECT * FROM user_passwords WHERE user_name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userName]);
    
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        if (password_verify($password, $row['password'])) {
            return true;
        }
    }
    return false;
}

// =========================================
// DESIGNER (PARENT) FUNCTIONS
// =========================================

function getAllDesigners($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM designer ORDER BY designer_id DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function searchDesigners($pdo, $searchQuery) {
    $sql = "SELECT * FROM designer 
            WHERE user_name LIKE ? 
               OR first_name LIKE ? 
               OR last_name LIKE ? 
               OR CONCAT(first_name, ' ', last_name) LIKE ? 
               OR specialization LIKE ? 
               OR added_by LIKE ?
            ORDER BY designer_id DESC";
    $stmt = $pdo->prepare($sql);
    $term = "%" . $searchQuery . "%";
    $stmt->execute([$term, $term, $term, $term, $term, $term]);
    return $stmt->fetchAll();
}

function getDesignerById($pdo, $designerId) {
    $stmt = $pdo->prepare("SELECT * FROM designer WHERE designer_id = ?");
    $stmt->execute([$designerId]);
    return $stmt->fetch();
}

function insertDesigner($pdo, $userName, $firstName, $lastName, $dateOfBirth, $specialization, $addedBy) {
    $sql = "INSERT INTO designer (user_name, first_name, last_name, date_of_birth, specialization, added_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$userName, $firstName, $lastName, $dateOfBirth, $specialization, $addedBy]);
}

function updateDesigner($pdo, $designerId, $userName, $firstName, $lastName, $dateOfBirth, $specialization, $lastUpdatedBy) {
    $sql = "UPDATE designer SET user_name = ?, first_name = ?, last_name = ?, date_of_birth = ?, specialization = ?, last_updated_by = ? WHERE designer_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$userName, $firstName, $lastName, $dateOfBirth, $specialization, $lastUpdatedBy, $designerId]);
}

function deleteDesigner($pdo, $designerId) {
    $stmt = $pdo->prepare("DELETE FROM designer WHERE designer_id = ?");
    return $stmt->execute([$designerId]);
}

// =========================================
// DIGITAL PRODUCTS (CHILD) FUNCTIONS
// =========================================

function getProductsByDesigner($pdo, $designerId) {
    $sql = "SELECT * FROM digital_products WHERE designer_id = ? ORDER BY digital_products_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$designerId]);
    return $stmt->fetchAll();
}

function searchProductsByDesigner($pdo, $designerId, $searchQuery) {
    $sql = "SELECT * FROM digital_products 
            WHERE designer_id = ? 
              AND (product_name LIKE ? 
                OR product_type LIKE ? 
                OR price LIKE ? 
                OR added_by LIKE ?)
            ORDER BY digital_products_id DESC";
    $stmt = $pdo->prepare($sql);
    $term = "%" . $searchQuery . "%";
    $stmt->execute([$designerId, $term, $term, $term, $term]);
    return $stmt->fetchAll();
}

function getProductById($pdo, $digitalProductId) {
    $stmt = $pdo->prepare("SELECT * FROM digital_products WHERE digital_products_id = ?");
    $stmt->execute([$digitalProductId]);
    return $stmt->fetch();
}

function insertProduct($pdo, $designerId, $productName, $productType, $price, $salePrice, $addedBy) {
    $sql = "INSERT INTO digital_products (designer_id, product_name, product_type, price, sale_price, added_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$designerId, $productName, $productType, $price, $salePrice, $addedBy]);
}

function updateProduct($pdo, $digitalProductId, $productName, $productType, $price, $salePrice, $lastUpdatedBy) {
    $sql = "UPDATE digital_products SET product_name = ?, product_type = ?, price = ?, sale_price = ?, last_updated_by = ? WHERE digital_products_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$productName, $productType, $price, $salePrice, $lastUpdatedBy, $digitalProductId]);
}

function deleteProduct($pdo, $digitalProductId) {
    $stmt = $pdo->prepare("DELETE FROM digital_products WHERE digital_products_id = ?");
    return $stmt->execute([$digitalProductId]);
}
?>