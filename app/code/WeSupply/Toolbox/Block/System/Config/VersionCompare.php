<?php
namespace WeSupply\Toolbox\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;
use WeSupply\Toolbox\Logger\Logger as Logger;

/**
 * Class SystemInformation
 * @package WeltPixel\Backend\Block\Adminhtml\System\Config
 */
class VersionCompare extends Field
{
    /**
     * Module current versions
     */
    public const MODULE_VERSIONS = 'https://www.weltpixel.com/weltpixel_extensions.json';

    /**
     * WeSupply new account url
     */
    public const WESUPPLY_CREATE_ACCOUNT = 'https://wesupplylabs.com/?utm_source=wesupply_extension&utm_medium=signup&utm_campaign=button';

    /**
     * Module download url
     */
    public const DOWNLOAD_URL = 'https://wesupplylabs.com/ws-toolbox/magento2/WeSupply_Toolbox.zip';

    /**
     * Module name
     */
    public const MODULE_NAME = 'WeSupply_Toolbox';

    /**
     * @var string
     */
    protected $_template = 'WeSupply_Toolbox::system/config/version_compare.phtml';

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * VersionCompare constructor.
     * @param Context $context
     * @param ReadFactory $readFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ReadFactory $readFactory,
        ComponentRegistrarInterface $componentRegistrar,
        Logger $logger
    )
    {
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @return bool|string
     * @throws FileSystemException
     */
    public function compareVersion()
    {
        if (!$this->getModuleLatestVersion()) {
            return false;
        }

        if (version_compare($this->getCurrentVersion(), $this->getModuleLatestVersion()) < 0) {
            return 'diff';
        }

        return 'same';
    }

    /**
     * @return string
     */
    public function getWeSupplyUrl()
    {
        return self::WESUPPLY_CREATE_ACCOUNT;
    }

    /**
     * @return string
     */
    public function getDownloadLink()
    {
        return self::DOWNLOAD_URL;
    }

    /**
     * @return bool|mixed
     */
    public function getCurrentVersion()
    {
        if ($version = $this->getCurrentComposerVersion()) {
            return $version;
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function getModuleLatestVersion()
    {
        $curl = curl_init(self::MODULE_VERSIONS);

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $version = false;
        $response = curl_exec($curl);
        if ($response !== false){
            $latestVersions = json_decode($response, true);
            if (!$this->isSetModuleVersion($latestVersions)) {
                // log error and exit
                $this->logger->error('Cannot get modules latest versions.');
                return false;
            }

            $version = $latestVersions['modules'][self::MODULE_NAME]['version'];
        }

        curl_close($curl);

        return $version;
    }

    /**
     * @return mixed
     */
    protected function getCurrentComposerVersion()
    {
        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                self::MODULE_NAME
            );

            $dirReader = $this->readFactory->create($path);
            $composerJsonData = $dirReader->readFile('composer.json');
            $data = json_decode($composerJsonData, true);

            return $data['version'] ?? false;

        } catch (FileSystemException $e) {
            $this->logger->error('Cannot get module current version. Error: ' . $e->getMessage());
        }
    }

    protected function isSetModuleVersion($latestVersions)
    {
        return isset($latestVersions['modules'][self::MODULE_NAME]['version']);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}