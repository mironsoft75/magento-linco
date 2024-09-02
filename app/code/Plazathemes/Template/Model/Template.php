<?php
namespace Plazathemes\Template\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class Template
{
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_configFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_configFactory = $configFactory;
    }

    public function saveConfigDesgin($helper, $store = null, $website = null, $demo_temp = null)
    {
        // Default response
        $messages = new DataObject([
            'is_valid' => false,
            'request_success' => false,
            'request_message' => __('Error during Import'),
        ]);

        try {
            $configData = $helper->getConfigData($demo_temp);
            $scope = "default";
            $scope_id = 0;

            if ($store && $store > 0) {
                $scope = "stores";
                $scope_id = $store;
            } elseif ($website && $website > 0) {
                $scope = "websites";
                $scope_id = $website;
            }

            foreach ($configData[0] as $key => $config) {
                if (isset($config) && $config !== null) {
                    $this->_configFactory->saveConfig('web/default/' . $key, $config, $scope, $scope_id);
                }
            }

            $messages->setIsValid(true);
            $messages->setRequestSuccess(true);
            $messages->setRequestMessage(__('Success to Import'));
        } catch (\Exception $exception) {
            $messages->setIsValid(false);
            $messages->setRequestMessage($exception->getMessage());
        }

        return $messages;
    }

    public function saveStaticBlock($helper, $model, $store = null)
    {
        $staticData = $helper->getStaticBlockData();

        foreach ($staticData as $block) {
            $block['stores'] = $store;
            if (!$helper->haveBlockBefore($model, $block['identifier'])) {
                $model->setData($block)->save();
            } else {
                $model->load($block['identifier'])->setStores($store)->save();
            }
        }
    }

    public function saveCmsPage($helper, $model, $store = [0])
    {
        $cmsData = $helper->getCmsPageData();

        foreach ($cmsData as $block) {
            $block['stores'] = $store;
            if (!$helper->haveBlockPageBefore($model, $block['identifier'])) {
                $model->setData($block)->save();
            } else {
                $model->load($block['identifier'])->setStores($store)->save();
            }
        }
    }

    public function saveBanner($helper, $model, $store = [0])
    {
        $bannerData = $helper->getBannerData();

        foreach ($bannerData as $banner) {
            if ($banner) {
                $model->setData($banner)->save();
            }
        }
    }

    public function saveBrand($helper, $model, $store = [0])
    {
        $brandData = $helper->getBrandSliderData();

        foreach ($brandData as $brand) {
            if ($brand) {
                $model->setData($brand)->save();
            }
        }
    }
   public function deleteCmsPageBlock($cmsModel, $key = null, $stores = null)

    {
        $cmsModel->load($key);
        $storesOld = $cmsModel->getStoreId();
        $storeNew = [];

        if (count($storesOld)) {
            foreach ($storesOld as $storeId) {
                if (!in_array($storeId, $stores)) {
                    $storeNew[] = $storeId;
                }
            }
        }

        if (in_array(0, $stores)) {
            $cmsModel->delete();
        } else {
            $cmsModel->setStores($storeNew)->save();
        }
    }

    public function deleteStaticBlock($blockModel ,$key = null, $stores = null)
    {
        $blockModel->load($key);
        $storesOld = $blockModel->getStoreId();
        $storeNew = [];

        if (count($storesOld)) {
            foreach ($storesOld as $storeId) {
                if (!in_array($storeId, $stores)) {
                    $storeNew[] = $storeId;
                }
            }
        }

        if (in_array(0, $stores)) {
            $blockModel->delete();
        } else {
            $blockModel->setStores($storeNew)->save();
        }
    }

    public function deleteBanner($bannerModel, $stores = null)
    {
        $bannerCollection = $bannerModel->getCollection();

        if (count($bannerCollection) > 0) {
            foreach ($bannerCollection as $banner) {
                $banner->delete();
            }
        }
    }

    public function deleteBrand($brandModel, $stores = null)
    {
        $brandCollection = $brandModel->getCollection();

        if (count($brandCollection) > 0) {
            foreach ($brandCollection as $brand) {
                $brand->delete();
            }
        }
    }
}

