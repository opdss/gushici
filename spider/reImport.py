#!/usr/bin/env python
# -*- coding:utf-8 -*-

import os
import pymysql
import json
import sys
import time
reload(sys)
sys.setdefaultencoding('utf-8')

ROOT = os.path.split(os.path.realpath(__file__))[0]

db = None

def log(msg, file_name=''):
    log_file = ROOT + '/reImport.log' if file_name == '' else file_name
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

def import_tags(tags):
    for tag in tags:
        res = db.query('select * from tags where name="%s"'%tag)
        if res:
            tag_id = int(res[0][0])
        else:
            tag_id = db.insert('tags', {'name':tag})

        if not tag_id:
            log('insert tag error:' + tag + ' : ' + json.dumps(tags[tag]))
            continue
        tag_data = []
        for a_id in tags[tag]:
            tag_data.append({'article_id': a_id, 'tag_id': tag_id})
        db.insert_muti('article_tag', tag_data)

def import_ziliao_shangxi(res, map_id):
    data = []
    for x in res:
        item = {
            'map_id': map_id,
            'title': x['title'],
            'content': pymysql.escape_string(x['content'].strip("\n")),
            'references': x['description'].strip("\n"),
            'type': x['type'],
            '_id': x['id'],
            '_url': x['_url'] if x.has_key('_url') else '',
        }
        data.append(item)
    db.insert_muti('documents', data)

def import_yizhushang(res, article_id):
    data = {
        'article_id': article_id,
        'yi': pymysql.escape_string(res['yi'].strip("\n")),
        'zhu': pymysql.escape_string(res['zhu'].strip("\n")),
        'shang': pymysql.escape_string(res['shang'].strip("\n")),
        'cankao': pymysql.escape_string(res['cankao'].strip("\n")),
        '_id': res['yizhu_id'],
    }
    db.insert('yizhushang', data)

class myDb():

    def __init__(self, **kwargs):
        try:
            self.__db = pymysql.connect(host=kwargs['host'], user=kwargs['user'], password=kwargs['password'], database=kwargs['database'], charset='utf8')
            self.__cursor = self.__db.cursor()
        except Exception as e:
            print('mysql execute: ' + str(e))
            exit()

    def execute(self, sql, id=False):
        try:
            self.__cursor.execute(sql)
            self.__db.commit()
            return self.__cursor.lastrowid
        except Exception as e:
            sql_file = ROOT + '/' + 'exec_error.sql'
            tt = '##' + str(time.time())
            log(tt, sql_file)
            log(sql.encode('utf-8') + ';', sql_file)
            log(tt + str(e))
            return False

    def query(self, sql):
        count = self.__cursor.execute(sql)
        if count == 0:
            res = 0
        else:
            res = self.__cursor.fetchall()
        return res
    #data 数组或者字典
    def insert(self, tbl, data):
        if len(data) == 0:
            log('insert in empty:%s => %s' % (tbl, json.dumps(data)))
            return False
        if type(data) == type({}):
            data = self.getInsStr(data)
            sql = 'INSERT INTO ' + tbl + ' SET ' + data
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
                ks, vs = self.getInsArr(x)
                k = ks
                v.append(vs)
            sql = 'INSERT INTO %s(%s) VALUES %s'%(tbl, k, ','.join(map(lambda _x:'(%s)'%_x, v)))
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
        ks = []
        vs = []
        for k in data:
            ks.append(k)
            vs.append(data[k])
        return (','.join(map(lambda x:'`%s`'%str(x), ks)), ','.join(map(lambda x:'"%s"'%str(x), vs)))

    def __del__(self):
        #if self.__db:
        #    self.__db.close()
        pass
def read_file(file):
    try:
        with open(file) as fp:
            contents = fp.read()
        return contents
    except Exception as e:
        log('file read error:' + file + str(e))
        return ''

def get_file_json(file, type=''):
    try:
        with open(file) as fp:
            contents = fp.read()
        try:
            return json.loads(contents, encoding='utf-8')
        except Exception as e1:
            return json.loads(contents[:int(len(contents) / 2)])

    except Exception as e:
        log('get_file_json:'+file+' => ' + str(e))
        return False

if __name__ == '__main__':

    import_log_file = ROOT + '/importMysql.log'

    db = myDb(host='localhost', user='root', password='X7SBMixvFKLKUIL*a@cWX', database='gushici_new')

    fp = open(import_log_file)

    dirs = []
    while 1:
        line = fp.readline()
        if not line:
            break
        l1 = line.split(':', 2)
        if l1[0] == 'get_file_json':
            f = l1[1].split('=>', 2)[0].strip(' ')
            dirs.append(f[:-12])
    fp.close()

    #存储所有标签
    tags = {}
    for author_dir in dirs:
        path = author_dir+'/'
        files = os.listdir(author_dir)
        files.sort()
        author_file = files[0]
        if author_file != 'author.json':
            log('author error: '+ author_dir)
            continue
        author = get_file_json(path + author_file)
        if not author:
            continue
        author_data = {
            'name' : author['author_name'],
            'description' : pymysql.escape_string(author['description'].strip("\n")),
            '_url' : author['author_url'],
            '_icon' : author['author_icon'],
            '_id' : author['author_id'],
            '_count' : author['count'],
        }
        author_id = db.insert('authors', author_data)
        if not author_id:
            log('insert author error:' + author_dir)
            continue
        if len(author['ziliao']) > 0:
            import_ziliao_shangxi(author['ziliao'], author_id)
        #下面开始入文章
        del(files[0])
        for file in files:
            article_file = path + file
            article = get_file_json(article_file)
            if not article:
                continue
            article_data = {
                'title' : article['article_name'],
                'content' : pymysql.escape_string(article['content'].strip("\n")),
                'author_id' : author_id,
                'author_name' : article['author'],
                'dynasty' : article['chaodai'],
                '_url' : article['article_url'],
                '_yizhu_id' : article['yizhu_id'],
            }
            article_id = db.insert('articles', article_data)
            if not article_id:
                log('insert article error:' + article_file)
                continue

            if article.has_key('yizhushang') and article['yizhushang']:
                yizhushang = article['yizhushang']
                if yizhushang['yi'] != '' or yizhushang['yi'] != '' or yizhushang['yi'] != '':
                    import_yizhushang(article['yizhushang'], article_id)

            if len(article['shangxi']) > 0:
                import_ziliao_shangxi(article['shangxi'], article_id)

            if len(article['tags']) > 0:
                for t in article['tags']:
                    if not tags.has_key(t):
                        tags[t] = []
                    tags[t].append(article_id)
    log(json.dumps(tags), ROOT+'/'+'retags.json')
    import_tags(tags)







