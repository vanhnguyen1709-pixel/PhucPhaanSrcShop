<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            $owner = $_GET['owner'] ?? null;
            $products = getAllProducts($owner);
            foreach ($products as &$p) {
                $p['packages'] = getPackagesByProductId($p['id']);
            }
            echo json_encode(['success' => true, 'data' => $products]);
        } elseif ($action === 'get' && isset($_GET['id'])) {
            $product = getProductWithPackages($_GET['id']);
            echo json_encode(['success' => true, 'data' => $product]);
        } elseif ($action === 'categories') {
            $owner = $_GET['owner'] ?? null;
            echo json_encode(['success' => true, 'data' => getAllCategories($owner)]);
        }
        break;
        
    case 'POST':
        $admin = requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        if ($action === 'create') {
            $packages = $data['packages'] ?? [];
            unset($data['packages']);
            $data['owner'] = $admin['username'];
            $id = createProduct($data, $packages);
            echo json_encode(['success' => true, 'id' => $id]);
        }
        break;
        
    case 'PUT':
        $admin = requireAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        if ($action === 'update' && isset($_GET['id'])) {
            $product = getProductById($_GET['id']);
            if (!canManageProduct($admin, $product)) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                exit;
            }
            $packages = $data['packages'] ?? [];
            unset($data['packages']);
            updateProduct($_GET['id'], $data, $packages);
            echo json_encode(['success' => true]);
        }
        break;
        
    case 'DELETE':
        $admin = requireAdmin();
        if (isset($_GET['id'])) {
            $product = getProductById($_GET['id']);
            if (!canManageProduct($admin, $product)) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                exit;
            }
            deleteProduct($_GET['id']);
            echo json_encode(['success' => true]);
        }
        break;
}