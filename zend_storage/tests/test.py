import os
import sys
import logging
import urllib

FILE_PATH = os.path.dirname(__file__)
sys.path.append('/home/yangrq/github/pycore')

from utils.btlog import btlog_init
from utils.common_handler import CommonHandler
from utils.http_client import HttpClient

class Tester(CommonHandler, HttpClient):
    def __init__(self):
        HttpClient.__init__(self)
        self.key = urllib.quote_plus('a/b/c/d/a.txt')
        pass

    def fetch(self):
        ret = self.DoGet('storage.service.kuxun.cn', 80, '/storage/fetch-item?key=%s' % self.key)
        print ret

    def store(self):
        data = self.LoadFile('test.py')
        ret = self.DoPost('storage.service.kuxun.cn', 80, '/storage/store-item?key=%s' % self.key, data)
        print ret

    def Run(self):
        self.store()

if __name__ == '__main__':
    btlog_init('a.log', console=True, logfile=False, level=logging.DEBUG)
    a = Tester()
    a.Run()
    a.fetch()
