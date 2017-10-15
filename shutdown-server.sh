theIP=`cat ip.txt`
#!/bin/bash
arrIN=(${theIP//:/ })
ssh -o 'StrictHostKeyChecking no' -o 'ConnectTimeout=5' -X $arrIN 'pgrep -f MainServer | xargs kill -9;' &