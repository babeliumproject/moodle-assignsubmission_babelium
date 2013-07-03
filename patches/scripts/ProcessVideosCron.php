<?php

echo "\n\n";
require_once 'MediaTask.php';
echo "[".date("d/m/Y H:i:s")."] Commencing video processing task...\n";
$mt = new MediaTask();
$mt->processPendingVideos();

echo "\n\n";
require_once 'VideoCollage.php';
echo "[".date("d/m/Y H:i:s")."] Commencing response merge task...\n";
$vc = new VideoCollage();
$vc->makeResponseCollages();

?>
