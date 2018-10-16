#!/usr/bin/env python
# -*- coding:utf-8 -*-

import requests
import math
import time, threading
import os
import sys
from bs4 import BeautifulSoup
reload(sys)
sys.setdefaultencoding('utf-8')

ROOT = os.path.split(os.path.realpath(__file__))[0]

API = 'http://panda.www.net.cn/cgi-bin/check.cgi?area_domain=';

def getZm():
    zm = []
    for x in range(97, 123):
        zm.append(chr(x))
    return zm

def genDomain():
    zm = getZm()
    for a in zm:
        for b in zm:
            for c in zm:
                for d in zm:
                    for e in zm:
                        yield "%s%s%s%s%s"%(a,b,c,d,e)


def log(msg, file='domain.log'):
    log_file = ROOT + '/' + file;
    with open(log_file, 'a') as f:
        f.write(msg + "\r\n")


ZM = genDomain()

class myThread(threading.Thread):  # 继承父类threading.Thread
    def __init__(self, threadID, name):
        threading.Thread.__init__(self)
        self.threadID = threadID
        self.name = name
        self._acount = 0
        self._start_time = time.time()

    def run(self):  # 把要执行的代码写到run函数里面 线程在创建后会直接运行run函数
        print self.name + ' start time :' + time.strftime('%Y-%m-%d %H:%M:%S')
        doname = ZM.next()
        while doname:
            for x in ['.com', '.net', '.cn']:
                dd = doname+x
                soup = BeautifulSoup(requests.get(API+dd).content, 'xml', from_encoding='utf-8')
                data = soup.original.get_text()
                if data[0:3] == 210:
                    logfile = 'domain-success.log'
                else:
                    logfile = 'domain-error.log'
                log('%s => %s'%(dd, data), logfile)

        print self.name + ' finish : ' + str(self._acount) + ', exec time: ' + str(time.time()-self._start_time)
        print self.name + ' end time :' + time.strftime('%Y-%m-%d %H:%M:%S')


if __name__ == '__main__':
    tn = 10
    for i in range(1, tn+1):
        td = myThread(i, "Thread-"+str(i))
        td.start()
    print 'game over'