<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class RPCGActions extends Column
{
    /** Url path */
    const ITEM_URL_PATH_EDIT = 'rpcg/rpcg/edit';
    const ITEM_URL_PATH_DELETE = 'rpcg/rpcg/delete';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = array(),
        array $data = array(),
        $editUrl = self::ITEM_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['edit'] = array(
                        'href' => $this->urlBuilder->getUrl($this->editUrl, array('id' => $item['id'])),
                        'label' => __('Edit')
                    );
                    $item[$name]['delete'] = array(
                        'href' => $this->urlBuilder->getUrl(self::ITEM_URL_PATH_DELETE, array('id' => $item['id'])),
                        'label' => __('Delete'),
                        'confirm' => array(
                            'title' => __('Delete "${ $.$data.attachment_name }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.name }"?')
                        )
                    );
                }
            }
        }

        return $dataSource;
    }
}
