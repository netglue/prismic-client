#!/usr/bin/env bash

# $1 = Exit Status of Job
# $2 = User
# $3 = WorkDir
# $4 = The JOB Json String

JOB=$4
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

cat /etc/php/"$PHP_VERSION"/cli/conf.d/99-settings.ini

bash <(curl -s https://codecov.io/bash)
