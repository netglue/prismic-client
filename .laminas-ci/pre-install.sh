#!/usr/bin/env bash


# $1: the user the QA command will run under
# $2: the WORKDIR path
# $3: the $JOB passed to the entrypoint (see above)

JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')
pecl install php"$PHP_VERSION"-pcov

phpdismod -v ALL -s ALL xdebug
phpenmod -v ALL -s ALL pcov
