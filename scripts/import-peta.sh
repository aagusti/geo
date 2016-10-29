#!/bin/bash
conn="host=192.168.56.1 user=aagusti dbname=pbb_banjar password=a";
for file in `ls`; do
    filename=$(basename "$file")
    extension="${filename##*.}"
    filename="${filename%.*}"
    if  [ "$extension" = "TAB" ];then
        echo "Import $file" 
        `ogr2ogr -f "PostgreSQL" -a_srs EPSG:4326 PG:"$conn" $file -t_srs EPSG:4326 -nln mi$filename` 
    fi
done
#loc=`dirname $BASH_SOURCE`
echo "Run import-peta.py Script";
#`python $loc/import-peta.py`
