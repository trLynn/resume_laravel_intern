<?php

namespace App\Traits;

/**
 * Download Header Trait
 *
 * @author  PhyoNaing Htun
 * @create  2022/06/21
 */
trait DownloadHeaderTrait
{

    /**
     * Return header array
     *
     * @author  PhyoNaing Htun
     * @create  2022/06/21
     * @param   file name
     * @return  array
     */
    public function getDownloadHeader($fileName)
    {
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment;filename=' . $fileName,
            'Access-Control-Expose-Headers' => 'Content-Disposition,X-Suggested-Filename'
        ];
        return $headers;
    }
}
