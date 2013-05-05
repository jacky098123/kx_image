README
======

This directory should be used to place project specfic documentation including
but not limited to project notes, generated API/phpdoc documentation, or
manual files generated or hand written.  Ideally, this directory would remain
in your development environment only and should not be deployed with your
application to it's final production location.


Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
   DocumentRoot "/home/yangrq/github/kx_image/zend_storage/public"
   ServerName zend_storage.local

   # This should be omitted in the production environment
   SetEnv APPLICATION_ENV development

   <Directory "/home/yangrq/github/kx_image/zend_storage/public">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>

</VirtualHost>

Patch
==============
modify FileSystem.php

    public function storeItem($destinationPath, $data, $options = array())
    {
        $path = $this->_getFullPath($destinationPath);
        // yangrq begin
        $dirName = dirname($path);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        // yangrq end
        file_put_contents($path, $data);
        chmod($path, 0777);
    }

