<?php

namespace Magepow\Preview\Block\Adminhtml\Page\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Block\Adminhtml\Page\Edit\GenericButton;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder as ActionUrlBuilder;

/**
 * Class PreviewButton
 */
class PreviewButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ActionUrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @param Context $context
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        Context $context,
        PageRepositoryInterface $pageRepository,
        ActionUrlBuilder $actionUrlBuilder
    ) {
        parent::__construct($context, $pageRepository);
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->storeManager = $this->context->getStoreManager();
    }

    private function disablePreview()
    {
        $page = $this->getPage();
        return $page ? !$this->getPage()->getIsActive() : true;
    }

    /**
     * Clear Cache button
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->disablePreview()) {
            return [];
        }
        $page = $this->getPage();
        $storeIds = $page->getStoreId();
        $page->setData('_first_store_id', array_shift($storeIds));
        $storeId    = (int) $this->context->getRequest()->getParam('store');
        $store      = $this->storeManager->getStore($storeId);
        $previewUrl = $this->actionUrlBuilder->getUrl(
            $page->getIdentifier(),
            $page->getData('_first_store_id'),
            $store->getCode()
        );

        return [
            'label' => __('Preview'),
            'class' => 'action-secondary preview',
            // 'on_click' => "confirmSetLocation('Are you sure', '{$previewUrl}')",
            'on_click' => 'window.open( \'' . $previewUrl . '\')',
            'sort_order' => 10
        ];
    }

    public function getPage()
    {
        try {
            return $this->pageRepository->getById(
                $this->context->getRequest()->getParam('page_id')
            );
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }
}