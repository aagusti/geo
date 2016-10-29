--- Jalankan Query Berikut Ini
--- Enable PostGIS (includes raster)

CREATE EXTENSION postgis;
-- Enable Topology
CREATE EXTENSION postgis_topology;
-- fuzzy matching needed for Tiger
CREATE EXTENSION fuzzystrmatch;

--Yang dibawah ini optional
-- Enable PostGIS Advanced 3D 
-- and other geoprocessing algorithms
-- sfcgal not available with all distributions
CREATE EXTENSION postgis_sfcgal;
-- rule based standardizer
CREATE EXTENSION address_standardizer;
-- example rule data set
CREATE EXTENSION address_standardizer_data_us;
-- Enable US Tiger Geocoder
CREATE EXTENSION postgis_tiger_geocoder;


alter table dat_nilai_individu set schema pbb;
alter table dat_nir set schema pbb;
alter table dat_objek_pajak set schema pbb;
alter table dat_op_bangunan set schema pbb;
alter table dat_op_bumi	set schema pbb;
alter table dat_subjek_pajak set schema pbb;
alter table dat_znt set schema pbb;
alter table sppt set schema pbb;


select count(*) from dat_nilai_individu;
select count(*) from dat_nir;
select count(*) from dat_objek_pajak;
select count(*) from dat_op_bangunan;
select count(*) from dat_op_bumi;
select count(*) from dat_subjek_pajak;
select count(*) from dat_znt;
select count(*) from sppt;

CREATE EXTENSION postgis;

select * from pg_available_extensions


alter table dat_nilai_individu set schema pbb;
alter table dat_nir  set schema pbb;
alter table dat_objek_pajak  set schema pbb;
alter table dat_op_bangunan  set schema pbb;
alter table dat_op_bumi  set schema pbb;
alter table dat_subjek_pajak  set schema pbb;
alter table dat_znt  set schema pbb;
alter table sppt  set schema pbb;

create schema sig;
alter table sig_blok	set schema sig;		
alter table sig_bng	set schema sig;		
alter table sig_bumi	set schema sig;		
alter table sig_dati2	set schema sig;		
alter table sig_kecamatan	set schema sig;		
alter table sig_kelurahan	set schema sig;		
alter table sig_propinsi	set schema sig;

delete from sig.sig_kecamatan;
delete from sig.sig_kelurahan;
delete from sig.sig_blok;
delete from sig.sig_bumi;
delete from sig.sig_bng;
