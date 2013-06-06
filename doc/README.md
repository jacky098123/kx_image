
kxbt\_image
==========
image service

dependency
=================
1. zend framework

    add Zend framework path to include_path

2. redis

    ./Cache/Backend/Redis.php.

3. imagemagick

    sudo apt-get install php5-imagick


objective
==========
impliment a image server and a storage framework


feature
=============
1. use a adaptor as a storage. (then can use distribute FS, local filesystem)

2. image server read image from storage, do image business

3. use redis store thumbnail

4. write: sync and async

5. API version


design
=============

dataflow
-------------
![dataflow](/image/image_server_dataflow.jpeg)

**dataflow for read image:**

* request to image server
* read from redis. if succeed, return
* read meta info from database, read from storage; format data; save to redis; return

**dataflow for read image info:**

* request to image server
* read from redis, if succeed, return
* read from database for meta data; save to redis; return

**dataflow for write image:**

* request to image server
* save to storage
* save to database


image server
--------------
Image service contain write and read function:

Write service provide various method to store image.
* download service, accept URL and download into system
* upload service, accept user upload image info, and store into system

Read service provide high performance service.
* read original image, or get thumbnail, watermark etc.


storage
------------
Storage stores the image file. use the Zend framework storage adaptor can support S3, local fs etc.


database
----------
database provide image meta info.

    create table user (
    id int auto_increment,
    domain varchar(16) not null default '',
    authorization varchar(32) not null default '',
    appliciant  varchar(32) not null default '',
    business    varchar(32) not null default '',
    status      varchar(8) not null default 'created' comment 'created, open, closed',
    create_time timestamp default '0000-00-00 00:00:00',
    update_time timestamp,
    primary key(id),
    key idx_domain(domain, authorization)
    ) engine=InnoDB, charset='utf8';

    create table image_info (
    id bigint auto_increment,
    domain varchar(16) not null default '',
    dkey varchar(255) not null default '',
    storage_meta text not null default '' comment 'json data',
    image_meta text not null default '' comment 'json data',
    md5sum varchar(32) not null default '',
    width int,
    height int,
    size int,
    image_type varchar(8),
    create_time timestamp default '0000-00-00 00:00:00',
    update_time timestamp,
    primary key(id),
    unique key idx_domain(domain, dkey),
    key idx_md5(md5sum)
    ) engine=InnoDb, charset=utf8;


cache
---------
use redis as persistence database store thumbnail, original image and image metadata. FS only store original image.


asynchnorous download service
---------------------------------
batch purpose

    create table image_task (
    id bigint auto_increment,
    domain varchar(16) not null default '',
    dkey varchar(255) not null default '',
    image_url varchar(255) not null default '',
    page_url varchar(255) not null default '',
    metadata text default '',
    callback text default '',
    flag varchar(16) default 'created' comment 'created, failed, succeed, warning',
    flag_ext text,
    create_time timestamp default '0000-00-00 00:00:00',
    update_time timestamp,
    primary key (id),
    key idx_url(url),
    key idx_flag(flag)
    ) engine=InnoDB, charset='utf8';


API
-----------
In order to fulfill different scenary, we support three type method to get image: 

**Info interface**

read image metadata from database

**Thumbnail interface**

width, height, option and watermark is optional

    http://${internet_domain}/image/thumbnail?id=${image id}&width=${width}&height=${height}&optin=${option}&watermark=${watermark}
    http://${internet_domain}/image/thumbnail?sum=${md5sum}&width=${width}&height=${height}&optin=${option}&watermark=${watermark}

**Original interface**

this type only support get original image

    http://${internet_domain}/${domain}/${dkey}

**Store interface**

    http://${internet_domain}/image/store?domain=${domain}&dkey=${dkey}

Http header `kx_meta_storage`

    kx-meta-storage: {'biz': 'hotel', 'biz_il': '10000088', 'image_url': '', 'page_url': '', 'api_type': 'crawl'}


Authorization
---------------------
Authorization only passed by Http Header, some request need authorization, some doesn't

    kx-authorization: ${token}


RESTfull api
=================
please see ZEND documentation

