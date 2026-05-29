import pymysql

def get_db():
    return pymysql.connect(
        host="localhost",
        user="root",
        password="",
        database="sm-sekolah",
        cursorclass=pymysql.cursors.DictCursor
    )
