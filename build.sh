#!/bin/sh
# Usage: ./build.sh 42  ->  dist/designstager-v42.zip (folder inside the zip: designstager-v42/)
set -e
V="${1:?version number, e.g. 42}"
rm -rf dist/tmp && mkdir -p dist/tmp
cp -r plugin "dist/tmp/designstager-v$V"
( cd dist/tmp && zip -qr "../designstager-v$V.zip" "designstager-v$V" -x '*.DS_Store' )
rm -rf dist/tmp
echo "dist/designstager-v$V.zip"
