import scrapy
import logging
from scrapy.http import Request
from ..items import BsItem
from lxml import etree
from datetime import datetime
from datetime import timedelta


types_table = {
    '体育频道-新浪RSS': '体育',
    '军事频道-新浪RSS': '军事',
    '博客频道-新浪RSS': '博客',
    '男性频道-新浪RSS': '男性',
    '女性频道-新浪RSS': '女性',
    '影音娱乐-新浪RSS': '娱乐',
    '房地产-新浪RSS': '房产',
    '教育频道-新浪RSS': '教育',
    '新闻中心-新浪RSS': '新闻',
    '星座频道-新浪RSS': '星座',
    '汽车新闻-新浪RSS': '汽车',
    '游戏频道-新浪RSS': '游戏',
    '科技频道-新浪RSS': '科技',
    '视频-新浪RSS':'视频',
    '读书频道-新浪RSS': '读书',
    '财经频道-新浪RSS': '财经',
}

class SinaSpider(scrapy.Spider):
    name = "sina"
    start_urls = ['http://rss.sina.com.cn/sina_all_opml.xml']

    def parse(self, response):
        root = etree.XML(response.body)
        items = []
        for child in root[1]:
            ntype = child.get('title')
            mtype = types_table.get(ntype, 'none') # D.get(k[,d]) -> D[k] if k in D, else d.  d defaults to None.
            if mtype == 'none':
                logging.error('[%s] MyTypeError: %s', self.name, ntype)
            for child2 in child:
                item = BsItem()
                item['type'] = mtype
                item['subtype'] = child2.get('title')
                item['link'] = child2.get('xmlUrl')
                item['source'] = 'sina'
                items.append(item)

        for item in items:
            yield Request(item['link'], meta={'item': item}, callback=self.parse2)

    def parse2(self, response):
        item = response.meta['item']  # parse传过来的
        root = etree.XML(response.body)
        for child in root.find('channel'):
            if child.tag != 'item':
                continue
            link = child.find('link').text
            title = child.find('title').text.strip()
            text = child.find('description').text.strip()
            time = child.find('pubDate').text
            if not (link and title and text and time):  # not None
                continue
            formats = ['%a, %d %b %Y %H:%M:%S %z',
                       '%a, %d %b %Y %H:%M:%S %Z',
                       '%a %d %b %Y %H:%M:%S %Z']
            success = False
            for timeFormat in formats:
                try:
                    time = datetime.strptime(time, timeFormat)
                    time = time + timedelta(hours=8)
                    time = time.strftime('%Y-%m-%d %H:%M:%S')
                    success = True
                    break
                except ValueError as e:
                    pass
            if not success:
                logging.error('[%s] timeError: %s', self.name, time)
                continue

            item['link'] = link
            item['title'] = title
            item['text'] = text
            item['time'] = time
            yield item
