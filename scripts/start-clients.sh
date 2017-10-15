user=u4lc1

#!/bin/bash
for ip in $(cat hosts); do
    #gnome-terminal -e -t "ssh $user@$ip ls" > out.txt
	nohup ssh -o 'StrictHostKeyChecking no' -o 'ConnectTimeout=5' -X $user@$ip 'cd public_html/client;java ClientStart' &
done

