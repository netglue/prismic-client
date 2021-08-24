#!/usr/bin/env bash

# disable Xdebug and enable pcov:

pecl install pcov

phpdismod -v ALL -s ALL xdebug
phpenmod -v ALL -s ALL pcov
