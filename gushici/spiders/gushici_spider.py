#!/usr/bin/env python
# -*- coding:utf-8 -*-

import scrapy

class GushiciSpider(scrapy.Spider):
    name = 'gushici'
    allowed_domains = ["gushiwen.org"]
    start_urls = [
        "http://so.gushiwen.org/authors/",
        "http://www.dmoz.org/Computers/Programming/Languages/Python/Resources/"
    ]

    def parse(self, response):
        filename = response.url.split("/")[-2]
        with open(filename, 'wb') as f:
            f.write(response.body)
