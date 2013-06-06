<?php

class Kx_Model_ImageInfo extends Zend_Db_Table_Abstract
{
    protected $_name = 'image_info';

    public function createImage($data) {
        $row = $this->createRow();
        if (count($data) == 0)
            return false;

        foreach ($data as $k => $v) {
            $row->$k = $v;
        }
        $row->create_time =  date('Y-m-d H:i:s');
        $row->update_time = $row->create_time;
        $row->save();
        return $row->id;
    }
}
