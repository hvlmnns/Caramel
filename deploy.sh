#!/bin/bash

API_PATH=Caramel


DIR=${PWD}



cd "$DIR"

if [ ! -d "slate/source/includes/phpdoc" ]; then
    mkdir slate/source/includes/phpdoc
fi

cd "$DIR/slate/vendor/bin"

./phpdoc -d ../../../$API_PATH -t ../../phpdoc --template="xml"
./phpdocmd ../../phpdoc/structure.xml ../../source/includes/phpdoc  --lt "#%c"

# cd "$DIR/slate"
# sh "./deploy.sh"
# rm -R "$DIR/fonts"
# rm -R "$DIR/javascripts"
# rm -R "$DIR/stylesheets"
# rm -R "$DIR/index.html"
# shopt -s dotglob
# mv $DIR/slate/build/* $DIR
# rm -R "$DIR/includes"
# rm -R "$DIR/slate/build"
# rm -R "$DIR/slate/phpdoc"