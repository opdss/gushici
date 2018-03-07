#!/usr/bin/env python
# -*- coding:utf-8 -*-

import scrapy
from gushici.items import GushiciItem

class GushiciSpider(scrapy.Spider):
    name = 'gushici'
    allowed_domains = ["gushiwen.org"]
    start_urls = []

    url = 'http://so.gushiwen.org'

    def __init__(self):
        for page in range(1, 317):
            self.start_urls.append('http://so.gushiwen.org/authors/Default.aspx?p=%d&c='%page)

    def parse(self, response):
        div = response.css('div[class=sonspic]')
        items = []
        for sel in div:
            author_icon = sel.css('div[class=divimg] img::attr(src)').extract_first(default='')
            author_url = sel.css('div[class=cont] p[style=height\:22px\;] a[target=_blank]::attr(href)').extract_first()
            author_name = sel.css('div[class=cont] p[style=height\:22px\;] a[target=_blank] b::text').extract_first()
            item = GushiciItem()
            item['author_url'] = self.url + author_url
            item['author_icon'] = author_icon
            item['author_name'] = author_name
            items.append(item)
        return items
