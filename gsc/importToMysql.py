#!/usr/bin/env python
# -*- coding:utf-8 -*-

import os
import pymysql
import json

ROOT = os.path.split(os.path.realpath(__file__))[0]

JSON_FILE = ROOT + '/json'

#DB = pymysql.connect('localhost', 'root', 'Xdy(x#123kt', 'gushici')
# 使用 cursor() 方法创建一个游标对象 cursor
#CURSOR = DB.cursor()


def log(msg):
    log_file = ROOT + '/importMysql.log'
    with open(log_file, 'a') as f:
        f.write(msg + "\r\n")

def insert(tbl, data):
    if len(data) == 0:
        log('insert in empty:%s => %s'%(tbl, json.dumps(data)))
        return False
    if type(data) == type([]):
        pass
    else:
        pass
    pass

def get_author_dir(path):
    path = path.rstrip('/')
    files = os.listdir(path)
    author_dirs = []
    for file in files:
        # 得到该文件下所有目录的路径
        author_dir = os.path.join(path, file)
        if os.path.isdir(author_dir):
            author_dirs.append(author_dir)
    return author_dirs

def get_author_files(path):
    path = path.rstrip('/')
    files = os.listdir(path)
    return map(lambda x:os.path.join(path, x), files)

def import_author(author):
    pass

class myDb():

    def __init__(self, **kwargs):
        try:
            self.__db = pymysql.connect(kwargs['host'],kwargs['user'],kwargs['password'],kwargs['database'] )
            self.__cursor = self.__db.cursor()
        except Exception,e:
            print('mysql execute: ' + str(e))

    def execute(self, sql, id=False):
        try:
            self.__cursor.execute(sql)
            self.__db.commit()
            return self.__cursor.lastrowid
        except Exception as e:
            log(sql)
            log(str(e))
            self.__db.rollback()
            return False

    #data 数组或者字典
    def insert(self, tbl, data):
        if len(data) == 0:
            log('insert in empty:%s => %s' % (tbl, json.dumps(data)))
            return False
        if type(data) == type({}):
            data = self.getInsStr(data)
            sql = 'INSERT INTO ' + tbl + ' SET ' + data
            # print(sql)
            # exit()
            return self.execute(sql, True)
        else:
            str = ','.join(map(lambda x:'"%s"'%str(x), data))
            sql = 'INSERT INTO ' + tbl + ' VALUES (' + str + ')'
            return self.execute(sql, True)
    #data 数组或者字典
    def insert_muti(self, tbl, data):
        if len(data) == 0:
            log('insert in empty:%s => %s' % (tbl, json.dumps(data)))
            return False
        if type(data[0]) == type({}):
            k = ''
            v = []
            for x in data:
                ks, vs = self.getInsArr(data)
                k = ks
                v.append(vs)
            sql = 'INSERT INTO %s(%s) VALUES %s'%(tbl, k, ','.join(map(lambda _x:'(%s)'%_x), v))
            # print(sql)
            # exit()
            return self.execute(sql)
        else:
            vs = []
            for x in data:
                str = ','.join(map(lambda _x:'"%s"'%str(_x), x))
                vs.append(str)
            v = ','.join(map(lambda _x: '(%s)' % _x), vs)
            sql = 'INSERT INTO ' + tbl + ' VALUES' + v
            # print(sql)
            # exit()
            return self.execute(sql)

    @staticmethod
    def getInsStr(data):
        string = an = ''
        for k in data :
            string += an +'`' + k + '`="%s"' % data[k]
            an = ','
        return string

    @staticmethod
    def getInsArr(data):
        ks = vs = []
        for k in data :
            ks.append(k)
            vs.append(data[k])
        return (','.join(map(lambda x:'`%s`'%str(x), ks)), ','.join(map(lambda x:'"%s"'%str(x), vs)))

    def __del__(self):
        if self.__db:
            self.__db.close()
if __name__ == '__main__':
    db = myDb(host='47.93.255.190', user='xin', password='XIN~!@#$%^&*123', database='poetry')
    data = {'name':'wuxin', 'author_id':1, 'content':'test'}
    print db.insert('article', data)