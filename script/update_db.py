#-*- encoding:utf-8 -*-
import time
import json
import requests
import traceback
import ConfigParser
import mysql.connector
from tqdm import tqdm

cf = ConfigParser.ConfigParser()
cf.read("clue.conf")

MYSQL_USER = cf.get("mysql", "mysql_user") 
MYSQL_PASSWD = cf.get("mysql", "mysql_passwd")

NewClueES = "http://10.206.6.48:8200/news_clue/news_clue/"
TrashClueES = "http://10.194.165.27:8200/news_clue_trash/_search?size=%s&fields=_id,identical_event_id,title,content,type,url,create_time_format"

class UpdateDB():
    def __init__(self):
        """连接DB, 初始化数据大小为1"""
        self.conn = mysql.connector.connect(user=MYSQL_USER, password=MYSQL_PASSWD, database='new_clue', use_unicode=True)
        self.cursor = self.conn.cursor()
        self.data_size='1'
    
    def init_size(self):
        """
            获取ES里面数据的总数, 保证拉全信息
        """
        resp = requests.get(TrashClueES%self.data_size)
        mjson = json.loads(resp.content)
        self.data_size = mjson['hits']['total']
        return self.data_size
    
    def fetch_del_es(self):
        """
            提取被删除事件ES中的 title, content, id, url等字段

            返回一个包含所有关联dict的list
        """
        resp = requests.get(TrashClueES%self.data_size)
        mjson = json.loads(resp.content)

        hits = mjson['hits']['hits']
        for hit in tqdm(hits):
            try:
                hit_data = {}
                items = hit['fields']   # 获取当前hit的所有字段

                hit_data['rel_id'] = items['identical_event_id'][0]    # 通过rel_id来查找保留事件
                # print hit_data['rel_id']
                keep_data = self.fetch_keep_es(hit_data['rel_id'])

                hit_data.update(keep_data)  # 扩充当前hit_data

                hit_data['del_id'] = items['_id']
                hit_data['del_title'] = items['title'][0].replace("'","''")
                hit_data['del_type'] = items['type'][0]
                hit_data['del_content'] = items['content'][0].replace("'","''")
                hit_data['del_url'] = items['url'][0].replace("'","''")
                hit_data['update_time'] = items['create_time_format'][0].split(' ')[0]

                self.insert_mysql(hit_data)
            except:
                traceback.print_exc()
                continue




    def fetch_keep_es(self, rel_id):
        """
            提取 被保留事件ES中的title, content, type, url
            返回 当前id被保留事件关键字段的dict {title, content, type, url}
        """
        res = {}
        resp = requests.get(NewClueES+rel_id)
        myjson = json.loads(resp.content)
        items = myjson['_source']

        # 获取保留的所有字段
        res['rel_content'] = items['content'].replace("'","''")
        res['rel_type'] = items['type']
        res['rel_title'] = items['title'].replace("'","''")
        res['rel_url'] = items['url'].replace("'","''")

        return res

    def insert_mysql(self, item_dict):
        """
            接收一个dict, 把dict插入 compare_rel 表中
        """
        sql = "INSERT INTO `compare_rel` (`del_id`, `del_title`, `del_content`, `del_type`, `del_url`, `rel_id`, `rel_title`, `rel_content`, `rel_type`, `rel_url`, `update_time`) VALUES"\
              "('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');"

        try:
            # 构建sql语句的datalist
            sql = sql % (item_dict['del_id'],item_dict['del_title'],item_dict['del_content'],item_dict['del_type'],item_dict['del_url'],item_dict['rel_id'],item_dict['rel_title'],item_dict['rel_content'],item_dict['rel_type'],item_dict['rel_url'],item_dict['update_time'])
            
            self.cursor.execute(sql)
            self.conn.commit()
        except:
            print sql
    def close_conn(self):
        self.cursor.close()
        self.conn.close()

if __name__ == "__main__":
    updatedb = UpdateDB()
    size = updatedb.init_size()
    print "[被删除事件总数]: %s" %size
    updatedb.fetch_del_es()
    updatedb.close_conn()