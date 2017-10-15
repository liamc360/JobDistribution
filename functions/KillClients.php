<?php
exec('cd ../;./kill-clients.sh', $out, $status);

if (0 === $status) {
    echo("Clients Stopped");
} else {
    echo "Command failed with status: $status";
}
?>