<?php

class ImageController extends Zend_Controller_Action
{
    const STORAGE_DOMAIN = 'storage.service.kuxun.cn';

    public function init()
    {
        /* Initialize action controller here */
        $this->logger = Zend_Registry::get('g_logger');
    }

    protected function getRequestImageInfo()
    {
    }

    protected function verifyAuthorization()
    {
        $headers = apache_request_headers();
        $authorizeHeader = 'kx-authorization';
        if (!isset($headers[$authorizeHeader]))
            return false;

        $user = new Kx_Model_User();
        $row = $user->fetchRow("authorization=?", $headers[$authorizeHeader]);
        if ($row == null)
            return false;

        if ($row->status != 'open') {
            return false;
        }

        return $row->toArray();
    }

    public function indexAction()
    {
        // action body
    }

    // out dated
    public function readAction()
    {
        // action body
    }

    public function uploadAction()
    {
        /*
        $authorization = $this->verifyAuthorization();
        if ($authorization == null) {
            $viewData = array();
            $viewData['ret']    = -100;
            $viewData['message']= "authorization failed";
            $this->view->viewData = $viewData;
            return;
        }*/

        $request    = $this->getRequest();
        $imageData  = $request->getRowBody();
        $domain     = $request->getParam('domain', '');
        $dkey       = $request->getParam('dkey', '');

        $viewData   = array();
        if (strlen($domain) == 0 || strlen($dkey) == 0) {
            $viewData['ret']        = -1;
            $viewData['message']    = "invalid param: {$domain}, {$dkey}";
            $this->view->viewData   = $viewData;
            return;
        }

        /*if ($authorization['domain'] != $domain) {
            $viewData['ret']    = -2;
            $viewData['message']= "domain does not match";
            $this->view->viewData   = $viewData;
            return;
        }*/

        // get info
        $imageDbInfo  = array();
        $im = new Imagic();
        try {
            $im->readImageBlob($imageData);
            $imageDbInfo['height']    = $im->getImageHeight();
            $imageDbInfo['width']     = $im->getImageWidth();
            $imageDbInfo['size']      = $im->getImageSize();
            $imageDbInfo['image_type']= $im->getImageType();
        } catch (exception $e) {
            $this->g_logger->error("read image error: {$e->getMessage()}");
            $viewData['ret']        = -2;
            $viewData['message']    = 'can not identify image file';
            return;
        }

        $kxMetaStorage  = $request->getHeader('kx-meta-storage', '');
        $imageDbInfo['meta_storage']    = $kxMetaStorage;

        // put to storage
        $md5    = md5($imageData);
        $client = new Zend_Http_Client("{self::STORAGE_DOMAIN}/storage/store-item?key={$md5}");
        $client->setRawData($imageData);
        $response = $client->request('POST');
        if (!$response->isSuccessful()) {
            $viewData['ret']    = -11;
            $viewData['message']= 'storage error';
            $this->view->viewData   = $viewData;
            return;
        }

        $responseJson = json_encode($response->getRawBody());
        if ($responseJson['ret'] == 0) {
            $viewData['ret']    = -12;
            $viewData['message']= "storage error" . $responseJson['message'];
            $this->view->viewData   = $viewData;
            return;
        }
        // finally store succeed.

        // write to database
        $dbImageInfo = Kx_Model_ImageInfo();
        $id     = $dbImageInfo->createImage($imageDbInfo);
        $row    = $dbImageInfo->find($id);

        if ($row == null) {
            $viewData['ret']    = -20;
            $viewData['message']= "retrieve from db error for id: {$id}";
            $this->view->viewData   = $viewData;
            return;
        } else {
            $viewData['ret']    = 0;
            $viewData['message']= "create succeed";
            $viewData['db_info']= $row->toArray();
            return;
        }
    }

    // same as uploadAction
    public function storeAction()
    {
        // action body
    }

    public function thumbnailAction()
    {
        // action body
    }

    public function infoAction()
    {
        $info = $this->getRequestImageInfo();
        if ($info == false)
            return;
        
        $viewData   = array();
        $dbImageInfo= new Kx_Model_ImageInfo();
        $row = $dbImageInfo->fetchRow('md5=?', $info['md5']);
        if ($row == null) {
            $viewData['ret']        = -1;
            $viewData['message']    = 'not in db';
            $this->view->viewData   = $viewData;
        } else {
            $viewData['ret']        = 0;
            $viewData['message']    = 'found';
            $viewData['db_info']    = $row->toArray();
            $this->view->viewData   = $viewData;
        }
    }
}
