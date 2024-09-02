<?php
namespace Plazathemes\Template\Helper;

use DOMDocument;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function getContentFromXmlFile($xmlFile = null, $node = null)
    {
        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $blockArrays = [];
        $blocks = $dom->getElementsByTagName($node);

        foreach ($blocks as $name) {
            $blockArray = [];
            if ($name->childNodes->length) {
                foreach ($name->childNodes as $i) {
                    $blockArray[$i->nodeName] = $i->nodeValue;
                }
            }
            $blockArrays[] = $blockArray;
        }

        return $blockArrays;
    }

    public function getConfigData($demo_temp = null)
    {
        $xmlPath = __DIR__ . '/Xml/data_config_demo' . $demo_temp . '.xml';
        $configData = $this->getContentFromXmlFile($xmlPath, 'default');
        return $configData ?: [];
    }

    public function getStaticBlockData()
    {
        $xmlPath = __DIR__ . '/Xml/data_static_blocks.xml';
        $staticBlockData = $this->getContentFromXmlFile($xmlPath, 'block');
        return $staticBlockData ?: [];
    }

    public function getCmsPageData()
    {
        $xmlPath = __DIR__ . '/Xml/data_resources.xml';
        $cmsPageData = $this->getContentFromXmlFile($xmlPath, 'resource');
        return $cmsPageData ?: [];
    }

    public function getBannerData()
    {
        $xmlPath = __DIR__ . '/Xml/banner.xml';
        $bannerData = $this->getContentFromXmlFile($xmlPath, 'record');
        return $bannerData ?: [];
    }

    public function getBrandSliderData()
    {
        $xmlPath = __DIR__ . '/Xml/brandslider.xml';
        $brandSliderData = $this->getContentFromXmlFile($xmlPath, 'record');
        return $brandSliderData ?: [];
    }

    public function haveBlockBefore($blockModel, $identifier = null)
    {
        $exist = $blockModel->getCollection()
            ->addFieldToFilter('identifier', ['eq' => $identifier])
            ->load();
        return count($exist) > 0;
    }

    public function haveBannerBefore($blockModel, $identifier = null)
    {
        $exist = $blockModel->getCollection()
            ->addFieldToFilter('banner_id', ['eq' => $identifier])
            ->load();
        return count($exist) > 0;
    }

    public function haveBrandBefore($blockModel, $identifier = null)
    {
        $exist = $blockModel->getCollection()
            ->addFieldToFilter('brandslider_id', ['eq' => $identifier])
            ->load();
        return count($exist) > 0;
    }

    public function haveBlockPageBefore($cmsModel, $identifier = null)
    {
        $exist = $cmsModel->getCollection()
            ->addFieldToFilter('identifier', ['eq' => $identifier])
            ->load();
        return count($exist) > 0;
    }

    public function getNodeDataFromBlock($node = 'identifier', $blocks = [])
    {
        $array_identifier = [];
        foreach ($blocks as $block) {
            $identifier = $block[$node];
            $array_identifier[] = $identifier;
        }
        return $array_identifier ?: [];
    }

    public function getNodeDataFromStaticBlock()
    {
        return $this->getNodeDataFromBlock('identifier', $this->getStaticBlockData()) ?: [];
    }

    public function getNodeDataFromCmsPageBlock()
    {
        return $this->getNodeDataFromBlock('identifier', $this->getCmsPageData()) ?: [];
    }

    public function getOldConfigData()
    {
        return [
            ['default', 'home'],
        ];
    }

    public function getAllStore()
    {
        $storeIds = [0];
        $stores = $this->_storeManager->getStores();
        foreach ($stores as $_store) {
            $storeIds[] = $_store->getId();
        }
        return $storeIds;
    }
}

