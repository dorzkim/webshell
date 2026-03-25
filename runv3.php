<?php
session_start();

// Konfigurasi default
$config = [
    'host' => 'localhost',
    'charset' => 'utf8mb4'
];

// Pesan error
$error = '';
$success = '';
$websites = [];
$results = [];

// Proses form login database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'login') {
            // Login ke database
            $host = $_POST['host'] ?? 'localhost';
            $username = $_POST['username'];
            $password = $_POST['password'];
            $database = $_POST['database'];
            
            // Koneksi ke database
            $conn = new mysqli($host, $username, $password, $database);
            
            if ($conn->connect_error) {
                throw new Exception("Koneksi gagal: " . $conn->connect_error);
            }
            
            // Simpan koneksi di session
            $_SESSION['db_host'] = $host;
            $_SESSION['db_user'] = $username;
            $_SESSION['db_pass'] = $password;
            $_SESSION['db_name'] = $database;
            
            $success = "Berhasil terhubung ke database!";
            
        } elseif ($_POST['action'] === 'fetch_websites' && isset($_SESSION['db_host'])) {
            // Ambil daftar website dari database
            $conn = new mysqli(
                $_SESSION['db_host'],
                $_SESSION['db_user'],
                $_SESSION['db_pass'],
                $_SESSION['db_name']
            );
            
            // Cari tabel wp_options (mungkin ada prefix yang berbeda)
            $tables_query = $conn->query("SHOW TABLES LIKE '%options'");
            $options_tables = [];
            
            while ($row = $tables_query->fetch_array()) {
                $options_tables[] = $row[0];
            }
            
            foreach ($options_tables as $table) {
                // Ambil siteurl dari setiap tabel options
                $query = $conn->query("SELECT option_value FROM $table WHERE option_name = 'siteurl'");
                
                if ($query && $query->num_rows > 0) {
                    $row = $query->fetch_assoc();
                    
                    // Dapatkan semua tabel untuk website ini
                    $prefix = str_replace('options', '', $table);
                    $all_tables_query = $conn->query("SHOW TABLES LIKE '{$prefix}%'");
                    $website_tables = [];
                    while ($table_row = $all_tables_query->fetch_array()) {
                        $website_tables[] = $table_row[0];
                    }
                    
                    $websites[] = [
                        'url' => $row['option_value'],
                        'options_table' => $table,
                        'users_table' => str_replace('options', 'users', $table),
                        'usermeta_table' => str_replace('options', 'usermeta', $table),
                        'prefix' => $prefix,
                        'tables' => $website_tables
                    ];
                }
            }
            
            if (empty($websites)) {
                $error = "Tidak ditemukan website WordPress dalam database.";
            }
            
        } elseif ($_POST['action'] === 'change_credentials' && isset($_SESSION['db_host'])) {
            // Ganti kredensial untuk website yang dipilih
            $conn = new mysqli(
                $_SESSION['db_host'],
                $_SESSION['db_user'],
                $_SESSION['db_pass'],
                $_SESSION['db_name']
            );
            
            $new_username = $_POST['new_username'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $selected_websites = $_POST['websites'] ?? [];
            
            if (empty($selected_websites)) {
                throw new Exception("Pilih setidaknya satu website.");
            }
            
            foreach ($selected_websites as $website_data) {
                $data = json_decode($website_data, true);
                $users_table = $data['users_table'];
                $site_url = $data['url'];
                
                // Update username jika diisi
                if (!empty($new_username)) {
                    $stmt = $conn->prepare("UPDATE $users_table SET user_login = ? WHERE ID = 1");
                    $stmt->bind_param("s", $new_username);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Update password jika diisi
                if (!empty($new_password)) {
                    $hashed_password = wp_hash_password($new_password);
                    $stmt = $conn->prepare("UPDATE $users_table SET user_pass = ? WHERE ID = 1");
                    $stmt->bind_param("s", $hashed_password);
                    $stmt->execute();
                    $stmt->close();
                }
                
                $results[] = [
                    'url' => $site_url,
                    'status' => 'Berhasil - Kredensial Diubah',
                    'new_username' => $new_username ?: '(tidak diubah)',
                    'new_password' => $new_password ? '********' : '(tidak diubah)'
                ];
            }
            
            $success = "Kredensial berhasil diubah untuk " . count($results) . " website.";
            
        } elseif ($_POST['action'] === 'add_admin_user' && isset($_SESSION['db_host'])) {
            // Tambah user admin baru
            $conn = new mysqli(
                $_SESSION['db_host'],
                $_SESSION['db_user'],
                $_SESSION['db_pass'],
                $_SESSION['db_name']
            );
            
            $admin_username = $_POST['admin_username'] ?? '';
            $admin_email = $_POST['admin_email'] ?? '';
            $admin_password = $_POST['admin_password'] ?? '';
            $selected_websites = $_POST['websites'] ?? [];
            
            if (empty($selected_websites)) {
                throw new Exception("Pilih setidaknya satu website.");
            }
            
            if (empty($admin_username) || empty($admin_email) || empty($admin_password)) {
                throw new Exception("Username, email, dan password harus diisi.");
            }
            
            foreach ($selected_websites as $website_data) {
                $data = json_decode($website_data, true);
                $users_table = $data['users_table'];
                $usermeta_table = $data['usermeta_table'];
                $prefix = $data['prefix'];
                $site_url = $data['url'];
                
                // Cek apakah username sudah ada
                $check_user = $conn->query("SELECT ID FROM $users_table WHERE user_login = '$admin_username' OR user_email = '$admin_email'");
                if ($check_user && $check_user->num_rows > 0) {
                    $results[] = [
                        'url' => $site_url,
                        'status' => 'Gagal - Username/Email sudah ada',
                        'new_username' => $admin_username,
                        'new_password' => '********'
                    ];
                    continue;
                }
                
                // Dapatkan ID user baru
                $result = $conn->query("SELECT MAX(ID) as max_id FROM $users_table");
                $row = $result->fetch_assoc();
                $new_user_id = $row['max_id'] + 1;
                
                // Hash password
                $hashed_password = wp_hash_password($admin_password);
                
                // Insert user baru
                $current_time = current_time('mysql');
                $stmt = $conn->prepare("
                    INSERT INTO $users_table 
                    (ID, user_login, user_pass, user_nicename, user_email, user_registered, display_name) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $user_nicename = sanitize_title($admin_username);
                $display_name = $admin_username;
                $stmt->bind_param("issssss", $new_user_id, $admin_username, $hashed_password, $user_nicename, $admin_email, $current_time, $display_name);
                $stmt->execute();
                $stmt->close();
                
                // Set role administrator
                $wp_capabilities = serialize(['administrator' => true]);
                $stmt = $conn->prepare("
                    INSERT INTO $usermeta_table 
                    (user_id, meta_key, meta_value) 
                    VALUES (?, ?, ?)
                ");
                $meta_key = $prefix . 'capabilities';
                $stmt->bind_param("iss", $new_user_id, $meta_key, $wp_capabilities);
                $stmt->execute();
                $stmt->close();
                
                // Set user level
                $stmt = $conn->prepare("
                    INSERT INTO $usermeta_table 
                    (user_id, meta_key, meta_value) 
                    VALUES (?, ?, ?)
                ");
                $meta_key = $prefix . 'user_level';
                $user_level = '10'; // Administrator level
                $stmt->bind_param("iss", $new_user_id, $meta_key, $user_level);
                $stmt->execute();
                $stmt->close();
                
                $results[] = [
                    'url' => $site_url,
                    'status' => 'Berhasil - User Admin Ditambahkan',
                    'new_username' => $admin_username,
                    'new_password' => '********'
                ];
            }
            
            $success = "User admin berhasil ditambahkan untuk " . count($results) . " website.";
            
        } elseif ($_POST['action'] === 'delete_user' && isset($_SESSION['db_host'])) {
            // Hapus user berdasarkan ID atau username
            $conn = new mysqli(
                $_SESSION['db_host'],
                $_SESSION['db_user'],
                $_SESSION['db_pass'],
                $_SESSION['db_name']
            );
            
            $user_identifier = $_POST['user_identifier'] ?? '';
            $identifier_type = $_POST['identifier_type'] ?? 'id';
            $selected_websites = $_POST['websites'] ?? [];
            
            if (empty($selected_websites)) {
                throw new Exception("Pilih setidaknya satu website.");
            }
            
            if (empty($user_identifier)) {
                throw new Exception("User ID atau username harus diisi.");
            }
            
            // Konfirmasi password untuk keamanan
            $confirm_password = $_POST['confirm_password'] ?? '';
            if ($confirm_password !== $_SESSION['db_pass']) {
                throw new Exception("Password database tidak cocok. Operasi hapus user dibatalkan.");
            }
            
            foreach ($selected_websites as $website_data) {
                $data = json_decode($website_data, true);
                $users_table = $data['users_table'];
                $usermeta_table = $data['usermeta_table'];
                $site_url = $data['url'];
                
                // Cari user berdasarkan ID atau username
                $user_query = "";
                if ($identifier_type === 'id') {
                    $user_query = "SELECT ID, user_login FROM $users_table WHERE ID = '$user_identifier'";
                } else {
                    $user_query = "SELECT ID, user_login FROM $users_table WHERE user_login = '$user_identifier'";
                }
                
                $user_result = $conn->query($user_query);
                
                if (!$user_result || $user_result->num_rows === 0) {
                    $results[] = [
                        'url' => $site_url,
                        'status' => 'Gagal - User tidak ditemukan',
                        'new_username' => $identifier_type === 'id' ? "ID: $user_identifier" : $user_identifier,
                        'new_password' => '-'
                    ];
                    continue;
                }
                
                $user = $user_result->fetch_assoc();
                $user_id = $user['ID'];
                $username = $user['user_login'];
                
                // Mulai transaksi
                $conn->begin_transaction();
                
                try {
                    // Hapus user meta terlebih dahulu (foreign key)
                    $conn->query("DELETE FROM $usermeta_table WHERE user_id = $user_id");
                    
                    // Hapus user dari tabel users
                    $conn->query("DELETE FROM $users_table WHERE ID = $user_id");
                    
                    // Commit transaksi
                    $conn->commit();
                    
                    $results[] = [
                        'url' => $site_url,
                        'status' => 'Berhasil - User dihapus',
                        'new_username' => $username,
                        'new_password' => "User ID: $user_id"
                    ];
                } catch (Exception $e) {
                    // Rollback jika terjadi error
                    $conn->rollback();
                    $results[] = [
                        'url' => $site_url,
                        'status' => 'Gagal - ' . $e->getMessage(),
                        'new_username' => $username,
                        'new_password' => '-'
                    ];
                }
            }
            
            $success = "Operasi hapus user selesai untuk " . count($selected_websites) . " website.";
            
        } elseif ($_POST['action'] === 'change_user_id_random' && isset($_SESSION['db_host'])) {
            // Ubah ID user dari 1 ke ID random yang belum digunakan
            $conn = new mysqli(
                $_SESSION['db_host'],
                $_SESSION['db_user'],
                $_SESSION['db_pass'],
                $_SESSION['db_name']
            );
            
            $selected_websites = $_POST['websites'] ?? [];
            
            if (empty($selected_websites)) {
                throw new Exception("Pilih setidaknya satu website.");
            }
            
            // Konfirmasi password untuk keamanan
            $confirm_password = $_POST['confirm_password_random'] ?? '';
            if ($confirm_password !== $_SESSION['db_pass']) {
                throw new Exception("Password database tidak cocok. Operasi dibatalkan.");
            }
            
            foreach ($selected_websites as $website_data) {
                $data = json_decode($website_data, true);
                $users_table = $data['users_table'];
                $usermeta_table = $data['usermeta_table'];
                $prefix = $data['prefix'];
                $site_url = $data['url'];
                
                // Cek apakah user dengan ID=1 ada
                $user_result = $conn->query("SELECT * FROM $users_table WHERE ID = 1");
                
                if (!$user_result || $user_result->num_rows === 0) {
                    $results[] = [
                        'url' => $site_url,
                        'status' => 'Gagal - User dengan ID=1 tidak ditemukan',
                        'new_username' => 'ID: 1',
                        'new_password' => '-'
                    ];
                    continue;
                }
                
                $user_data = $user_result->fetch_assoc();
                
                // Dapatkan semua ID yang sudah digunakan
                $used_ids_query = $conn->query("SELECT ID FROM $users_table");
                $used_ids = [];
                while ($row = $used_ids_query->fetch_assoc()) {
                    $used_ids[] = $row['ID'];
                }
                
                // Generate ID random yang belum digunakan (antara 10000 dan 99999)
                $max_attempts = 100;
                $attempt = 0;
                $new_user_id = 0;
                
                do {
                    $new_user_id = rand(10000, 99999);
                    $attempt++;
                    if ($attempt > $max_attempts) {
                        throw new Exception("Tidak dapat menemukan ID unik setelah $max_attempts percobaan.");
                    }
                } while (in_array($new_user_id, $used_ids));
                
                // Mulai transaksi
                $conn->begin_transaction();
                
                try {
                    // Matikan foreign key checks sementara
                    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                    
                    // Update ID di tabel users
                    $conn->query("UPDATE $users_table SET ID = $new_user_id WHERE ID = 1");
                    
                    // Update user_id di tabel usermeta
                    $conn->query("UPDATE $usermeta_table SET user_id = $new_user_id WHERE user_id = 1");
                    
                    // Update tabel lain yang mungkin menggunakan user_id sebagai foreign key
                    // Posts
                    $posts_table = $prefix . 'posts';
                    if ($conn->query("SHOW TABLES LIKE '$posts_table'")->num_rows > 0) {
                        $conn->query("UPDATE $posts_table SET post_author = $new_user_id WHERE post_author = 1");
                    }
                    
                    // Comments
                    $comments_table = $prefix . 'comments';
                    if ($conn->query("SHOW TABLES LIKE '$comments_table'")->num_rows > 0) {
                        $conn->query("UPDATE $comments_table SET user_id = $new_user_id WHERE user_id = 1");
                    }
                    
                    // Aktifkan kembali foreign key checks
                    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                    
                    $conn->commit();
                    
                    $results[] = [
                        'url' => $site_url,
                        'status' => "Berhasil - ID diubah dari 1 ke $new_user_id (random)",
                        'new_username' => $user_data['user_login'],
                        'new_password' => "ID Baru: $new_user_id"
                    ];
                } catch (Exception $e) {
                    $conn->rollback();
                    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                    $results[] = [
                        'url' => $site_url,
                        'status' => 'Gagal - ' . $e->getMessage(),
                        'new_username' => $user_data['user_login'],
                        'new_password' => '-'
                    ];
                }
            }
            
            $success = "Operasi perubahan ID random selesai untuk " . count($selected_websites) . " website.";
        }
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fungsi untuk hash password WordPress (tanpa ketergantungan pada WordPress)
function wp_hash_password($password) {
    // Menggunakan password_hash yang lebih aman (WordPress modern menggunakan ini)
    return password_hash($password, PASSWORD_BCRYPT);
}

// Fungsi untuk sanitize title
function sanitize_title($title) {
    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9-]/', '-', $title);
    $title = preg_replace('/-+/', '-', $title);
    return trim($title, '-');
}

// Fungsi untuk current time mysql
function current_time($type) {
    return date('Y-m-d H:i:s');
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WPDB - WordPress Database Manager</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h1, h2, h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        h1 {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .form-section {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-success {
            background-color: #27ae60;
        }
        
        .btn-success:hover {
            background-color: #219653;
        }
        
        .btn-warning {
            background-color: #f39c12;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .btn-info {
            background-color: #00bcd4;
        }
        
        .btn-info:hover {
            background-color: #00acc1;
        }
        
        .btn-purple {
            background-color: #9b59b6;
        }
        
        .btn-purple:hover {
            background-color: #8e44ad;
        }
        
        .btn-random {
            background-color: #e67e22;
        }
        
        .btn-random:hover {
            background-color: #d35400;
        }
        
        .btn-dark {
            background-color: #34495e;
        }
        
        .btn-dark:hover {
            background-color: #2c3e50;
        }
        
        .website-list {
            margin-top: 20px;
        }
        
        .website-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: white;
            display: flex;
            align-items: center;
        }
        
        .website-item input[type="checkbox"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
        }
        
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .result-table th,
        .result-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .result-table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        
        .result-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-success {
            color: #27ae60;
            font-weight: 600;
        }
        
        .status-error {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .status-warning {
            color: #f39c12;
            font-weight: 600;
        }
        
        .logout-btn {
            float: right;
            background-color: #95a5a6;
        }
        
        .logout-btn:hover {
            background-color: #7f8c8d;
        }
        
        .tab-container {
            margin-bottom: 20px;
        }
        
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .tab-button {
            padding: 12px 24px;
            background-color: #e0e0e0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .tab-button.active {
            background-color: #3498db;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .info-box {
            background-color: #e8f4fc;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 5px 5px 0;
        }
        
        .warning-box {
            background-color: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 5px 5px 0;
        }
        
        .danger-box {
            background-color: #fdeded;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 5px 5px 0;
        }
        
        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .radio-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .radio-item input[type="radio"] {
            width: auto;
            margin-right: 5px;
        }
        
        .inline-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .inline-group select {
            width: auto;
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .form-section {
                padding: 15px;
            }
            
            .btn {
                width: 100%;
                margin-right: 0;
            }
            
            .tab-buttons {
                flex-direction: column;
            }
            
            .tab-button {
                width: 100%;
            }
            
            .inline-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .inline-group select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>WPDB - WordPress Database Manager</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!isset($_SESSION['db_host'])): ?>
            <!-- Form Login Database -->
            <div class="form-section">
                <h2>Login ke Database MySQL</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="host">Host:</label>
                        <input type="text" id="host" name="host" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username Database:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password Database:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="database">Nama Database:</label>
                        <input type="text" id="database" name="database" required>
                    </div>
                    
                    <input type="hidden" name="action" value="login">
                    <button type="submit" class="btn">Login ke Database</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Tampilkan informasi koneksi -->
            <div class="info-box">
                <p><strong>Terhubung ke:</strong> <?php echo htmlspecialchars($_SESSION['db_host']); ?> | 
                <strong>Database:</strong> <?php echo htmlspecialchars($_SESSION['db_name']); ?></p>
                <a href="?logout=1" class="btn logout-btn">Logout Database</a>
                <div style="clear: both;"></div>
            </div>
            
            <!-- Form untuk mengambil daftar website -->
            <div class="form-section">
                <h2>List Web</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="fetch_websites">
                    <button type="submit" class="btn btn-info">Sikat (Ambil Daftar Website)</button>
                </form>
                
                <?php if (!empty($websites)): ?>
                    <div class="website-list">
                        <h3>Daftar Website Ditemukan (<?php echo count($websites); ?>):</h3>
                        
                        <div class="tab-container">
                            <div class="tab-buttons">
                                <button type="button" class="tab-button active" onclick="openTab('changeCredentials')">Ganti Kredensial Admin</button>
                                <button type="button" class="tab-button" onclick="openTab('addAdminUser')">Tambah User Admin Baru</button>
                                <button type="button" class="tab-button" onclick="openTab('deleteUser')">Hapus User</button>
                                <button type="button" class="tab-button" onclick="openTab('changeUserIdRandom')">Ubah ID User 1 ke Random</button>
                            </div>
                            
                            <!-- Tab Ganti Kredensial -->
                            <div id="changeCredentials" class="tab-content active">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="change_credentials">
                                    
                                    <?php foreach ($websites as $index => $website): ?>
                                        <div class="website-item">
                                            <input type="checkbox" name="websites[]" value='<?php echo json_encode($website); ?>' id="website_<?php echo $index; ?>">
                                            <label for="website_<?php echo $index; ?>">
                                                <?php echo htmlspecialchars($website['url']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div style="margin-top: 20px;">
                                        <h3>Ganti Kredensial Admin (User ID=1)</h3>
                                        
                                        <div class="form-group">
                                            <label for="new_username">Username Baru:</label>
                                            <input type="text" id="new_username" name="new_username" placeholder="Kosongkan jika tidak ingin mengubah username">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="new_password">Password Baru:</label>
                                            <input type="password" id="new_password" name="new_password" placeholder="Kosongkan jika tidak ingin mengubah password">
                                        </div>
                                        
                                        <div class="button-group">
                                            <button type="submit" class="btn btn-success">Ganti Kredensial untuk Website Terpilih</button>
                                            <button type="button" onclick="selectAllWebsites('changeCredentials', true)" class="btn">Pilih Semua</button>
                                            <button type="button" onclick="selectAllWebsites('changeCredentials', false)" class="btn">Batal Pilih Semua</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Tab Tambah User Admin Baru -->
                            <div id="addAdminUser" class="tab-content">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="add_admin_user">
                                    
                                    <?php foreach ($websites as $index => $website): ?>
                                        <div class="website-item">
                                            <input type="checkbox" name="websites[]" value='<?php echo json_encode($website); ?>' id="admin_website_<?php echo $index; ?>">
                                            <label for="admin_website_<?php echo $index; ?>">
                                                <?php echo htmlspecialchars($website['url']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div style="margin-top: 20px;">
                                        <h3>Tambah User Admin Baru</h3>
                                        
                                        <div class="form-group">
                                            <label for="admin_username">Username Baru:</label>
                                            <input type="text" id="admin_username" name="admin_username" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="admin_email">Email:</label>
                                            <input type="email" id="admin_email" name="admin_email" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="admin_password">Password:</label>
                                            <input type="password" id="admin_password" name="admin_password" required>
                                        </div>
                                        
                                        <div class="button-group">
                                            <button type="submit" class="btn btn-warning">Tambah User Admin untuk Website Terpilih</button>
                                            <button type="button" onclick="selectAllWebsites('addAdminUser', true)" class="btn">Pilih Semua</button>
                                            <button type="button" onclick="selectAllWebsites('addAdminUser', false)" class="btn">Batal Pilih Semua</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Tab Hapus User -->
                            <div id="deleteUser" class="tab-content">
                                <div class="warning-box">
                                    <h3 style="color: #f39c12;">⚠️ PERINGATAN: Hapus User</h3>
                                    <p>Fitur ini akan <strong>menghapus user secara permanen</strong> dari database WordPress.</p>
                                    <ul>
                                        <li>Semua data terkait user (meta, posts, dll) akan tetap ada</li>
                                        <li>Pastikan Anda yakin sebelum melanjutkan</li>
                                    </ul>
                                </div>
                                
                                <form method="POST" action="" onsubmit="return confirmDeleteUser()">
                                    <input type="hidden" name="action" value="delete_user">
                                    
                                    <?php foreach ($websites as $index => $website): ?>
                                        <div class="website-item">
                                            <input type="checkbox" name="websites[]" value='<?php echo json_encode($website); ?>' id="delete_website_<?php echo $index; ?>">
                                            <label for="delete_website_<?php echo $index; ?>">
                                                <?php echo htmlspecialchars($website['url']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div style="margin-top: 20px;">
                                        <h3>Hapus User</h3>
                                        
                                        <div class="form-group">
                                            <label>Cari user berdasarkan:</label>
                                            <div class="inline-group">
                                                <select name="identifier_type" id="identifier_type">
                                                    <option value="id">ID User</option>
                                                    <option value="username">Username</option>
                                                </select>
                                                <input type="text" name="user_identifier" id="user_identifier" required placeholder="Masukkan ID atau username user yang akan dihapus">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password">Konfirmasi Password Database:</label>
                                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Masukkan password database untuk konfirmasi">
                                            <small><em>Password database diperlukan untuk keamanan</em></small>
                                        </div>
                                        
                                        <div class="button-group">
                                            <button type="submit" class="btn btn-danger">🗑️ Hapus User untuk Website Terpilih</button>
                                            <button type="button" onclick="selectAllWebsites('deleteUser', true)" class="btn">Pilih Semua</button>
                                            <button type="button" onclick="selectAllWebsites('deleteUser', false)" class="btn">Batal Pilih Semua</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Tab Ubah ID User 1 ke Random -->
                            <div id="changeUserIdRandom" class="tab-content">
                                <div class="danger-box">
                                    <h3 style="color: #e74c3c;">⚠️ PERINGATAN: Ubah ID User 1 ke Random</h3>
                                    <p>Fitur ini akan mengubah ID user admin utama (ID=1) ke ID random yang belum digunakan (10000-99999).</p>
                                    <ul>
                                        <li>ID baru akan dibuat secara random dan dipastikan unik</li>
                                        <li>Semua data terkait user (posts, comments, meta) akan diperbarui secara otomatis</li>
                                        <li>Pastikan Anda telah melakukan backup database sebelum melanjutkan</li>
                                    </ul>
                                </div>
                                
                                <form method="POST" action="" onsubmit="return confirmRandomIdAction()">
                                    <input type="hidden" name="action" value="change_user_id_random">
                                    
                                    <?php foreach ($websites as $index => $website): ?>
                                        <div class="website-item">
                                            <input type="checkbox" name="websites[]" value='<?php echo json_encode($website); ?>' id="random_website_<?php echo $index; ?>">
                                            <label for="random_website_<?php echo $index; ?>">
                                                <?php echo htmlspecialchars($website['url']); ?>
                                                <small>(<?php echo htmlspecialchars($website['users_table']); ?>)</small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div style="margin-top: 20px;">
                                        <h3>Konfigurasi Perubahan ID Random</h3>
                                        
                                        <div class="info-box" style="margin-top: 15px;">
                                            <p><strong>Informasi:</strong> ID baru akan digenerate secara random antara 10000 - 99999 dan dipastikan tidak bentrok dengan ID yang sudah ada.</p>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password_random">Konfirmasi Password Database:</label>
                                            <input type="password" id="confirm_password_random" name="confirm_password_random" required placeholder="Masukkan password database untuk konfirmasi">
                                        </div>
                                        
                                        <div class="button-group">
                                            <button type="submit" class="btn btn-random">🎲 Ubah ID User 1 ke Random untuk Website Terpilih</button>
                                            <button type="button" onclick="selectAllWebsites('changeUserIdRandom', true)" class="btn">Pilih Semua</button>
                                            <button type="button" onclick="selectAllWebsites('changeUserIdRandom', false)" class="btn">Batal Pilih Semua</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Tampilkan hasil eksekusi -->
            <?php if (!empty($results)): ?>
                <div class="form-section">
                    <h2>Hasil Eksekusi</h2>
                    <p>Perubahan yang telah dilakukan:</p>
                    
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>Website URL</th>
                                <th>Username/Info</th>
                                <th>Password/Info</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $index => $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['url']); ?></td>
                                    <td><?php echo htmlspecialchars($result['new_username']); ?></td>
                                    <td><?php echo htmlspecialchars($result['new_password']); ?></td>
                                    <td class="<?php 
                                        if (strpos($result['status'], 'Berhasil') !== false) echo 'status-success';
                                        elseif (strpos($result['status'], 'Gagal') !== false) echo 'status-error';
                                        else echo 'status-warning';
                                    ?>">
                                        <?php echo htmlspecialchars($result['status']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script>
        // Fungsi untuk memilih/batalkan semua website berdasarkan tab
        function selectAllWebsites(tabId, select) {
            const container = document.getElementById(tabId);
            if (container) {
                const checkboxes = container.querySelectorAll('input[name="websites[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = select;
                });
            }
        }
        
        // Fungsi untuk tab switching
        function openTab(tabName) {
            // Sembunyikan semua tab content
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Nonaktifkan semua tab button
            const tabButtons = document.getElementsByClassName('tab-button');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            
            // Aktifkan tab yang dipilih
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Konfirmasi sebelum hapus user
        function confirmDeleteUser() {
            const checkboxes = document.querySelectorAll('#deleteUser input[name="websites[]"]:checked');
            const userIdentifier = document.getElementById('user_identifier').value;
            const identifierType = document.getElementById('identifier_type').value;
            
            if (checkboxes.length === 0) {
                alert('Pilih setidaknya satu website.');
                return false;
            }
            
            if (!userIdentifier.trim()) {
                alert('Masukkan ID atau username user yang akan dihapus.');
                return false;
            }
            
            const message = `⚠️ PERINGATAN!\n\n` +
                `Anda akan menghapus user ${identifierType === 'id' ? 'ID' : 'username'}: ${userIdentifier}\n` +
                `dari ${checkboxes.length} website.\n\n` +
                `TINDAKAN INI TIDAK DAPAT DIBATALKAN!\n\n` +
                `Apakah Anda yakin ingin melanjutkan?`;
            
            return confirm(message);
        }
        
        // Konfirmasi untuk tindakan Random ID
        function confirmRandomIdAction() {
            const checkboxes = document.querySelectorAll('#changeUserIdRandom input[name="websites[]"]:checked');
            
            if (checkboxes.length === 0) {
                alert('Pilih setidaknya satu website.');
                return false;
            }
            
            const message = `⚠️ PERINGATAN!\n\n` +
                `Anda akan mengubah ID user admin utama (ID=1) ke ID random yang belum digunakan.\n` +
                `untuk ${checkboxes.length} website.\n\n` +
                `ID baru akan digenerate antara 10000 - 99999.\n` +
                `Semua data terkait user akan diperbarui secara otomatis.\n\n` +
                `Pastikan Anda telah melakukan backup database.\n\n` +
                `Apakah Anda yakin ingin melanjutkan?`;
            
            return confirm(message);
        }
    </script>
</body>
</html>