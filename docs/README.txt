SIG PBB
Requerement Data PBB
- pbb.dat_nilai_individu		
- pbb.dat_nir			
- pbb.dat_objek_pajak			
- pbb.dat_op_bangunan			
- pbb.dat_op_bumi			
- pbb.dat_subjek_pajak			
- pbb.dat_znt			
- pbb.sppt

Aplikasi
- Apache
- Postgresql
- Postgis
- Map Server
- PHP5 

Instalasi
- Install Apache
    apt-get install apache2
- Install PHP 5
    apt-get install php5
    apt-get install php5-pgsql
    apt-get install php5-mapscritp
    
- Install Postgresql
    apt-get install postgresql
- Install PostGIS
    apt-get install postgis
- Install Mapserver
    apt-get install cgi-mapserver
-  Install GDAL
    apt-get install gdal-bin

- SQLAlchemy geoalchemy2

Setting Aplikasi
- Buat Database 
- Restore Database sig-structure.backup folder docs
  
- Buat user untuk aplikasi sig
- Clone https://github.com/aagusti/opensipkd-sig
1. Edit file conf/local_setting.ini
2. buat directory temporary out mkdir /tmp/out  chmod 777
3. Edit file maps/pemda/pbb/connection.inc
4. Edit file maps/geomoose_globals.map 
5. Edit file maps/temp_directory.map mkdir /tmp/www chmod 777
6. Edit file htdocs/.htaccess di folder htdocs
7. Edit file htdocs/app/db.php 

Reconfigure Apache
- Edit file /etc/apache2/site-enabled/000-default.conf
     sesuaikan directory root menjadi 
     /home/[user]/geo/htdocs
     
- Activkan module cgi 
    a2enmod cgi

Reconfigure PHP
- Edit file /etc/php/apache2/php.ini
- Activekan short_open_tag


Konversi File2 MAP Versi MapInfo
- Buat folder  mapinfo di home user
- Buat folder  mapinfo/bak di home user
- Copy Seluruh File Mapinfo ke folder tersebut
- Masuk ke folder mapinfo
- jalankan script sh ../geo/scripts/import-peta.sh
- jalankan script python ../geo/scripts/import-peta.py

Test Aplikasi http://ip-address