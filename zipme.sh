#!/bin/bash
# Activate with fswatch -o upload/ | xargs -n1 ./zipme.sh

OUTFILE=store_sync.ocmod.zip
UPLOAD=./upload

rm $OUTFILE
zip -r $OUTFILE $UPLOAD
