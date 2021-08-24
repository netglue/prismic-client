#!/usr/bin/env bash

exit 0;

# disable Xdebug and enable pcov:

phpdismod -v ALL -s ALL xdebug
phpenmod -v ALL -s ALL pcov
