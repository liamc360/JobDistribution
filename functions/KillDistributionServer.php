<?php
exec('cd ../;./shutdown-server.sh', $out, $status);

if (0 === $status) {
    echo("Server shutdown");
} else {
    echo "Command failed with status: $status";
}
?>
