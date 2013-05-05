<?php

class StorageController extends Zend_Controller_Action
{
    const IMAGE_PATH    = '/tmp/image_storage';
    const PARAM_KEY     = 'key';
    private $adapter_   = null;
    private $logger_    = null;

    public function init()
    {
        /* Initialize action controller here */
        $this->adapter_ = Zend_Cloud_StorageService_Factory::getAdapter(array(
                Zend_Cloud_StorageService_Factory::STORAGE_ADAPTER_KEY => 'Zend_Cloud_StorageService_Adapter_FileSystem',
                Zend_Cloud_StorageService_Adapter_FileSystem::LOCAL_DIRECTORY => self::IMAGE_PATH
                ));
        $this->logger_ = Zend_Registry::get('g_logger');
    }

    public function indexAction()
    {
        // action body
    }

    public function storeItemAction()
    {
        $this->logger_->info('storeItemAction');
        $request = $this->getRequest();
        $contentKey = $request->getParam(self::PARAM_KEY);
        $contentData = $request->getRawBody();
        $metadata = $request->getHeader('kx-storage');
        $metadata = json_decode($metadata);

        $viewData = array();
        try {
            $this->adapter_->storeItem($contentKey, $contentData, $metadata);
            $viewData['ret']        = 0;
            $viewData['message']    = 'succeed';
        } catch (Exception $e) {
            $viewData['ret']        = -1;
            $viewData['message']    = "store error {$e->getMessage()}";
        }

        $this->view->viewData = $viewData;
        $this->logger_->info($viewData['message']);
    }

    public function fetchItemAction()
    {
        $this->logger_->info('fetchItemAction');
        $request = $this->getRequest();
        $contentKey = $request->getParam(self::PARAM_KEY);

        $viewData   = array();
        try {
            $contentData = $this->adapter_->fetchItem($contentKey);
            if ($contentData === false) {
                $viewData['ret']        = -2;
                $viewData['message']    = 'adapter fetch empty';
                $viewData['data']       = null;
            } else {
                $viewData['ret']        = 0;
                $viewData['message']    = 'succeed ';
                $viewData['data']       = $contentData;
            }
        } catch (Exception $e) {
            $viewData['ret']        = -1;
            $viewData['message']    = "fetchItem error {$e->getMessage()}";
            $viewData['data']       = null;
        }

        $this->view->viewData = $viewData;
        $this->logger_->info($viewData['message']);
    }

    public function deleteItemAction()
    {
        $this->logger_->info('fetchItemAction');
        $request = $this->getRequest();
        $contentKey = $request->getParam(self::PARAM_KEY);

        $viewData   = array();
        try {
            $this->adapter_->fetchItem($contentKey);
            $viewData['ret']        = 0;
            $viewData['message']    = 'succeed ';
        } catch (Exception $e) {
            $viewData['ret']        = -1;
            $viewData['message']    = "deleteItem error {$e->getMessage()}";
        }

        $this->view->viewData = $viewData;
        $this->logger_->info($viewData['message']);
    }

    public function fetchMetadataAction()
    {
        // action body
    }

    public function storeMetadataAction()
    {
        // action body
    }

    public function deleteMetadataAction()
    {
        // action body
    }
}
