#!/usr/bin/env python
# -*- coding:utf-8 -*-

import requests
from bs4 import BeautifulSoup
import re
import math
import time, threading
import os
import json
import sys
reload(sys)
sys.setdefaultencoding('utf-8')

ROOT = os.path.split(os.path.realpath(__file__))[0]

FPS = '{$}'

start_urls = []

def get_author_list(list_url):
    try:
        author_item = []
        url = 'http://so.gushiwen.org'
        html = requests.get(list_url).content
        soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
        for div in soup.find_all('div', class_ = 'sonspic'):
            item = {}
            href = div.p.a['href']
            item['author_url'] = url + href
            item['author_id'] = int(href.split('.')[0][8:])
            item['author_name'] = div.p.a.get_text()
            div_img = div.find('div', class_='divimg')
            if div_img :
                item['author_icon'] = div_img.img['src']
            else:
                item['author_icon'] = ''
            other = div.find_all('p')[1]
            item['description'] = other.get_text('|').split('|')[0]
            item['start_url'] = url + other.a['href']
            item['count'] = re.findall(r'\d+', other.a.get_text())[0]
            author_item.append(item)
        return author_item
    except Exception as e:
        log('get_author_list: ' + list_url + ' : ' + str(e))
        return False

#作者的详情页面地址抓取
def get_author_ziliao_all(author_url):
    try:
        data = []
        html = requests.get(author_url).content
        soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
        ziliao = soup.find_all('div', attrs={'id':re.compile(r"fanyi\d+")})
        if ziliao:
            for x in ziliao:
                id = x['id'][5:]
                zl = get_ajax_info_by_id(int(id), 'ziliao', author_url)
                if zl:
                    data.append(zl)
        return data
    except Exception as e:
        log('get_author_ziliao_all: ' + ' : ' + str(e))
        return False

#去除html标签
def trip_tag(s, tag):
    try:
        tags = [str(tag)] if type(tag)!=type([]) else tag
        for x in tags:
            r = re.compile('<\/?' + x + '.*?>')
            s = re.sub(r, '', s)
        return s
    except Exception as e:
        log('trip_tag: %s ==> %s'%(s, tag))
        return s



def log(msg):
    log_file = ROOT + '/gushici.log'
    with open(log_file, 'a') as f:
        f.write(msg + "\r\n")

def mkdir(path):
    if not os.path.exists(path):
        os.makedirs(path)
    return True

#根据id抓取ajax资料信息
def get_ajax_info_by_id(id, tp, pre_url=''):
    try:
        types = {'ziliao':'authors', 'fanyi':'shiwen2017', 'shangxi':'shiwen2017'}
        if not types.has_key(tp):
            raise Exception('tp is not exists: %s'%tp)
        data = {'id':id, 'type':tp, 'title':'', 'content':'', 'description':'', '_url':''}
        url = 'http://so.gushiwen.org/%s/ajax%s.aspx?id=%d'%(types[tp], tp, id)
        data['_url'] = url
        html = requests.get(url).content
        soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
        h2 = soup.find('h2')
        if not h2:
            raise Exception('get h2 error')
        data['title'] = h2.get_text()
        div_contyishang = soup.find('div', class_='contyishang')
        ps = div_contyishang.find_all('p')
        if ps:
            contents = []
            for p in ps:
                contents.append(trip_tag(unicode(p), 'a'))
            data['content'] = ''.join(contents)
        else:
            div_contyishang.div.clear()
            data['content'] = div_contyishang.get_text()

        cankao = soup.find('div', class_='cankao')
        if cankao:
            data['description'] = FPS.join(map(lambda x:x.get_text(), cankao.find_all('div')))
        return data
    except Exception as e:
        log('get_ajax_info_by_id::' + url + '::' + str(e) + '::' + pre_url)
        return False

def get_article_list_link_by_author(author_id, count):
    page_number = 10
    page_count = int(math.ceil(float(count)/float(page_number)))
    start_urls = []
    for page in range(1, page_count+1):
        start_urls.append('http://so.gushiwen.org/authors/authorsw_%dA%d.aspx'%(int(author_id), page))
    return start_urls

#文章的详情页面地址抓取
def get_article_info(article_url):
    try:
        data = []
        html = requests.get(article_url).content
        soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
        sons = soup.find_all('div', class_='sons')
        for x in sons:
            attrs = dict(x.attrs)
            if attrs.has_key('id'):
                idr = attrs['id']
                tp = re.sub(r'\d+', '', idr)
                if tp == 'fanyi' or tp == 'shangxi':
                    info = get_ajax_info_by_id(int(idr[len(tp):]), tp, article_url)
                    if info:
                        data.append(info)
            else:
                h2 = x.find('h2')
                if h2:
                    info = {'id':0, 'type':'other', 'title':'', 'content':'', 'description':''}
                    info['title'] = h2.get_text()
                    ps = x.find('div', class_='contyishang')
                    info['content'] = ps.get_text().replace(info['title'], '')
                    cankao = x.find('div', class_='cankao')
                    if cankao:
                        info['description'] = FPS.join(map(lambda x:x.get_text(), cankao.find_all('div')))
                    data.append(info)
        return data
    except Exception as e:
        log('get_article_info: ' + article_url + ' : ' + str(e))
        return False

#根据译注id获取 译注赏信息 yizhu_id
def get_article_yizhushang(yizhu_id, pre_url=''):
    try:

        data = {'yi': '', 'zhu': '', 'shang': '', 'cankao': '', 'yizhu_id': yizhu_id}
        cankao = []
        hr = '<p style=" color:#919090;margin:0px; font-size:12px;line-height:160%;">参考资料：</p>'

        url = 'http://so.gushiwen.org/shiwen2017/ajaxshiwencont.aspx?id=%d&value=yi' % yizhu_id
        html = requests.get(url).content
        ck = ''
        if len(html) != 0:
            soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
            ps = soup.find_all('p')
            if len(ps) > 1:
                cankao_div = soup.find_all('div')
                if len(cankao_div) > 0:
                    ck = FPS.join(map(lambda x: x.find_all('span')[1].get_text(), cankao_div))
                    ps = ps[:-1]
                data['yi'] = '{$$}'.join(map(lambda x: x.get_text(FPS), ps))
            else:
                body = unicode(soup).split(hr, 2)
                data['yi'] = BeautifulSoup(body[0], 'html.parser').get_text()
                if len(body) == 2:
                    cankao_div = BeautifulSoup(body[1], 'html.parser').find_all('div')
                    if len(cankao_div) > 0:
                        ck = FPS.join(map(lambda x: x.find_all('span')[1].get_text(), cankao_div))
        cankao.append(ck)

        url = 'http://so.gushiwen.org/shiwen2017/ajaxshiwencont.aspx?id=%d&value=zhu' % yizhu_id
        html = requests.get(url).content
        ck = ''
        if len(html) != 0:
            soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
            ps = soup.find_all('p')
            if len(ps) > 1:
                cankao_div = soup.find_all('div')
                if len(cankao_div) > 0:
                    ck = FPS.join(map(lambda x: x.find_all('span')[1].get_text(), cankao_div))
                    ps = ps[:-1]
                data['zhu'] = '{$$}'.join(map(lambda x: x.get_text(FPS), ps))
            else:
                body = unicode(soup).split(hr, 2)
                data['zhu'] = BeautifulSoup(body[0], 'html.parser').get_text()
                if len(body) == 2:
                    cankao_div = BeautifulSoup(body[1], 'html.parser').find_all('div')
                    if len(cankao_div) > 0:
                        ck = FPS.join(map(lambda x: x.find_all('span')[1].get_text(), cankao_div))
        cankao.append(ck)

        url = 'http://so.gushiwen.org/shiwen2017/ajaxshiwencont.aspx?id=%d&value=shang' % yizhu_id
        html = requests.get(url).content
        ck = ''
        if len(html) != 0:
            soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
            body = unicode(soup).split('<div class="hr"></div>', 2)
            if len(body) == 2:
                div_shang = BeautifulSoup(body[1], 'html.parser')
                div_cankao = div_shang.find_all('div')
                if div_cankao:
                    data['shang'] = ''.join(map(lambda x: unicode(x), div_shang.find_all('p')[:-1]))
                    ck = FPS.join(map(lambda x: x.find_all('span')[1].get_text(), div_cankao))
                else:
                    data['shang'] = ''.join(map(lambda x: unicode(x), div_shang.find_all('p')))
        cankao.append(ck)
        data['cankao'] = '{$$}'.join(cankao)
        return data
    except Exception as e:
        log('get_article_yizhushang: ' + str(yizhu_id) + ' : ' + str(e))
        return False

def get_articles_by_list(list_url):
    try:
        data = []
        domain = 'http://so.gushiwen.org'
        html = requests.get(list_url).content
        soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
        for div in soup.find_all('div', class_ = 'sons'):
            item = {}
            cont = div.find('div', class_ = 'cont')
            cont_p = cont.find_all('p')
            item['article_url'] = domain + cont_p[0].a['href']
            #item['author_id'] = int(href.split('.')[0][8:])
            item['article_name'] = cont_p[0].get_text()
            cont_p_a = cont_p[1].find_all('a')
            item['chaodai'] = cont_p_a[0].get_text()
            item['author'] = cont_p_a[1].get_text()
            contson = cont.find('div', class_='contson')
            item['content'] = trip_tag(unicode(contson), 'div')
            item['yizhu_id'] = int(contson['id'][7:])
            #获取译注信息
            item['yizhushang'] = get_article_yizhushang(item['yizhu_id'], item['article_url'])
            #在文章详情获取赏析等信息
            item['shangxi'] = get_article_info(item['article_url'])
            tag = div.find('div', class_='tag')
            item['tags'] = []
            if tag:
                item['tags'] = map(lambda x: x.get_text(), tag.find_all('a'))
            data.append(item)
        return data
    except Exception as e:
        log('get_articles_by_list: ' + list_url + ' : ' + str(e))
        return False


class myThread(threading.Thread):  # 继承父类threading.Thread
    def __init__(self, threadID, name):
        threading.Thread.__init__(self)
        self.threadID = threadID
        self.name = name
        self._acount = 0
        self._start_time = time.time()

    def run(self):  # 把要执行的代码写到run函数里面 线程在创建后会直接运行run函数
        print self.name + ' start time :' + time.strftime('%Y-%m-%d %H:%M:%S')
        while len(start_urls) != 0:
            start_url = start_urls.pop(0)
            self.get_author_article(start_url)
            time.sleep(1)
        print self.name + ' finish : ' + str(self._acount) + ', exec time: ' + str(time.time()-self._start_time)
        print self.name + ' end time :' + time.strftime('%Y-%m-%d %H:%M:%S')

    def get_author_article(self, start_url):
        authors = get_author_list(start_url)
        for author in authors:
            path = ROOT + '/json/author_' + str(author['author_id'])
            mkdir(path)
            author_file = path + '/author.json'
            #读取作者详情所有资料
            author['ziliao'] = get_author_ziliao_all(author['author_url'])
            self.wjson(author_file, author)
            #读取所有文章
            self.get_articles(author, path)
            self._acount = self._acount + 1
            time.sleep(1)

    def get_articles(self, author, path):
        urls = get_article_list_link_by_author(author['author_id'], author['count'])
        for url in urls:
            article_list = get_articles_by_list(url)
            for x in article_list:
                file_name = path + '/' + url.split('/')[-1][:-5] + '_'+str(x['yizhu_id'])+'.json'
                self.wjson(file_name, x)
            time.sleep(1)

    def wjson(self, filename, data):
        msg = json.dumps(data)
        with open(filename, 'a') as f:
            f.write(msg)


if __name__ == '__main__':

    for page in range(1, 317):
        start_urls.append('http://so.gushiwen.org/authors/Default.aspx?p=%d&c=' % page)
    tn = 8
    for i in range(1, tn+1):
        td = myThread(i, "Thread-"+str(i))
        td.start()
    print 'game over'
