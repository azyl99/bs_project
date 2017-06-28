import scrapy
import logging
from scrapy.http import Request
from ..items import BsItem
from lxml import etree
from datetime import datetime
from datetime import timedelta


types_table = {
    '汽车最新': '汽车',
    '房产最新': '房产',
    '国际最新': '国际',
    '互联网最新': '科技',
    '军事最新': '军事',
    '国内最新': '国内',
    '娱乐最新': '娱乐',
    '体育最新': '体育',
    '财经最新': '财经',
}
types_ignore = ['none',]


class BaiduSpider(scrapy.Spider):
    name = "baidu"
    start_urls = ['https://www.baidu.com/search/rss.html']

    def parse(self, response):

        types = response.xpath('//*[@id="feeds"]/div[2]/ul/li/span/text()').extract()
        links = response.xpath('//*[@id="feeds"]/div[2]/ul/li/input/@value').extract()

        items = []
        for ntype, link in zip(types, links):
            item = BsItem()
            mtype = types_table.get(ntype, 'none') # D.get(k[,d]) -> D[k] if k in D, else d.  d defaults to None.
            if mtype == 'none':
                logging.error('[%s] MyTypeError: %s', self.name, ntype)

            item['type'] = mtype
            item['subtype'] = ''
            item['link'] = link
            item['source'] = 'baidu'
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
            formats = ['%Y-%m-%dT%H:%M:%S.000Z']
                        # '%a, %d %b %Y %H:%M:%S %z',
                        #        '%a, %d %b %Y %H:%M:%S %Z',
                        # '%a %d %b %Y %H:%M:%S %Z']
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
