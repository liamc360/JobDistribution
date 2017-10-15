#!/bin/bash
for ip in $(cat hosts); do
	nohup ssh -o 'StrictHostKeyChecking no' -o 'ConnectTimeout=5' -X $ip 'cd public_html/client;java ClientStart' &
done

