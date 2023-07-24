<?php declare(strict_types = 1);
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailedException;

class Data extends AbstractHelper
{
    /** Regx */
    const REGX_CWEBP = '/^[a-zA-Z0-9-_\s]+$/mi';
    const REGX_IMAGEMAGICK = '/^[a-zA-Z0-9-_\s,=:]+$/mi';
    const REGX_CWEBP_PATH = '/(^[a-zA-Z0-9\/_\-\.]+cwebp$|^cwebp$)/mi';
    const REGX_IMAGEMAGICK_PATH = '/(^[a-zA-Z0-9\/_\-\.]+convert$|^convert$)/mi';

    /**
     * StoreManager Interface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Driver file
     *
     * @var DriverFile
     */
    protected $driverFile;

    /**
     * @var string|null
     */
    protected $newFile;

    /**
     * IO file
     *
     * @var Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * @var null
     */
    protected $imageQuality;

    /**
     * Module file helper
     *
     * @var File
     */
    protected $fileHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ObjectManager $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param DriverFile $driverFile
     * @param Filesystem\Io\File $ioFile
     * @param File $fileHelper
     */
    public function __construct(
        Context $context,
        ObjectManager $objectManager,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        DriverFile $driverFile,
        Filesystem\Io\File $ioFile,
        File $fileHelper
    ) {
        $this->ioFile = $ioFile;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->driverFile = $driverFile;
        $this->fileHelper = $fileHelper;
        parent::__construct($context);
    }

    /**
     * Convert Image to Webp Image
     *
     * @param string $imageUrl
     *
     * @return string
     */
    public function convert($imageUrl)
    {
        $webpUrl = $this->getWebpNameFromImage($imageUrl);
        $webpPath = $this->getImagePathFromUrl($webpUrl);
        $this->newFile = $webpPath;
        $folder = $this->driverFile->getParentDirectory($webpPath);
        $this->createFolderIfNotExist($folder);
        $imagePath = $this->getImagePathFromUrl($imageUrl);
        if (empty($imagePath)) {
            return $imageUrl;
        }

        if (!$this->isFileExists($imagePath)) {
            return $imageUrl;
        }

        if (!$this->isEnabled()) {
            return $imageUrl;
        }

        if ($this->isFileExists($webpPath)) {
            // check if modified date of converted image is older than original image.
            // then delete the old converted image.
            $checkIsOldConvertedImage = $this->checkIsOldConvertedImage($imagePath, $webpPath);

            if (!$checkIsOldConvertedImage) {
                return $webpUrl;
            }
        }

        // Detect alpha-transparency in PNG-images and skip it
        if ($this->hasCheckTransparency() && $this->hasAlphaTransparency($imagePath)) {
            return $imageUrl;
        }
        switch ($this->scopeConfig->getValue(
            'webp/advance_mode/convert_tool',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            case 'cwebp':
                $this->newFile = $this->convertToWebpViaCwebp($imagePath, $webpPath);
                break;
            case 'convert':
                $this->newFile = $this->convertToWebpViaImageMagick($imagePath, $webpPath);
                break;
            case 'gd':
                $this->newFile = $this->convertToWebpViaGd($imagePath, $webpPath);
                break;
        }

        $webpUrl = $this->getImageUrlFromPath($this->newFile);
        return $webpUrl;
    }

    /**
     * Check Is Old Converted Image
     *
     * @param $imagePath
     * @param $webpPath
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkIsOldConvertedImage($imagePath, $webpPath)
    {
        // Get converted file modified date
        $convertedFileModifiedDate = $this->driverFile->stat($webpPath)['mtime'];
        
        // Get original file modified date
        $originfileModifiedDate = $this->driverFile->stat($imagePath)['mtime'];

        // check if modified date of converted image is older than original image.
        if ($originfileModifiedDate && $convertedFileModifiedDate && $originfileModifiedDate > $convertedFileModifiedDate) {
            // remove old converted file.
            $this->driverFile->deleteFile($webpPath);
            return true;
        }

        return false;
    }

    /**
     * Method to convert an image to WebP using the GD method
     *
     * @param string $imagePath
     * @param string $webpPath
     *
     * @return bool
     */
    public function convertToWebpViaGd($imagePath, $webpPath)
    {
        if ($this->hasGdSupport() == false) {
            return $imagePath;
        }
        $imageData = $this->fileGetContent($imagePath);

        try {
            $image = imagecreatefromstring($imageData);
            imagepalettetotruecolor($image);
        } catch (\Exception $ex) {
            return false;
        }

        imagewebp($image, $webpPath, $this->imageQuality());

        return $webpPath;
    }

    /**
     * Method to convert an image to WebP using the Imagemagick command
     *
     * @param string $imagePath
     * @param string $webpPath
     *
     * @return string
     */
    public function convertToWebpViaImageMagick($imagePath, $webpPath)
    {
        if ($this->isLoadedImageMagick()) {
            $customCommand = $this->scopeConfig->getValue(
                'webp/advance_mode/imagemagick_command',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $pathCommand = $this->scopeConfig->getValue(
                'webp/advance_mode/path_to_imagemagick',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $cmd = $pathCommand != null ? $pathCommand : 'convert';
            try {
                if ($customCommand != null) {
                    if (!preg_match(\Jajuma\WebpImages\Helper\Data::REGX_IMAGEMAGICK, $customCommand)) {
                        $this->error = __('Invalid ImageMagick Custom Command. Custom Command must only include
                     underscore (_), minus (-), space ( ), comma (,), colon (:),
                      equals sign (=) and alphanumeric characters.');
                        return false;
                    } else {
                        $process = SymfonyProcess::fromShellCommandline(
                            $this->escapeshellarg($cmd) . ' '
                            . $imagePath . ' '
                            . $customCommand . ' '
                            . $webpPath
                        );
                    }
                } else {
                    $process = SymfonyProcess::fromShellCommandline(
                        $this->escapeShellArg($cmd) . ' '
                        . $imagePath
                        . ' -quality '
                        . $this->imageQuality()
                        . ' -define webp:lossless=false,method=6,segments=4,sns-strength=80,auto-filter=true,'
                        . ' filter-sharpness=0,filter-strength=25,filter-type=1,'
                        . ' alpha-compression=1,alpha-filtering=fast,alpha-quality=100 '
                        . $webpPath
                    );
                }
                $process->mustRun();
            } catch (\Throwable $exception) {
                return $imagePath;
            }

            if ($this->isFileExists($webpPath)) {
                return $webpPath;
            }
        }
        return $imagePath;
    }

    /**
     * Method to convert an image to WebP using the Cwebp command
     *
     * @param string $imagePath
     * @param string $webpPath
     *
     * @return string
     */
    public function convertToWebpViaCwebp($imagePath, $webpPath)
    {
        $customCommand = $this->scopeConfig->getValue(
            'webp/advance_mode/cwebp_command',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pathCommand = $this->scopeConfig->getValue(
            'webp/advance_mode/path_to_cwebp',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $cmd = $pathCommand != null ? $pathCommand : 'cwebp';
        try {
            if ($customCommand != null) {
                if (!preg_match(\Jajuma\WebpImages\Helper\Data::REGX_CWEBP, $customCommand)) {
                    $this->error = __('Invalid Cwepb Custom Command. Custom Command must only include
                    underscore (_), minus (-), space ( ) and alphanumeric characters.');
                    return false;
                } else {
                    $process = SymfonyProcess::fromShellCommandline(
                        $this->escapeshellarg($cmd) . ' '
                        . $imagePath . ' '
                        . $customCommand . ' '
                        . $webpPath
                    );
                }
            } else {
                $process = SymfonyProcess::fromShellCommandline(
                    $this->escapeShellArg($cmd) . ' '
                    . $imagePath . ' -q '
                    . $this->imageQuality()
                    . ' -alpha_q 100 -z 9 -m 6 -segments 4 -sns 80 -f 25 -sharpness 0 -strong -pass 10'
                    . ' -mt -alpha_method 1 -alpha_filter fast -o '
                    . $webpPath
                );
            }
            $process->mustRun();
        } catch (\Throwable $exception) {
            return $imagePath;
        }

        if ($this->isFileExists($webpPath)) {
            return $webpPath;
        }

        return $imagePath;
    }

    /**
     * Is Enabled
     *
     * @param int|null $store
     *
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            'webp/setting/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Image Quality
     *
     * @param int|null $store
     *
     * @return int
     */
    public function imageQuality($store = null)
    {
        if (!$this->imageQuality) {
            $this->imageQuality = $this->scopeConfig->getValue(
                'webp/advance_mode/quality',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        return (int)$this->imageQuality;
    }

    /**
     * Has Gd Support
     *
     * @return bool
     */
    public function hasGdSupport()
    {
        if (!function_exists('imagewebp')) {
            return false;
        }

        return true;
    }

    /**
     * Is Loaded Image Magick
     *
     * @return bool
     */
    public function isLoadedImageMagick()
    {
        if (extension_loaded('imagick')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the WebP path equivalent of an image path
     *
     * @param mixed $image
     *
     * @return mixed
     */
    public function getWebpNameFromImage($image)
    {
        $image = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $image);
        $image = str_replace('media/', 'media/webp_image/', $image);
        return $image;
    }

    /**
     * Get System Paths
     *
     * @return array
     */
    public function getSystemPaths()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $systemPaths = [
            'pub' => [
                'url' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),
                'urlWithoutBaseUrl' => str_replace($baseUrl, '', $mediaUrl),
                'path' => $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
            ]
        ];
        return $systemPaths;
    }

    /**
     * Get Image Path From Url
     *
     * @param string $imageUrl
     *
     * @return mixed
     */
    public function getImagePathFromUrl($imageUrl)
    {
        $systemPaths = $this->getSystemPaths();

        foreach ($systemPaths as $systemPath) {
            if (strstr($imageUrl, $systemPath['url'])) {
                // if url have base url. ex: https://example.com/pub/media/..
                return str_replace($systemPath['url'], $systemPath['path'], $imageUrl);
            } elseif (strstr($imageUrl, $systemPath['urlWithoutBaseUrl'])) {
                // if url don't have base url. ex: /pub/media/..
                if (strpos($imageUrl, '/media/') !== false) {
                    $imageUrl = str_replace('/media/', 'media/', $imageUrl);
                }
                // replace pub/media with system path
                return str_replace($systemPath['urlWithoutBaseUrl'], $systemPath['path'], $imageUrl);
            }
        }
        return false;
    }

    /**
     * Get Image Url From Path
     *
     * @param string $imagePath
     *
     * @return mixed
     */
    public function getImageUrlFromPath($imagePath)
    {
        $systemPaths = $this->getSystemPaths();
        if (!preg_match('/^http/', $imagePath)) {
            foreach ($systemPaths as $systemPath) {
                if (strstr($imagePath, $systemPath['path'])) {
                    return str_replace($systemPath['path'], $systemPath['url'], $imagePath);
                }
            }
        }
        return false;
    }

    /**
     * Has Check Transparency
     *
     * @param int|null $store
     *
     * @return mixed
     */
    public function hasCheckTransparency($store = null)
    {
        return $this->scopeConfig->getValue(
            'webp/setting/check_transparency',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Detect whether an image has PNG alpha transparency
     *
     * @param mixed $image
     *
     * @return bool
     */
    public function hasAlphaTransparency($image)
    {
        if (empty($image)) {
            return false;
        }

        if ($this->isFileExists($image) == false) {
            return false;
        }

        if (preg_match('/\.(jpg|jpeg)$/', $image)) {
            return false;
        }

        $fileIo = $this->fileHelper;
        $fileIo->setCwd($this->driverFile->getParentDirectory($image));
        $fileIo->setIwd($this->driverFile->getParentDirectory($image));

        $imageContents = $fileIo->read($image);
        $colorType = ord(substr($imageContents, 25, 1));

        if ($colorType == 6 || $colorType == 4) {
            return true;
        } elseif (stripos($imageContents, 'PLTE') !== false && stripos($imageContents, 'tRNS') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Create Folder If Not Exist
     *
     * @param string $path
     */
    public function createFolderIfNotExist($path)
    {
        if (!$this->driverFile->isDirectory($path)) {
            $ioAdapter = $this->ioFile;
            $ioAdapter->mkdir($path, 0775);
        }
    }

    /**
     * Delete file
     *
     * @param string $filePath
     */
    public function deleteFile($filePath)
    {
        if ($this->isFileExists($filePath)) {
            $ioAdapter = $this->ioFile;
            $ioAdapter->rm($filePath);
        }
    }

    /**
     * Remove Folder
     *
     * @param string $folderPath
     *
     * @return bool|string
     */
    public function removeFolder($folderPath)
    {
        if ($this->driverFile->isDirectory($folderPath)) {
            if ($this->driverFile->isWritable($folderPath)) {
                $ioAdapter = $this->ioFile;
                $ioAdapter->rmdir($folderPath, true);
            } else {
                return false;
            }
        } else {
            return 'nowebpFolder';
        }
    }

    /**
     * Clear Test Webp Folder
     *
     * @return mixed
     */
    public function clearTestWebpFolder()
    {
        $webpFolder = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'webp_image/test';
        return $this->removeFolder($webpFolder) ;
    }

    /**
     * Clear webp
     *
     * @return mixed
     */
    public function clearWebp()
    {
        $webpFolder = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'webp_image';
        return $this->removeFolder($webpFolder) ;
    }

    /**
     * Get Exclude Image Attribute
     *
     * @param int|null $store
     *
     * @return mixed
     */
    public function getExcludeImageAttribute($store = null)
    {
        return $this->scopeConfig->getValue(
            'webp/professional_mode/exclude_img',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get custom src tag
     *
     * @param int|null $store
     *
     * @return mixed
     */
    public function getCustomSrcTag($store = null)
    {
        return $this->scopeConfig->getValue(
            'webp/professional_mode/src_tag',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Custom Src Set Tag
     *
     * @param int|null $store
     *
     * @return mixed
     */
    public function getCustomSrcSetTag($store = null)
    {
        return $this->scopeConfig->getValue(
            'webp/professional_mode/srcset_tag',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is Native Lazy Loading Enabled
     *
     * @param int|null $store
     *
     * @return mixed
     */
    public function isNativeLazyLoadingEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            'webp/native_lazy/enable_native_lazy',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Exclude Native Lazy load Image Attribute
     *
     * @param int|null $store
     *
     * @return mixed
     */
    public function getExcludeNativeLazyloadImageAttribute($store = null)
    {
        return $this->scopeConfig->getValue(
            'webp/native_lazy/exclude_native_lazy',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Exclude Native Lazy load Image Attributes
     *
     * @return string
     */
    public function getExcludeNativeLazyloadImageAttributes()
    {
        $excludeImageAttributes = $this->getExcludeNativeLazyloadImageAttribute();
        if ($excludeImageAttributes) {
            // Make sure unescaped slashes in blacklist get escaped.
            $excludeImageAttributes = preg_replace('/([^\\\\]\\K\\/|^\\/)/', '\\/', $excludeImageAttributes);
            $excludeImageAttributes = explode(',', $excludeImageAttributes);
            $excludeImageAttributes = array_map('trim', $excludeImageAttributes);
            $excludeImageAttributes = implode(".*|.*", $excludeImageAttributes);
            $excludeImageAttributes = '/(.*data-nowebp=\"true\".*|.*' . $excludeImageAttributes . '.*)/mi';
        } else {
            $excludeImageAttributes = '/(.*data-nowebp=\"true\".*)/mi';
        }

        return $excludeImageAttributes;
    }

    /**
     * Is File Exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function isFileExists($path)
    {
        return $this->ioFile->fileExists($path);
    }

    /**
     * File Get Content
     *
     * @param string $path
     *
     * @return false|string
     */
    public function fileGetContent($path)
    {
        return $this->driverFile->fileGetContents($path);
    }

    /**
     * Escape Shell Arg
     *
     * @param string $str
     *
     * @return string
     */
    public function escapeShellArg($str)
    {
        return escapeshellarg($str);
    }
}
