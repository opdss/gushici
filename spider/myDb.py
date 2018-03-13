#!/usr/bin/env python
# -*- coding:utf-8 -*-

import pymysql

class db(object):

    coun = 0

    def __init__(self, host, port, user, passwd, dbname, charset, timeout=5):
        self.host = host
        self.port = port
        self.user = user
        self.passwd = passwd
        self.dbname = dbname
        self.timeout = timeout
        self.charset = charset
        try:
            self.__conn = MySQLdb.connect(host=self.host,user=self.user,passwd=self.passwd,port=int(self.port),connect_timeout=self.timeout,charset=self.charset)
            self.__conn.select_db(self.dbname)
            self.__curs = self.__conn.cursor()
            self.__class__.coun += 1
        except Exception,e:
            print('mysql execute: ' + str(e))

    def execute(self,sql,param=''):
        if param <> '':
            self.__curs.execute(sql,param)
        else:
            self.__curs.execute(sql)
        return self.__conn.commit()

    def query(self,sql):
        count = self.__curs.execute(sql)
        if count == 0 :
            res = 0
        else:
            res = self.__curs.fetchall()
        return res

    def insert(self, tbname, data):
        data = self.getInsStr(data)
        sql = 'INSERT INTO ' + tbname + ' SET ' + data
        #print(sql)
        #exit()
        return self.execute(sql)

    def update(self, tbname, **kwargs):
        where = ''
        if isinstance(kwargs['where'], str) == True :
            where = kwargs['where']
        elif isinstance(kwargs['where'], dict) == True :
            contor = kwargs['contor'] if 'contor' in kwargs.keys() else 'and'
            where = self.getWhereStr(kwargs['where'],contor)
        data = ''
        if isinstance(kwargs['data'], str) == True :
            data = kwargs['data']
        elif isinstance(kwargs['data'], dict) == True :
            data = self.getInsStr(kwargs['data'])

        if where == '' or data == '' :
            return False
        sql = 'UPDATE ' + tbname + ' SET ' + data + ' WHERE ' + where
        return self.execute(sql)

    def delete(self, tbname, **kwargs):
        where = kwargs['where'] if 'where' in kwargs.keys()  else ' 1 '
        if isinstance(where, dict) == True :
            contor = kwargs['contor'] if 'contor' in kwargs.keys() else 'and'
            where = self.getWhereStr(where, contor)
        if where == '' :
            return False
        sql = 'DELETE FROM ' + tbname + ' WHERE ' + where
        return self.execute(sql)

    def list(self, tbname, **kwargs):
        select = kwargs['select'] if 'select' in kwargs.keys() else ' * '
        if isinstance(select, tuple) == True :
            _select = an = ''
            for x in select :
                _select += an + x
                an = ', '
            select = _select
        where = kwargs['where'] if 'where' in kwargs.keys() else ' 1 '
        where = self.getWhereStr(where) if isinstance(where, dict) else where
        order = kwargs['order'] if 'order' in kwargs.keys() else ''
        if isinstance(order, dict) == True :
            _order = an = ''
            for k in order :
                _order += an + k + ' ' + order[k]
                an = ', '
            order = _order
        order = (' ORDER BY %s ' % order) if order <> '' else ''
        limit = kwargs['limit'] if 'limit' in kwargs.keys() else ''
        limit = str(limit[0]) + ',' + str(limit[1]) if isinstance(limit, tuple) else limit
        limit = '' if limit=='' else ' LIMIT ' + limit
        #sql = 'SELECT ' + select + ' FROM ' + tbname + ' WHERE ' + where + order + ' limit ' + limit
        sql = 'SELECT ' + select + ' FROM ' + tbname + ' WHERE ' + where + order + limit
        #print(sql)
        return self.query(sql)
    def count(self, tbname, **kwargs):
        where = kwargs['where'] if 'where' in kwargs.keys()  else None
        if isinstance(where, dict) == True :
            contor = kwargs['contor'] if 'contor' in kwargs.keys() else 'and'
            where = self.getWhereStr(where, contor)
        if where == None:
            where = ''
        else:
            where = ' WHERE ' + where
        sql = 'SELECT count(*) as cc FROM ' + tbname + where
        return self.query(sql)[0][0]

    @staticmethod
    def getInsStr(data):
        string = an = ''
        for k in data :
            string += an +'`' + k + '`="%s"' % data[k]
            an = ','
        return string

    @staticmethod
    def getWhereStr(data, contor='and'):
        string = an = ''
        for k in data :
            string += an + ' `' + k + '`=' + (str(data[k])+' ' if isinstance(data[k], int) else '"' + str(data[k]) + '" ')
            an = contor
        return string

    @classmethod
    def getCoun(cls):
        print(' %s : coun=%s' % (cls.__name__, cls.coun))

    def __del__(self):
        #print('pid:%s is over' % (os.getpid()))
        self.__conn.close()
        self.__curs.close()

if __name__ == '__main__' :

    '''
    mdb = db('127.0.0.1','3306','root','123456','lantouzi','utf8')
    #print mdb.execute('INSERT INTO ltz_lanrenjihua_info SET `lrId`="8",`details`="gaweg",`riskManagement`="test"')
    res = mdb.insert('ltz_lanrenjihua_info',{'lrId' : 14, 'details' : 'gaweg', 'riskManagement' : 'test'})
    print(res)

    mdb = db('127.0.0.1','3306','root','123456','lepus','utf8')
    mdb2 = db('192.168.100.179','3306','root','123456','lepus','utf8')
    #res = mdb.mysql_query('show tables')
    #print(res)
    print mdb.execute('delete from mysql_connected where id=6')
    print mdb.update('mysql_connected',where={'id':5}, data='tags="fffff"')
    print mdb.delete('mysql_connected',where={'id':8})
    print mdb.list('mysql_connected',where={'id':5},order={'id':'ASC'},limit=(0,3))
    #print db.getWhereStr({'a':2,'b':3},'or')
    db.getCoun()
    mdb.__class__.getCoun()
    '''
