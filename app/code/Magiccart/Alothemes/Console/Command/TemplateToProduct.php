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
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

class TemplateToProduct extends Command
{

    /**
     * Attribute argument
     */
    const ATTRIBUTE_ARGUMENT = 'attribute';

    /**
     * Sku argument
     */
    const SKU_ARGUMENT = 'sku';

    /**
     * Template argument
     */
    const TEMPLATE_ARGUMENT = 'template';

    /**
     * Allow option
     */
    const ALL_PRODUCT = 'all-product';

    /**
     * @var Magento\PageBuilder\Model\TemplateRepository
     */
    private $templateRepository;

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
     * @var objectManager
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductVisibility $productVisibility
     * @param string $name
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ProductVisibility $productVisibility,
        string $name = null,
        ?LoggerInterface $logger = null
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->objectManager = ObjectManager::getInstance();
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);

        parent::__construct($name);
    }

    protected function configure()
    {
        // command: php bin/magento TemplateToProduct 2 description 24-MB02-1 
        // command: php bin/magento TemplateToProduct 2 description '*'
        $this->setName('TemplateToProduct')
            ->setDescription('Add a Template to product attribute')
            ->setDefinition([
                new InputArgument(
                    self::TEMPLATE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Template Id of PageBuilder: Admin Panel > Content > Templates'
                ),
                new InputArgument(
                    self::ATTRIBUTE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Attribute of Product'
                ),
                new InputArgument(
                    self::SKU_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Sku of Product'
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
        $returnValue = Cli::RETURN_SUCCESS;
        $productMetadata = $this->objectManager->get(ProductMetadataInterface::class);
        $version = str_replace(['dev-', '-develop'], ['', '.0'], $productMetadata->getVersion());
        $versionSupport = '2.3.4';
        if (version_compare($version, $versionSupport, '<')) {
            $message = __("This command not require Magento version or latest!", $versionSupport);
            $output->writeln($message);
            return $returnValue;
        }
        try {
            $attribute = $input->getArgument(self::ATTRIBUTE_ARGUMENT);
            if ($attribute) $attribute = 'description';
            $sku = $input->getArgument(self::SKU_ARGUMENT);
            if(!$sku) $sku = '*';
            $templateId = $input->getArgument(self::TEMPLATE_ARGUMENT);
            $allProduct = $input->getOption(self::ALL_PRODUCT);
            $template = $this->getTemplate($templateId);
            $model = $this->objectManager->get('Magento\Catalog\Model\Product\Action');
            $products = $this->getProducts($sku, $allProduct);
            $num = 0;
            foreach ($products as $product) {
                $id   = $product->getId();
                $name = $product->getName() . ' (ID: ' . $id . ')';
                $output->writeln($name);
                $model->updateAttributes([$id], [$attribute => $template->getTemplate()], 0);
                $num++;
            }

            $message = __("Successfully updated %1 %2 product(s)!", $num, $attribute);
            $output->writeln($message);

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
            $collection->addAttributeToFilter('sku', $skuFilter);
        } else if(!$allProduct){
            $collection->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        }

        return $collection;
    }

    public function getTemplate($templateId)
    {
        if(!$this->templateRepository){
            $this->templateRepository = $this->objectManager->get('Magento\PageBuilder\Model\TemplateRepository');
        }
        $template = $this->templateRepository->get($templateId);

        return $template;
    }
}