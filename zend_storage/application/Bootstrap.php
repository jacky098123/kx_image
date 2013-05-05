<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initLog()
    {   
        $pid = getmypid();
        $format = '[%timestamp%] [%priorityName%] ' . $pid . ' : %message%' .  PHP_EOL ;
        $formater = new Zend_Log_Formatter_Simple($format);

        $logfile = '/tmp/zend_storage.log.' . date('Y-m-d');
        $writer = new Zend_Log_Writer_Stream($logfile);
        $writer->setFormatter($formater);

        $logger = new Zend_Log($writer);

        Zend_Registry::set('g_logger', $logger);
        $logger->info('== bootstrap ==');
    }
}
