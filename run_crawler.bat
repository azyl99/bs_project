@echo off
cd crawler
:loop
	@echo [%date% %time%] crawling...
	scrapy crawl sina
	scrapy crawl baidu
	@echo [%date% %time%] waiting for next crawl...
	choice /t 300 /d n>null
goto loop