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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Category;

class RandomProductCategory extends Command
{
    /**
     * Sku argument
     */
    const SKU_ARGUMENT = 'sku';

    /**
     * LIMIT argument
     */
    const LIMIT_ARGUMENT = 'limit';

    /**
     * ROOT argument
     */
    const ROOT_ARGUMENT = 'root';

    /**
     * Allow option
     */
    const ALL_PRODUCT = 'all-product';

    /**
     * Empty option
     */
    const EMPTY_CATEGORY = 'empty-category';

    protected $linkType = 'category';

    protected $commandName = 'RandomProductCategory';

    /** @var \Magento\Framework\App\State **/
    private $state;

    /**
     * @var ProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

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
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

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
     * @param \Magento\Framework\App\State $state
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Visibility $visibility
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param string $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        ProductCollectionFactory $productCollectionFactory,
        ProductVisibility $productVisibility,
        CategoryCollectionFactory $categoryCollectionFactory,
        string $name = null,
        ?LoggerInterface $logger = null
    ) {
        $this->state = $state;
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->objectManager = ObjectManager::getInstance();
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);

        parent::__construct($name);
    }

    protected function configure()
    {
        // command: php bin/magento RandomProductCategory 24-MB02-1 20
        // command: php bin/magento RandomProductCategory '*' 20
        // command: php bin/magento RandomProductCategory '*' 20 -e
        // command: php bin/magento RandomProductCategory '*' 20 2 -e
        $this->setName($this->commandName)
            ->setDescription(__('Add a Random product to %1', $this->linkType))
            ->setDefinition([
                new InputArgument(
                    self::SKU_ARGUMENT,
                    InputArgument::REQUIRED,
                    'Sku of Product'
                ),
                new InputArgument(
                    self::LIMIT_ARGUMENT,
                    InputArgument::REQUIRED,
                    __('Number product will add to %1', $this->linkType)
                ),
                new InputArgument(
                    self::ROOT_ARGUMENT,
                    InputArgument::OPTIONAL,
                    __('Only add to category with Root category ID')
                ),
                new InputOption(
                    self::ALL_PRODUCT,
                    '-a',
                    InputOption::VALUE_NONE,
                    'All Products includes product not Visibility'
                ),
                new InputOption(
                    self::EMPTY_CATEGORY,
                    '-e',
                    InputOption::VALUE_NONE,
                    'Only add to empty category'
                ),

            ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML); 
        $returnValue = Cli::RETURN_SUCCESS;
        try {
            $sku   = $input->getArgument(self::SKU_ARGUMENT);
            $limit = $input->getArgument(self::LIMIT_ARGUMENT);
            $rootId = $input->getArgument(self::ROOT_ARGUMENT);
            $allProduct = $input->getOption(self::ALL_PRODUCT);
            $emtpryCategory = $input->getOption(self::EMPTY_CATEGORY);
            $products = $this->getProducts($sku, $allProduct);
            $categories = $this->getCategories($rootId);
            $productSkus = [];
            foreach ($products as $product) {
                $productSkus[$product->getId()] = $product->getSku();
            }
            $categoriesIds = [];
            foreach ($categories as $category) {
                $categoriesIds[$category->getId()] = $category->getName();
            }

            $num = 0;
            foreach ($categoriesIds as $categoryId => $name) {
                $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
                $category->load($categoryId);
                $products = (count($productSkus) > $limit) ? array_rand($productSkus, $limit) : array_keys($productSkus);
                shuffle($products);
                $products = array_flip($products);
                if (!$category->getProductsReadonly()
                ) {
                    if($emtpryCategory && $category->hasChildren() && $category->getProductCount() ){
                        continue;
                    }
                    $category->setPostedProducts($products);
                }
                $category->save();
                $name = $name . ' (ID: ' . $categoryId . ')';
                $output->writeln($name);
                $num++;
            }
            $message = __("Successfully add product to %1 category(s)!", $num);
            $output->writeln($message);
            $arguments = new ArrayInput(['command' => 'indexer:reindex']);
            $this->getApplication()->find('indexer:reindex')->run($arguments, $output);
            $arguments = new ArrayInput(['command' => 'cache:flush']);
            $this->getApplication()->find('cache:flush')->run($arguments, $output);
        } catch (IOExceptionInterface $e) {
            $message = __("An error occurred while deleting your directory at %1", $e->getPath());
            $output->writeln($message);
            $output->writeln($e->getMessage());
            $returnValue = Cli::RETURN_FAILURE;

            $this->logger->critical($e->getMessage());
        }

        return $returnValue;
    }

    public function getProducts($skuFilter, $allProduct=false)
    {
        if ($skuFilter == '*') {
            $skuFilter = '';
        } elseif (str_contains($skuFilter, '%')) {
            $skuFilter = ['like' => $skuFilter];
        } elseif (str_contains($skuFilter, ',')) {
            $skuFilter = explode(',', $skuFilter);
        }
        $collection = $this->productCollectionFactory->create()->setStoreId(0)->addAttributeToSelect('name');

        if ($skuFilter) {
            $collection->addAttributeToSelect('sku')
                        ->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
                        ->addAttributeToFilter('sku', $skuFilter);
        } else if(!$allProduct){
            $collection->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
                    ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        }

        return $collection;
    }

    public function getCategories($rootId=Category::ROOT_CATEGORY_ID)
    {
        $categories = $this->categoryCollectionFactory->create()                              
                            ->addAttributeToSelect('name')
                            ->addAttributeToFilter('include_in_menu', 1)
                            // ->addAttributeToFilter('level', ['gt' > 1])
                            ->addIsActiveFilter();
        if($rootId){
            $categories->addFieldToFilter('path', ['like' => Category::TREE_ROOT_ID . '/' . $rootId . '/%']); //load only from store root
        }

        return $categories;
    }

}