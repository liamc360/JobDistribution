theIP=$1
#!/bin/bash
nohup ssh -o 'StrictHostKeyChecking no' -o 'ConnectTimeout=5' -X $theIP 'cd public_html/server;java -cp .:mysql-connector-java-5.1.40-bin.jar MainServer;exit'
	


