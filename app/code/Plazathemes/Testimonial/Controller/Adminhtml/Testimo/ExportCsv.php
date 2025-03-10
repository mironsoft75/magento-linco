<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Testimonial\Controller\Adminhtml\Testimo;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Plazathemes\Testimonial\Controller\Adminhtml\Testimo {
	/**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
	 * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context, $coreRegistry);
		$this->_fileFactory = $fileFactory;
    }

	public function execute() {
		$fileName = 'testimonials.csv';
		$content = $this->_view->getLayout()->createBlock('Plazathemes\Testimonial\Block\Adminhtml\Testimo\Grid')->getCsv();
		return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
	}
}
