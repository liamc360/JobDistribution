<?php
$IP = $_POST['IP'];
exec('cd ../;./start-server-ip.sh '.$IP.' ', $out, $status);
?>