<?php

namespace Addify\RestrictOrderByCustomer\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Addify\RestrictOrderByCustomer\Model\RestrictOrderByCustomer;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Addify\RestrictOrderByCustomer\Helper\HelperData;
use Addify\RestrictOrderByCustomer\Model\ResourceModel\RestrictOrderByCustomer\CollectionFactory as RestrictFactory;
 

class Save extends \Magento\Backend\App\Action
{
    
    const ADMIN_RESOURCE = 'Addify_RestrictOrderByCustomer::managerestrictorderbycustomer';
    protected $dataProcessor;    
    protected $dataPersistor;
    protected $model;
    protected $helperData;
    protected $_storeManager;
    protected $restrictFactory;

    public function __construct(
        Action\Context $context,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        PostDataProcessor $dataProcessor,
        RestrictOrderByCustomer $model,
        RestrictFactory $restrictFactory,
        DataPersistorInterface $dataPersistor
    )
    {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->model = $model;
        $this->helperData = $helperData;
        $this->_storeManager = $storeManager;
        $this->restrictFactory = $restrictFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            $data = $this->dataProcessor->filter($data);
            $id = $data['restrict_id'];
            $data['store'] = implode(',', $data['store']);
            $productUsing = $data['product_type'];
            $relatedProducts = '';
            $data['customer_group']=implode(',', $data['customer_group']);



            if(empty($id))
            {
                unset($data['restrict_id']);


            }

            $this->model->setData($data);
            if(isset($data['tab_related_customer']) )
            {
                $customerArr = $data['tab_related_customer'];
                $customer = $this->helperData->getCustomers($customerArr);

                $this->model->setData('customer_ids',$customer);
            }


            if($productUsing == '2')
            {
                if(isset($data['tab_related_products']) )
                {
                    $relatedProductsArr = $data['tab_related_products'];
                    $relatedProducts = $this->helperData->getRelatedProducts($relatedProductsArr);
                    $this->model->setData('product_ids',$relatedProducts);
                }
            }
            else
            {
               $this->model->setData('product_ids',$relatedProducts);
            }


            $this->_eventManager->dispatch(
                'restrictorderbycustomer_prepare_save',
                ['restrictorderbycustomer' => $this->model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->model->getId(), '_current' => true]);
            }

            
            try {

                $this->model->save();

                $this->messageManager->addSuccess(__('You saved the Record Successfully.'));
                $this->dataPersistor->clear('restrictorderbycustomer');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $this->model->getId(),
                         '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __($e->getMessage()));
            }

            $this->dataPersistor->set('restrictorderbycustomer', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}