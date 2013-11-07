#!/bin/sh

user=`grep DB_USER ../config.php |sed "s|^define('DB_USER', '\(.*\)');|\1|"`
bdd=`grep DB_NAME ../config.php |sed "s|^define('DB_NAME', '\(.*\)');|\1|"`
pass=`grep DB_PASS ../config.php |sed "s|^define('DB_PASS', '\(.*\)');|\1|"`

bddfile=mysql_items_selling.txt.gz

echo "Downloading market data dump ..."
wget http://eve-marketdata.com/developers/$bddfile

echo "Importing $bddfile in $bdd database"

gunzip < mysql_items_selling.txt.gz | mysql -u$user -p$pass $bdd

if [[ "$?" != 0 ]]; then
    echo "error !"
    exit 1;
fi

echo "Import complete, cleaning file"
rm mysql_items_selling.txt.gz

exit 0
