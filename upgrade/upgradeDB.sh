#!/bin/sh
if [ $# != 0 ]; then
echo "Upgrade BDD CCP"
echo "Usage: upgradeDB.sh"
exit 0
fi

dumpdir=CCP
user=`grep DB_USER ../config.php |sed "s|^define('DB_USER', '\(.*\)');|\1|"`
bdd=`grep DB_NAME ../config.php |sed "s|^define('DB_NAME', '\(.*\)');|\1|"`
pass=`grep DB_PASS ../config.php |sed "s|^define('DB_PASS', '\(.*\)');|\1|"`
echo "Upgrade EVE BDD with sql in $dumpdir in $bdd database"

echo "Dropping all eve tables..."
mysql -u$user -p$pass $bdd < sql/dropCCP.sql

cd $dumpdir
#mkdir imported

for sql in *.sql.bz2; do
  echo "Importing $sql...";
  bunzip2 -c $sql | mysql -u$user -p$pass $bdd 
  if [ $? != 0 ]; then
    echo "error !"
    exit 1;
  fi
 # mv $sql imported;
done

echo "Upgrade completed!"
exit 0

