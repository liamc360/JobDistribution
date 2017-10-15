<?php
exec('cd ../;./start-clients.sh', $out, $status);

if (0 === $status) {
    echo("Started Clients");
} else {
    echo "Command failed with status: $status";
}
?>