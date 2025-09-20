#!/bin/sh
# Nastaví git, aby používal .githooks jako adresář s hooky

git config core.hooksPath .githooks
echo "Git hooky budou načítány z .githooks/"
