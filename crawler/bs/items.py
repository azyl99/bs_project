# -*- coding: utf-8 -*-

# Define here the models for your scraped items
#
# See documentation in:
# http://doc.scrapy.org/en/latest/topics/items.html

import scrapy


class BsItem(scrapy.Item):
    # define the fields for your item here like:
    title = scrapy.Field()  # 标题
    text = scrapy.Field()   # 简介
    link = scrapy.Field()
    type = scrapy.Field()
    subtype = scrapy.Field()
    time = scrapy.Field()
    source = scrapy.Field()
    pass
