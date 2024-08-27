<?php
/**
 * @Author: nguyen
 * @Date:   2020-12-15 14:01:01
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2023-08-19 16:25:12
 * https://github.com/magento/magento2-samples/tree/master/sample-module-command/Console/Command
 */

namespace Magiccart\Alothemes\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

class Quickstart extends Command
{

    /**
     * Date argument
     */
    const DATE_ARGUMENT = 'date';

    /**
     * Allow option
     */
    const ALL_PRODUCT = 'all-product';

    private $todayEndOfDayDate;
    private $todayStartOfDayDate;
    private $expiryDate;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var ProductVisibility
     */
    protected $productVisibility;

    /**
     *
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     *
     * @var objectManager
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $name
     * @param \Magento\Framework\App\State $state
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Visibility $visibility
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        ProductCollectionFactory $productCollectionFactory,
        ProductVisibility $productVisibility,
        string $name = null,
        ?LoggerInterface $logger = null
    ) {
        $this->state = $state;
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->objectManager = ObjectManager::getInstance();
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
       
        parent::__construct($name);
    }

    protected function configure()
    {
        // command: bin/magento initQuickstart
        // command: bin/magento initQuickstart "next week"
        // command: bin/magento initQuickstart 10
        $this->setName('initQuickstart')
             ->setDescription('Config init quickstart')
            ->setDefinition([
                new InputArgument(
                    self::DATE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Date time'
                ),
                new InputOption(
                    self::ALL_PRODUCT,
                    '-a',
                    InputOption::VALUE_NONE,
                    'All Products includes product not Visibility'
                ),

            ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $date = new \DateTime();
        // $date = new DateTime('2025-01-01');
        $this->todayStartOfDayDate = $date->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $this->todayEndOfDayDate = $date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $today = \date('Y-m-d');
        /* strtotime('next month') */
        /* strtotime('next week') */
        /* strtotime("+10 days") */
        $this->expiryDate = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($today)) );

        $returnValue = Cli::RETURN_SUCCESS;
        try {
            $date   = $input->getArgument(self::DATE_ARGUMENT);
            if($date){
                if(is_numeric($date)){
                    $this->expiryDate = date('Y-m-d H:i:s', strtotime('+' . $date . ' days', strtotime($today)) );
                }else {
                    $this->expiryDate = date('Y-m-d H:i:s', strtotime($date, strtotime($today)) );
                }
            }
            $model = $this->objectManager->get('Magento\Catalog\Model\Product\Action');
            $products = $this->getNewProducts();
            $num = 0;
            $output->writeln(__("Start update new product to %1.", $this->expiryDate));
            foreach ($products as $product) {
                $productId = $product->getId();
                // $product->setStoreId(0)->setData('news_to_date', $setTime)->save();
                $model->updateAttributes([$productId], ['news_to_date' => $this->expiryDate], 0);
                $name = $product->getName() . ' (ID: ' . $productId . ')';
                $output->writeln($name);
                $num++;
            }

            $message = __("Successfully updated %1 New product(s)!", $num);
            $output->writeln($message);

            $products = $this->getSaleProducts();
            $num = 0;
            $output->writeln(__("Start update sale product to %1.", $this->expiryDate));
            foreach ($products as $product) {
                $productId = $product->getId();
                // $product->setStoreId(0)->setData('special_to_date', $setTime)->save();
                $model->updateAttributes([$productId], ['special_to_date' => $this->expiryDate], 0);
                $name = $product->getName() . ' (ID: ' . $productId . ')';
                $output->writeln($name);
                $num++;
            }

            $message = __("Successfully updated %1 Sale product(s)!", $num);
            $output->writeln($message);

        } catch (IOExceptionInterface $e) {
            $message = __("An error occurred while deleting your directory at %1", $e->getPath());
            $output->writeln($message);
            $output->writeln($e->getMessage());
            $returnValue = Cli::RETURN_FAILURE;

            $this->logger->critical($e->getMessage());
        }

        return $returnValue;
    }

    public function getNewProducts() {

        // $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        // $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->todayEndOfDayDate;
        $todayStartOfDayDate = $this->todayStartOfDayDate;
        $collection = $this->productCollectionFactory->create()
                            ->addAttributeToSelect('name')
                            ->addAttributeToFilter(
                                'news_from_date',
                                [
                                    'or' => [
                                        0 => ['date' => true, 'to' => $todayEndOfDayDate],
                                        1 => ['is' => new \Zend_Db_Expr('null')],
                                    ]
                                ],
                                'left'
                            )
                            // ->addAttributeToFilter(
                            //     'news_to_date',
                            //     [
                            //         'or' => [
                            //             0 => ['date' => true, 'from' => $todayStartOfDayDate],
                            //             1 => ['is' => new \Zend_Db_Expr('null')],
                            //         ]
                            //     ],
                            //     'left'
                            // )
                            ->addAttributeToFilter(
                                [
                                    ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                                    ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                                ]
                            )->addAttributeToSort('news_from_date', 'desc')
                            ->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
                            // ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
                            ->addStoreFilter();

        return $collection;
    }

    public function getSaleProducts(){

        // $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        // $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->todayEndOfDayDate;
        $todayStartOfDayDate = $this->todayStartOfDayDate;
        $collection = $this->productCollectionFactory->create()
                            ->addAttributeToSelect('name')
                            ->addAttributeToFilter(
                                'special_from_date',
                                [
                                    'or' => [
                                        0 => ['date' => true, 'to' => $todayEndOfDayDate],
                                        1 => ['is' => new \Zend_Db_Expr('null')],
                                    ]
                                ],
                                'left'
                            )
                            // ->addAttributeToFilter(
                            //     'special_to_date',
                            //     [
                            //         'or' => [
                            //             0 => ['date' => true, 'from' => $todayStartOfDayDate],
                            //             1 => ['is' => new \Zend_Db_Expr('null')],
                            //         ]
                            //     ],
                            //     'left'
                            // )
                            ->addAttributeToFilter(
                                [
                                    ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
                                    ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
                                ]
                            )->addAttributeToSort('special_to_date', 'desc')
                            ->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
                            // ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
                            ->addStoreFilter();

        return $collection;

    }

}