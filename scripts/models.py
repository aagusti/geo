#from zope.sqlalchemy import ZopeTransactionExtension
#import transaction
#extension=ZopeTransactionExtension()
from base_model import Base
from sqlalchemy import (
    create_engine,
    MetaData,
    Table,
    select,
    func,
    String,
    Integer,
    BigInteger,
    Column
    )
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from geoalchemy2 import Geometry

TABLE_ARGS = dict(autoload=True,
                  schema='sig')
                  
class DBProfile(object):
    def __init__(self, db_url):
        self.engine = create_engine(db_url)
        self.metadata = MetaData(self.engine)
        base_session = sessionmaker(bind=self.engine)
        self.session = base_session()

    def query(self, *args):
        return self.session.query(*args)

    def commit(self, row):
        self.session.add(row)
        self.session.commit()

    def execute(self, sql):
        return self.engine.execute(sql)

    def get_table(self, tablename, schema=None):
        return Table(tablename, self.metadata, autoload=True, schema=schema)

    def get_count(self, table):
        sql = select([func.count()]).select_from(table)
        q = self.execute(sql)
        return q.scalar()

class SigKecamatan(Base):
    __tablename__ = 'sig_kecamatan'
    __table_args__ = TABLE_ARGS
    gid = Column(BigInteger, primary_key=True)
    kd_propinsi = Column(String(2))
    kd_kecamatan = Column(String(3))
    nm_kecamatan = Column(String(50))
    geom = Column(Geometry())
    
class SigKelurahan(Base):
    __tablename__ = 'sig_kelurahan'
    __table_args__ = TABLE_ARGS
    
class SigBlok(Base):
    __tablename__ = 'sig_blok'
    __table_args__ = TABLE_ARGS

class SigBumi(Base):
    __tablename__ = 'sig_bumi'
    __table_args__ = TABLE_ARGS

class SigBng(Base):
    __tablename__ = 'sig_bng'
    __table_args__ = TABLE_ARGS
    