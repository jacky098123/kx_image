kxbt_image
==========
image service

dependency
=================
1. zend framework

    add Zend framework path to include_path

2. redis

    ./Cache/Backend/Redis.php.

3. imagemagick

    sudo apt-get install imagemagick


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
# request to image server
# read from redis. if succeed, return
# read meta info from database, read from storage; format data; save to redis; return

**dataflow for read image info:**
# request to image server
# read from redis, if succeed, return
# read from database for meta data; save to redis; return

**dataflow for write image:**
# request to image server
# save to storage
# save to database

image server
--------------
Image service contain write and read function:

Write service provide various method to store image.
# download service, accept URL and download into system
# upload service, accept user upload image info, and store into system

Read service provide high performance service.
# read original image, or get thumbnail, watermark etc.


storage
------------
Storage stores the image file. use the Zend framework storage adaptor can support S3, local fs etc.


database
----------
database provide image meta info.

    create table image_info (
    id bigint auto_increment,
    domain varchar(16) not null default '',
    dkey varchar(255) not null default '',
    user_metadata text not null default '' comment 'json data',
    image_metadata text not null default '' comment 'json data',
    md5sum varchar(32) not null default '',
    width int,
    height int,
    size int,
    create_time timestamp default '0000-00-00 00:00:00',
    update_time timestamp,
    primary key(id),
    key idx_md5(md5sum),
    key idx_domain(domain, dkey)
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
    url varchar(255) not null default '',
    page_url varchar(255) not null default '',
    metadata text default '',
    callback text default '',
    flag varchar(16) default 'created' comment 'created, failed, succeed, warning',
    flag_ext text default '',
    create_time timestamp default '0000-00-00 00:00:00',
    update_time timestamp,
    primary key (id),
    key idx_url(url),
    key idx_flag(flag)
    ) engine=InnoDB, charset='utf8';


API
-----------
in order to fulfill different scenary, we support three type method to get image: 

**image id or md5sum**

width, height, and watermark is optional

    http://${internet_domain}/image/read?id=${image id}&width=${width}&height=${height}&watermark=${watermark}
    http://${internet_domain}/image/read?sum=${md5sum}&width=${width}&height=${height}&watermark=${watermark}

**doman + dkey**

this type only support get original image

    http://${internet_domain}/${domain}/${dkey}


RESTfull api
=================
please see ZEND documentation

