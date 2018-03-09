#!/usr/bin/env python
# -*- coding:utf-8 -*-

import requests
from bs4 import BeautifulSoup
import re
import math
import traceback
import time, threading
import os
import json

ROOT = os.path.split(os.path.realpath(__file__))[0]

FPS = '{$}'

start_urls = []
for page in range(1, 6):
    start_urls.append('http://so.gushiwen.org/authors/Default.aspx?p=%d&c=' % page)

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
        log('get_author_list: ' + list_url)
        log(repr(e))
        log(traceback.format_exc())
        return False

#作者的详情页面地址抓取
def get_author_ziliao_all(author_url):
    data = []
    html = requests.get(author_url).content
    soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
    ziliao = soup.find_all('div', attrs={'id':re.compile(r"fanyi\d+")})
    for x in ziliao:
        id = x['id'][5:]
        zl = get_ajax_info_by_id(int(id), 'ziliao')
        data.append(zl)
    return data

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
                    info = get_ajax_info_by_id(int(idr[len(tp):]), tp)
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
        log(article_url)
        log(repr(e))
        log(traceback.format_exc())
        return False

#去除html标签
def trip_tag(s, tag):
    tags = [str(tag)] if type(tag)!=type([]) else tag
    for x in tags:
        r = re.compile('<\/?' + x + '.*?>')
        s = re.sub(r, '', s)
    return s

def log(msg):
    log_file = ROOT + '/gushici.log'
    with open(log_file, 'a') as f:
        f.write(msg + "\r\n")

#根据id抓取ajax资料信息
def get_ajax_info_by_id(id, tp):
    try:
        types = {'ziliao':'authors', 'fanyi':'shiwen2017', 'shangxi':'shiwen2017'}
        if not types.has_key(tp):
            raise Exception('不存在的%s'%tp)
        data = {'id':id, 'type':tp, 'title':'', 'content':'', 'description':''}
        url = 'http://so.gushiwen.org/%s/ajax%s.aspx?id=%d'%(types[tp], tp, id)
        html = requests.get(url).content
        soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
        h2 = soup.find('h2')
        if not h2:
            raise Exception('%s抓取失败'%url)
        data['title'] = h2.get_text()
        ps = soup.find('div', class_='contyishang').find_all('p')
        contents = []
        for p in ps:
            contents.append(trip_tag(unicode(p), 'a'))
        data['content'] = ''.join(contents)

        cankao = soup.find('div', class_='cankao')
        if cankao:
            data['description'] = FPS.join(map(lambda x:x.get_text(), cankao.find_all('div')))
        return data
    except Exception as e:
        log(url)
        log(repr(e))
        log(traceback.format_exc())
        return False

def get_article_list_link_by_author(author_id, count):
    page_number = 10
    page_count = int(math.ceil(float(count)/float(page_number)))
    start_urls = []
    for page in range(1, page_count+1):
        start_urls.append('http://so.gushiwen.org/authors/authorsw_%dA%d.aspx'%(int(author_id), page))
    return start_urls

#根据译注id获取 译注赏信息 yizhu_id
def get_article_yizhushang(yizhu_id):
    data = {}
    url = 'http://so.gushiwen.org/shiwen2017/ajaxshiwencont.aspx?id=%d&value=yizhushang'%yizhu_id
    html = requests.get(url).content
    if len(html) == 0:
        url = 'http://so.gushiwen.org/shiwen2017/ajaxshiwencont.aspx?id=%d&value=yizhu' % yizhu_id
        html = requests.get(url).content
    soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
    body = unicode(soup).split('<div class="hr"></div>', 2)
    body_0 = BeautifulSoup(body[0], 'html.parser')
    data['yizhu'] = map(lambda x:x.get_text(FPS), body_0.find_all('p'))
    data['shang'] = ''
    data['cankao'] = []
    data['yizhu_id'] = yizhu_id
    if len(body) == 2:
        div_shang = BeautifulSoup(body[1], 'html.parser')
        div_cankao = div_shang.find_all('div')
        if div_cankao:
            data['shang'] = ''.join(map(lambda x:unicode(x), div_shang.find_all('p')[:-1]))
            data['cankao'] = FPS.join(map(lambda x:x.get_text(), div_cankao))
        else:
            data['shang'] = ''.join(map(lambda x:unicode(x), div_shang.find_all('p')))
    else:
        div_cankao = body_0.find_all('div')
        if div_cankao:
            data['cankao'] = FPS.join(map(lambda x: x.get_text(), div_cankao))
    return data

def get_article_list(url):
    try:
        data = []
        domain = 'http://so.gushiwen.org'
        html = requests.get(url).content
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
            item['content'] = contson.extract()
            #item['yizhu_id'] = int(contson['id'][7:])
            item['yizhu'] = get_article_yizhushang(item['yizhu_id'])
            tag = div.find('div', class_='tag')
            item['tags'] = []
            if tag:
                item['tags'] = map(lambda x: x.get_text(), tag.find_all('a'))
            data.append(item)
        return data
    except Exception as e:
        log(e)
        return False


class myThread(threading.Thread):  # 继承父类threading.Thread
    def __init__(self, threadID, name):
        threading.Thread.__init__(self)
        self.threadID = threadID
        self.name = name

    def run(self):  # 把要执行的代码写到run函数里面 线程在创建后会直接运行run函数
        while len(start_urls) != 0:
            start_url = start_urls.pop(0)
            self.get_author_article(start_url)
            time.sleep(2)
        print self.name + ' finish ！'

    def get_authors(self, list_url):
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
            yield item

    def get_author_article(self, start_url):
        authors = self.get_authors(start_url)
        for author in authors:
            data = {}
            json_file = ROOT + '/json/author_' + str(author['author_id']) + '.json'
            data['article'] = self.get_article(author)
            author['ziliao'] = get_author_ziliao_all(author['author_url'])
            data['author'] = author
            self.wjson(json_file, data)
            time.sleep(1)
    def get_articles(self, author):
        pass

    def get_article(self, author):
        urls = get_article_list_link_by_author(author['author_id'], author['count'])
        articles = []
        for url in urls:
            article = get_article_info(url)
            articles.append(article)
            time.sleep(1)
        return articles

    def wjson(self, filename, data):
        msg = json.dumps(data)
        with open(filename, 'a') as f:
            f.write(msg)

def get_all_authors():
    for list_url in start_urls:
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
            yield item

if __name__ == '__main__':
    tn = 5
    for i in range(1, tn+1):
        td = myThread(i, "Thread-"+str(i))
        td.start()
    print 'game over'
