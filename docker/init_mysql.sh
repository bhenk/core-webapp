#!/usr/bin/env bash
# -------------------------------------------------------------------
# Script to initiate mysql container.
# -------------------------------------------------------------------

B_LGRAY="\E[1;47m"
C_END="\E[0m "

CONTAINER_NAME=cosql
MYSQL_DATA_DIR="$(realpath $PWD/../../../mysql/cosql)"

docker run -d \
  	--name $CONTAINER_NAME \
  	-v $MYSQL_DATA_DIR:/var/lib/mysql \
    -e MYSQL_ROOT_PASSWORD=admin \
    -e MYSQL_DATABASE=corewa \
    -e MYSQL_USER=ucorewa \
    -e MYSQL_PASSWORD=pcorewa \
    -p 3306:3306 \
    mysql:latest \
    --character-set-server=utf8mb4 \
    --collation-server=utf8mb4_unicode_ci \

if [ $? -eq 0 ]; then
  	echo "-- Mysql server started"
  	printf "   To stop the server type $B_LGRAY docker stop %s $C_END\n" $CONTAINER_NAME
else
  	echo "## FAIL ######## see above for details ########"
  	exit
fi
