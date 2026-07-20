-- ============================================================
-- CHEATING GAME VN - DATABASE
-- Hệ thống shop bán file/key tự động
-- ============================================================

-- Tạo database
CREATE DATABASE IF NOT EXISTS cheating_game_vn
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cheating_game_vn;

-- ============================================================
-- BẢNG ADMINS
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id VARCHAR(20) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('root', 'seller') DEFAULT 'seller',
    name VARCHAR(100) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- BẢNG USERS (UID)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    uid VARCHAR(20) PRIMARY KEY,
    password VARCHAR(255) NOT NULL DEFAULT '123456',
    name VARCHAR(100) DEFAULT 'Thành viên',
    balance BIGINT DEFAULT 0,
    avatar VARCHAR(500) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- BẢNG PRODUCTS (Sản phẩm)
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(20) PRIMARY KEY,
    owner VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    tag VARCHAR(50) DEFAULT 'ACTIVE',
    sold INT DEFAULT 0,
    stock INT DEFAULT 0,
    description TEXT,
    media VARCHAR(500) DEFAULT '',
    media_type ENUM('image', 'video') DEFAULT 'image',
    delivery_type ENUM('file', 'key') DEFAULT 'key',
    delivery_text TEXT,
    delivery_file_name VARCHAR(200) DEFAULT '',
    delivery_file_type VARCHAR(100) DEFAULT '',
    delivery_file_data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_owner (owner),
    INDEX idx_category (category)
);

-- ============================================================
-- BẢNG PACKAGES (Gói giá của sản phẩm)
-- ============================================================
CREATE TABLE IF NOT EXISTS packages (
    id VARCHAR(20) PRIMARY KEY,
    product_id VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    price BIGINT NOT NULL,
    deliver TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
);

-- ============================================================
-- BẢNG ORDERS (Đơn hàng)
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(20) PRIMARY KEY,
    uid VARCHAR(20) NOT NULL,
    product_id VARCHAR(20) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    package_name VARCHAR(100) NOT NULL,
    price BIGINT NOT NULL,
    status ENUM('done', 'pending') DEFAULT 'done',
    seller VARCHAR(50) NOT NULL,
    delivery_type ENUM('file', 'key') DEFAULT 'key',
    deliver TEXT,
    file_name VARCHAR(200) DEFAULT '',
    file_type VARCHAR(100) DEFAULT '',
    file_data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_uid (uid),
    INDEX idx_seller (seller),
    INDEX idx_product (product_id)
);

-- ============================================================
-- BẢNG TOPUPS (Nạp tiền)
-- ============================================================
CREATE TABLE IF NOT EXISTS topups (
    id VARCHAR(20) PRIMARY KEY,
    uid VARCHAR(20) NOT NULL,
    amount BIGINT NOT NULL,
    face_amount BIGINT,
    method ENUM('bank', 'card') DEFAULT 'bank',
    note TEXT,
    card_telco VARCHAR(50) DEFAULT '',
    card_serial VARCHAR(50) DEFAULT '',
    card_code VARCHAR(50) DEFAULT '',
    bank_name VARCHAR(100) DEFAULT 'MBANK',
    bank_number VARCHAR(50) DEFAULT '0792822868',
    qr_content VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
    approved_by VARCHAR(50) DEFAULT '',
    created_ms BIGINT NOT NULL,
    expires_at_ms BIGINT NOT NULL,
    expires_at DATETIME,
    done_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_uid (uid),
    INDEX idx_status (status)
);

-- ============================================================
-- BẢNG CHATS (Tin nhắn chat)
-- ============================================================
CREATE TABLE IF NOT EXISTS chats (
    id VARCHAR(20) PRIMARY KEY,
    uid VARCHAR(20) NOT NULL,
    from_uid VARCHAR(50) NOT NULL,
    from_role VARCHAR(20) NOT NULL,
    text TEXT,
    file_name VARCHAR(200) DEFAULT '',
    file_type VARCHAR(100) DEFAULT '',
    file_data LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_uid (uid),
    INDEX idx_from (from_uid)
);

-- ============================================================
-- BẢNG NOTIFICATIONS (Thông báo)
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id VARCHAR(20) PRIMARY KEY,
    target_uid VARCHAR(20) DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    text TEXT NOT NULL,
    from_uid VARCHAR(50) DEFAULT 'system',
    admin_only TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_target (target_uid)
);

-- ============================================================
-- BẢNG DOWNLOADS (File tải xuống)
-- ============================================================
CREATE TABLE IF NOT EXISTS downloads (
    id VARCHAR(20) PRIMARY KEY,
    owner VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_name VARCHAR(200) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_data LONGTEXT,
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_owner (owner)
);

-- ============================================================
-- BẢNG SETTINGS (Cài đặt shop)
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY DEFAULT 1,
    shop_name VARCHAR(200) DEFAULT 'CHEATING GAME VN',
    slogan VARCHAR(200) DEFAULT 'Shop bán file / key gọn',
    logo VARCHAR(500) DEFAULT 'assets/products/logo-cheating-game-vn.png',
    announcement TEXT,
    partner VARCHAR(200) DEFAULT 'cheatinggame.vn',
    bank_name VARCHAR(100) DEFAULT 'MBANK',
    bank_number VARCHAR(50) DEFAULT '0792822868',
    bank_owner VARCHAR(100) DEFAULT 'CHEATING GAME VN',
    card_note TEXT,
    zalo VARCHAR(20) DEFAULT '0393399533',
    contact TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- DỮ LIỆU MẶC ĐỊNH
-- ============================================================

-- Admin (password: admin123)
INSERT IGNORE INTO admins (id, username, password, role, name, active) VALUES
('A-ROOT', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'root', 'Admin gốc', 1),
('A-SELLER', 'seller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 'Seller demo', 1);

-- Users demo
INSERT IGNORE INTO users (uid, password, name, balance) VALUES
('UID-DEMO01', '123456', 'Khách demo 1', 5000000),
('UID-DEMO02', '123456', 'Khách demo 2', 2000000);

-- Settings mặc định
INSERT IGNORE INTO settings (id) VALUES (1);

-- ============================================================
-- SẢN PHẨM MẪU
-- ============================================================

INSERT IGNORE INTO products (id, owner, name, category, tag, sold, stock, description, media, media_type, delivery_type, delivery_text, delivery_file_name, delivery_file_type, delivery_file_data) VALUES
('P-IOS', 'seller', 'PROXY AIM IOS', 'AIMBOT AURORA', 'ACTIVE', 118, 54, 'Hỗ trợ kéo tâm IOS\nTùy chọn nhiều gói giá\nKey/mô tả giao thủ công sau khi mua', 'assets/products/demo-aim-ios.jpg', 'image', 'key', 'KEY IOS DEMO - nhập key thật trong admin trước khi bán', '', '', ''),
('P-DRAG', 'seller', 'IMAZING AIM DRAG', 'AIMBOT AURORA', 'ACTIVE', 202, 61, 'Aim drag PC/giả lập\nTối ưu kéo tâm nhẹ\nCó hướng dẫn sử dụng kèm đơn hàng', 'assets/products/demo-aim-drag.jpg', 'image', 'key', 'KEY DRAG DEMO - thay bằng mô tả/key thật trong admin', '', '', ''),
('P-BYPASS', 'admin', 'BYPASS XGTEAM MOBILE MODE', 'BYPASS XGTEAM', 'ACTIVE', 3674, 295, 'Giả lập Mobile Mode\nHỗ trợ BlueStacks/MSI\nGiao file hoặc mô tả theo admin gắn', 'assets/products/demo-bypass.jpg', 'image', 'file', 'Tải file demo/hướng dẫn sau khi mua. Upload file thật trong admin.', 'huong-dan-bypass.txt', 'text/plain', 'data:text/plain;charset=utf-8,Huong dan bypass demo - thay file that trong admin'),
('P-FIX', 'admin', 'FIX LAG TỐI ƯU FPS', 'FIX LAG TỐI ƯU', 'ACTIVE', 812, 140, 'Tối ưu giả lập\nGiảm tụt FPS\nKèm file cấu hình hướng dẫn', 'assets/products/demo-fixlag.jpg', 'image', 'file', 'File cấu hình/hướng dẫn sẽ hiện ở đơn hàng.', 'fix-lag-demo.txt', 'text/plain', 'data:text/plain;charset=utf-8,Fix lag demo - upload file that trong admin'),
('P-ACC', 'seller', 'ACC CLONE FF GOOGLE', 'ACC CLONE FF', 'ACTIVE', 1972, 160, 'Acc clone đăng nhập Google\nThông tin giao thủ công theo mô tả/key admin gắn\nBảo hành theo mô tả', 'assets/products/demo-acc.jpg', 'image', 'key', 'Tài khoản demo: email@example.com | pass: 123456 - thay bằng thông tin thật trong admin', '', '', '');

-- Packages cho sản phẩm
INSERT IGNORE INTO packages (id, product_id, name, price, deliver) VALUES
('G-IOS-1', 'P-IOS', '1 ngày', 450000, ''),
('G-IOS-2', 'P-IOS', '7 ngày', 999000, ''),
('G-DRAG-1', 'P-DRAG', '1 ngày', 350000, ''),
('G-DRAG-2', 'P-DRAG', '7 ngày', 750000, ''),
('G-BYPASS-1', 'P-BYPASS', '1 ngày', 100000, ''),
('G-BYPASS-2', 'P-BYPASS', '30 ngày', 1000000, ''),
('G-FIX-1', 'P-FIX', 'Basic', 50000, ''),
('G-FIX-2', 'P-FIX', 'Pro', 200000, ''),
('G-ACC-1', 'P-ACC', '1 acc random', 12000, ''),
('G-ACC-2', 'P-ACC', '10 acc', 100000, '');

-- Downloads mẫu
INSERT IGNORE INTO downloads (id, owner, title, description, file_name, file_type, file_data, active) VALUES
('D-DEMO1', 'admin', 'Hướng dẫn mua hàng', 'File hướng dẫn sử dụng shop và cách nhận key theo UID.', 'huong-dan.txt', 'text/plain', 'data:text/plain;charset=utf-8,Hướng dẫn mua hàng CHEATING GAME VN', 1),
('D-DEMO2', 'admin', 'Launcher Auto Update', 'File launcher tự động cập nhật cho tool/game.', 'launcher-setup.exe', 'application/octet-stream', '', 1);

-- Notification mẫu
INSERT IGNORE INTO notifications (id, title, text, from_uid) VALUES
('N-DEMO', 'Thông báo hệ thống', 'Shop đã hỗ trợ 2 kiểu giao: File/Sản phẩm hoặc Key/Mô tả. Key không tự sinh random.', 'system');