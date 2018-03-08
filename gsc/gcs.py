#!/usr/bin/env python
# -*- coding:utf-8 -*-

import requests
from bs4 import BeautifulSoup
import re

start_urls = []
for page in range(1, 317):
    start_urls.append('http://so.gushiwen.org/authors/Default.aspx?p=%d&c=' % page)

def get_author_list(list_url):
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

def get_author_info(author_url):
    html = requests.get(author_url).content
    print html

def trip_tag(s, tag):
    tags = [str(tag)] if type(tag)!=type([]) else tag
    for x in tags:
        r = re.compile('<\/?' + x + '.*?>')
        s = re.sub(r, '', s)
    return s
def log(msg):
    print msg
    pass

def get_author_ziliao(id):
    try:
        data = {'id':id, 'title':'', 'content':'', 'description':''}
        url = 'http://so.gushiwen.org/authors/ajaxziliao.aspx?id=%s'%str(id)
        html = requests.get(url).content
        soup = BeautifulSoup(html, 'html.parser', from_encoding='utf-8')
        data['title'] = soup.div.h2.get_text()
        ps = soup.find('div', class_='contyishang').find_all('p')
        contents = []
        for p in ps:
            contents.append(trip_tag(unicode(p), 'a'))
        data['content'] = ''.join(contents)

        cankao = soup.find('div', class_='cankao')
        if cankao:
            data['description'] = cankao.get_text()
        return data
    except Exception as e:
        log(e)
        return False

#print get_author_list('http://so.gushiwen.org/authors/')
get_author_info('http://so.gushiwen.org/author_12.aspx')
#print get_author_ziliao(454)