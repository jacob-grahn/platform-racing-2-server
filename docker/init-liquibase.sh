#!/bin/bash
/scripts/wait-for-it.sh -t 360 mysql:3306

echo "driver: ${LIQUIBASE_DRIVER}" >> /liquibase.properties
echo "url: ${LIQUIBASE_URL}" >> /liquibase.properties
echo "username: ${LIQUIBASE_USERNAME}" >> /liquibase.properties
echo "password: ${LIQUIBASE_PASSWORD}" >> /liquibase.properties
echo "changeLogFile: ${LIQUIBASE_CHANGELOG}" >> /liquibase.properties

liquibase $1