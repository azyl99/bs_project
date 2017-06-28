# -*- coding: utf-8 -*-

# Define your item pipelines here
#
# Don't forget to add your pipeline to the ITEM_PIPELINES setting
# See: http://doc.scrapy.org/en/latest/topics/item-pipeline.html

import pymysql


class BsPipeline(object):
    def open_spider(self, spider):
        self.con = pymysql.connect(user='azyl99', passwd='azyl99', db='bs',
                                   host='localhost', charset='utf8', use_unicode=True)
        self.cu = self.con.cursor()

    def process_item(self, item, spider):
        insert_sql = "insert into news(title,text,link,type,subtype,time,source) values('{}','{}','{}','{}','{}','{}','{}') " \
            .format(item['title'], item['text'], item['link'], item['type'], item['subtype'], item['time'], item['source'])
        try:
            self.cu.execute(insert_sql)
            self.con.commit()  # 查询不需要，更改要commit
        except Exception as e:
            pass
            # print('Duplicated titles!')
        return item

    def spider_close(self, spider):
        self.con.close()
