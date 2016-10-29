from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm.exc import NoResultFound
from sqlalchemy.orm import (
    scoped_session,
    sessionmaker,
    relationship,
    backref
    )

DBSession = scoped_session(sessionmaker())
Base = declarative_base()
