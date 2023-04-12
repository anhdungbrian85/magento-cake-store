<?php
namespace WeltPixel\GA4\Model\ServerSide;

class JsonBuilder extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /** @var string  */
    const CACHE_PATH = 'ga4';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    )
    {
        parent::__construct($context, $registry);
        $this->fileSystem = $fileSystem;
        $this->directoryList = $directoryList;
    }


    /**
     * @param $content
     * @return false|string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function saveToFile($content)
    {
        $cachePath = $this->fileSystem->getDirectoryWrite($this->directoryList::CACHE);
        $fileHash =  hash( 'sha1', $content);
        $filePath = self::CACHE_PATH . DIRECTORY_SEPARATOR. $fileHash;
        try {
            $cachePath->writeFile($filePath, $content);
        } catch (\Exception $exception) {
            return null;
        }
        return $fileHash;
    }

    /**
     * @param $fileHash
     * @return string|null
     */
    public function getContentFromFile($fileHash)
    {
        $cachePath = $this->fileSystem->getDirectoryRead($this->directoryList::CACHE);
        $filePath = self::CACHE_PATH . DIRECTORY_SEPARATOR. $fileHash;

        try {
            $content = $cachePath->readFile($filePath);
        } catch (\Exception $ex) {
            return '';
        }

        return $content;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function clearSavedHashes()
    {
        $cachePathDirectory = $this->fileSystem->getDirectoryWrite($this->directoryList::CACHE);
        try {
            $cachePathDirectory->delete(self::CACHE_PATH);
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }


}
