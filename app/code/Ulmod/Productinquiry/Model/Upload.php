<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model;

use Magento\Framework\File\Uploader as FileUploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFileFactory;

    /**
     * @param UploaderFactory $uploaderFileFactory
     */
    public function __construct(
        UploaderFactory $uploaderFileFactory
    ) {
        $this->uploaderFactory = $uploaderFileFactory;
    }
    
    /**
     * Process file uploader and get name
     *
     * @param string $input
     * @param string $destinationFolder
     * @param array $data
     * @return string
     */
    public function uploadFileAndGetName(
        $input,
        $destinationFolder,
        $data
    ) {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploaderFile = $this->uploaderFactory
                    ->create(['fileId' => $input]);
                
                $uploaderFile->setFilesDispersion(true);
                $uploaderFile->setAllowRenameFiles(true);
                $uploaderFile->setAllowCreateFolders(true);
                
                $result = $uploaderFile->save(
                    $destinationFolder
                );
                
                return $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != FileUploader::TMP_NAME_EMPTY) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        
        return '';
    }
}
