theIP=$1
#!/bin/bash
ssh -o 'StrictHostKeyChecking no' -o 'ConnectTimeout=2' -X $theIP cat /proc/loadavg | awk '{print $1, $2, $3}'