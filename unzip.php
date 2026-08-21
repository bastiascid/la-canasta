<?php
$zip = new ZipArchive;
$res = $zip->open('watts_images.zip');
if ($res === TRUE) {
  $zip->extractTo('./');
  $zip->close();
  echo 'ok';
} else {
  echo 'failed';
}
?>
