#!/usr/bin/python
import os, sys, getopt, shutil
from sqlalchemy import create_engine
from subprocess import call

path = os.path.dirname(os.path.realpath(__file__))

sys.path[0:0] = [path]
from config import (
    db_url,
    db_pool_size,
    db_max_overflow,
    )
engine = create_engine(db_url, pool_size=db_pool_size,
            max_overflow=db_max_overflow)

from base_model import  Base, DBSession
Base.metadata.bind = engine
DBSession.configure(bind=engine)

from models import SigKecamatan, SigKelurahan, SigBlok, SigBumi, SigBng, DBProfile

#sys.path[0:0] = ['/etc/opensipkd']

engine = create_engine(db_url, echo=True)

db1 = db_url.replace('//','').split('@')

db_user = db1[0].split(':')
db_name = db1[1].split('/')

#db_source = DBProfile(db_url)

def call_sh():
    #apath = os.path.realpath(__file__)
    apath = os.path.dirname(__file__)
    params = ['sh',apath+'/import-peta.sh']
    print "Execute", ":".join(params)
    #call(params)
    #sys.exit()
def get_kd_kec(kode):
    ret = {}
    ret['kd_pro'] = kode[0:2]
    ret['kd_dat'] = kode[2:4]
    ret['kd_kec'] = kode[4:7]
    return ret

def get_kd_kel(kode):
    ret = get_kd_kec(kode)
    ret['kd_kel'] = kode[7:10]
    return ret

def get_kd_blok(kode):
    ret = get_kd_kel(kode)
    ret['kd_blok'] = kode[10:13]
    return ret

def get_kd_nop(kode):
    ret = get_kd_blok(kode)
    ret['no_urut'] = kode[13:17]
    ret['kd_jns'] = kode[17:18]
    return ret
    
def get_kd_bng(kode):
    ret = get_kd_nop(kode)
    ret['no_bng'] = kode[18:]
    return ret
    
def import_kecamatan(file):
    print 'Kecamatan',file
    rows = DBSession.execute('SELECT d_kd_kec, d_nm_kec, wkb_geometry geom FROM mi'+file)
    try:
        for row in rows:
            kode = get_kd_kec(row['d_kd_kec'])
            qry = DBSession.query(SigKecamatan).\
                  filter_by(
                        kd_propinsi=kode['kd_pro'],
                        kd_dati2=kode['kd_dat'],
                        kd_kecamatan=kode['kd_kec'],
                  ).first()
            if not qry:
                qry=SigKecamatan()
            qry.kd_propinsi=kode['kd_pro']
            qry.kd_dati2=kode['kd_dat']
            qry.kd_kecamatan=kode['kd_kec']
            qry.nm_kecamatan=row['d_nm_kec']
            qry.geom = row.geom
            DBSession.add(qry)
    finally:
        DBSession.flush()
        DBSession.commit()
    
def import_kelurahan(file):
    print 'Kelurahan',file
    rows = DBSession.execute('SELECT d_kd_kel, d_nm_kel, wkb_geometry geom FROM mi'+file)
    try:
        for row in rows:
            kode = get_kd_kel(row['d_kd_kel'])
            qry = DBSession.query(SigKelurahan).\
                  filter_by(
                        kd_propinsi=kode['kd_pro'],
                        kd_dati2=kode['kd_dat'],
                        kd_kecamatan=kode['kd_kec'],
                        kd_kelurahan=kode['kd_kel'],
                  ).first()
            if not qry:
                qry=SigKelurahan()
            qry.kd_propinsi=kode['kd_pro']
            qry.kd_dati2=kode['kd_dat']
            qry.kd_kecamatan=kode['kd_kec']
            qry.kd_kelurahan=kode['kd_kel']
            qry.nm_kelurahan=row['d_nm_kel']
            qry.geom = row.geom
            DBSession.add(qry)
    finally:
        DBSession.flush()
        DBSession.commit()

def import_blok(file):
    print 'Blok', file
    rows = DBSession.execute('SELECT d_blok, wkb_geometry geom FROM mi'+file)
    try:
        for row in rows:
            kode = get_kd_blok(row['d_blok'])
            qry = DBSession.query(SigBlok).\
                  filter_by(
                        kd_propinsi=kode['kd_pro'],
                        kd_dati2=kode['kd_dat'],
                        kd_kecamatan=kode['kd_kec'],
                        kd_kelurahan=kode['kd_kel'],
                        kd_blok=kode['kd_blok'],
                  ).first()
            if not qry:
                qry=SigBlok()
            qry.kd_propinsi=kode['kd_pro']
            qry.kd_dati2=kode['kd_dat']
            qry.kd_kecamatan=kode['kd_kec']
            qry.kd_kelurahan=kode['kd_kel']
            qry.kd_blok=kode['kd_blok']
            qry.geom = row.geom
            DBSession.add(qry)
    finally:
        DBSession.flush()
        DBSession.commit()
        
def import_bumi(file):
    print 'Bumi',file
    rows = DBSession.execute('SELECT d_nop, wkb_geometry geom FROM mi'+file)
    try:
        for row in rows:
            kode = get_kd_nop(row['d_nop'])
            qry = DBSession.query(SigBumi).\
                  filter_by(
                        kd_propinsi=kode['kd_pro'],
                        kd_dati2=kode['kd_dat'],
                        kd_kecamatan=kode['kd_kec'],
                        kd_kelurahan=kode['kd_kel'],
                        kd_blok=kode['kd_blok'],
                        no_urut=kode['no_urut'],
                        kd_jns_op=kode['kd_jns'],
                  ).first()
            if not qry:
                qry=SigBumi()
            qry.kd_propinsi=kode['kd_pro']
            qry.kd_dati2=kode['kd_dat']
            qry.kd_kecamatan=kode['kd_kec']
            qry.kd_kelurahan=kode['kd_kel']
            qry.kd_blok=kode['kd_blok']
            qry.no_urut=kode['no_urut']
            qry.kd_jns_op=kode['kd_jns']
            qry.geom = row.geom
            DBSession.add(qry)
    finally:
        DBSession.flush()
        DBSession.commit()
    
def import_bng(file):
    print 'Bangunan',file

    rows = DBSession.execute('SELECT d_nop, wkb_geometry geom FROM mi'+file)
    #try:
    for row in rows:
        kode = get_kd_bng(row['d_nop'])
        qry = DBSession.query(SigBng).\
              filter_by(
                    kd_propinsi=kode['kd_pro'],
                    kd_dati2=kode['kd_dat'],
                    kd_kecamatan=kode['kd_kec'],
                    kd_kelurahan=kode['kd_kel'],
                    kd_blok=kode['kd_blok'],
                    no_urut=kode['no_urut'],
                    kd_jns_op=kode['kd_jns'],
                    no_bng=kode['no_bng'],
              ).first()
        if not qry:
            qry=SigBng()
        qry.kd_propinsi=kode['kd_pro']
        qry.kd_dati2=kode['kd_dat']
        qry.kd_kecamatan=kode['kd_kec']
        qry.kd_kelurahan=kode['kd_kel']
        qry.kd_blok=kode['kd_blok']
        qry.kd_no_urut=kode['no_urut']
        qry.kd_jns_op=kode['kd_jns']
        qry.no_bng=kode['no_bng']
        qry.geom = row.geom
        DBSession.add(qry)
    # finally:
        # DBSession.flush()
        # DBSession.commit()

def main(argv):
    # path = ''
    # jenis    = ''
    # help = 'import-peta.py -d <directory> -t <jenis>'
    # if len(argv)<1:
        # print help
        # sys.exit(3)
        
    # try:
        # opts, args = getopt.getopt(argv,"d:t:")
    # except getopt.GetoptError:
        # print help
        # sys.exit(2)
      
    # for opt, arg in opts:
        # if opt == '-h':
            # print help
            # sys.exit()
        # elif opt in ("-d", "--dir"):
            # path = arg
        # elif opt in ("-t", "--type"):
            # jenis = arg
    # print 'Input Dir is "', path
    # print 'Type Data "', jenis
    
    # call_sh()
    
    #os.chdir( path )
    path = os.getcwd()
    files = os.listdir(path)
    for file in files:
        filename, file_extension = os.path.splitext(file)
        if file_extension=='.TAB':
            try:
                if len(filename)==4: #sig_kelurahan 3279 (batas kecamatan)
                    import_kecamatan(filename)
                if len(filename)==7: #sig_kelurahan 3279010 (batas kelurahan)
                    import_kelurahan(filename)
                elif len(filename)==10: #kelurahan sig_bumi (batas bumi)
                    import_bumi(filename)
                elif len(filename)==12 and filename[-2:]=='bl':
                    import_blok(filename)
                elif len(filename)==12 and filename[-2:]=='bg':
                    import_bng(filename)
                
                try:
                    sql = 'DROP TABLE mi%s' % filename
                    print sql
                    DBSession.execute(sql)
                    DBSession.flush()
                    DBSession.commit()
                except Exception, e:
                    print file, str(e)
                    
                
                try:
                    shutil.move( "%s.TAB" % filename, "bak/")
                    shutil.move( "%s.ID" % filename, "bak/")
                    shutil.move( "%s.MAP" % filename, "bak/")
                    shutil.move( "%s.DAT" % filename, "bak/")
                except Exception, e:
                    print 'Error:', str(e)
                    shutil.move( "%s.TAB" % filename, "bak/")
                    
            except Exception, e:
                print 'ErrorL:', file, str(e)
                DBSession.rollback()
                
if __name__ == "__main__":
    main(sys.argv[1:])