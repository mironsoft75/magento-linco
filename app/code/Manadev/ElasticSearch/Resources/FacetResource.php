<?php

namespace Manadev\ElasticSearch\Resources;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\Core\Traits\FrontendDependencies;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Dropdown\OptimizedFacet;
use Manadev\Seo\Enums\UrlKeyStatus;
use Zend_Db_Expr;
use Manadev\Seo\Enums\UrlKeySubType as UrlKeySubTypeEnum;

class FacetResource extends Db\AbstractDb
{
    use FrontendDependencies;

    protected $attributesUsingCustomSourceModel;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalog_product_index_eav');
    }

    public function selectDropdownBatch($optionIds, $selectedOptionIds) {
        if (empty($optionIds)) {
            return null;
        }

        $select = $this->selectLabels($optionIds, $selectedOptionIds);
        $this->addSeoColumns($select);
        $this->addAttributeIdColumn($select);
        return $select;
    }

    public function selectSwatchBatch($optionIds, $selectedOptionIds) {
        if (empty($optionIds)) {
            return null;
        }

        $select = $this->selectLabels($optionIds, $selectedOptionIds);
        $this->addSwatchColumns($select);
        $this->addSeoColumns($select);
        $this->addAttributeIdColumn($select);
        return $select;
    }

    public function selectDropdownSlider($optionIds) {
        if (count($optionIds) < 3) {
            return null;
        }

        return $this->selectLabels($optionIds, []);
    }

    protected function selectLabels($optionIds, $selectedOptionIds) {
        $db = $this->getConnection();

        $optionIds = array_map(function($id) { return "'$id'"; }, $optionIds);

        return $db->select()
            ->from(['o' => $this->getTable('eav_attribute_option')], null)
            ->where("`o`.`option_id` IN (" . implode(',', $optionIds) . ")")
            ->joinInner(['vg' => $this->getTable('eav_attribute_option_value')],
                $db->quoteInto("`vg`.`option_id` = `o`.`option_id` AND `vg`.`store_id` = ?", 0), null)
            ->joinLeft(['vs' => $this->getTable('eav_attribute_option_value')],
                $db->quoteInto("`vs`.`option_id` = `o`.`option_id` AND `vs`.`store_id` = ?",
                $this->getStoreId()), null)
            ->columns([
                'sort_order' => new Zend_Db_Expr("`o`.`sort_order`"),
                'value' => new Zend_Db_Expr("`o`.`option_id`"),
                'label' => new Zend_Db_Expr("COALESCE(`vs`.`value`, `vg`.`value`)"),
                'is_selected' => new Zend_Db_Expr(empty($selectedOptionIds)
                    ? "1 <> 1"
                    : "`o`.`option_id` IN (" . implode(',', $selectedOptionIds). ")"),
            ]);
    }


    protected function addSwatchColumns(Select $select) {
        $db = $this->getConnection();

        $select
            ->joinLeft(['sg' => $this->getTable('eav_attribute_option_swatch')],
                $db->quoteInto("`sg`.`option_id` = `o`.`option_id` AND `sg`.`store_id` = ?", 0), null)
            ->joinLeft(['ss' => $this->getTable('eav_attribute_option_swatch')],
                $db->quoteInto("`ss`.`option_id` = `o`.`option_id` AND `ss`.`store_id` = ?", $this->getStoreId()), null)
            ->columns([
                'swatch_type' => new Zend_Db_Expr("COALESCE(`ss`.`type`, `sg`.`type`)"),
                'swatch' => new Zend_Db_Expr("COALESCE(`ss`.`value`, `sg`.`value`)"),
            ]);
    }

    protected function addSeoColumns(Select $select) {
        if (!$this->isFeatureEnabled('Manadev_LayeredNavigationSeo')) {
            return;
        }

        $db = $this->getConnection();

        $select
            ->joinLeft(['url_key' => $this->getTable('mana_url_key')],
                "`url_key`.`option_id` = `o`.`option_id` AND " .
                $db->quoteInto("`url_key`.`sub_type` = ?", UrlKeySubTypeEnum::FILTER_OPTION) . " AND " .
                $db->quoteInto("`url_key`.`status` = ?", UrlKeyStatus::ACTIVE) . " AND " .
                $db->quoteInto("`url_key`.`store_id` = ?", $this->getStoreId()), null)
            ->columns([
                'url_key' => new Zend_Db_Expr("`url_key`.`url_key`"),
                'url_position' => new Zend_Db_Expr("`url_key`.`position`"),
            ]);
    }

    protected function addAttributeIdColumn(Select $select) {
        $select
            ->columns([
                'attribute_id' => new Zend_Db_Expr("`o`.`attribute_id`"),
            ])
            ->order('attribute_id');
    }

    public function getSelectedData(Facet $facet) {
        /* @var $facet OptimizedFacet */
        $selectedOptionIds = $facet->getSelectedOptionIds();
        if (empty($selectedOptionIds)) {
            return false;
        }

        if ($facet->getData() !== false) {
            foreach ($facet->getData() as $item) {
                if (($index = array_search($item['value'], $selectedOptionIds)) !== false) {
                    unset($selectedOptionIds[$index]);
                }
            }
        }

        if (empty($selectedOptionIds)) {
            return false;
        }

        $db = $this->getConnection();

        return $db->fetchAssoc($db->select()
            ->from(['o' => $this->getTable('eav_attribute_option')], null)
            ->where("`o`.`option_id` IN (" . implode(',', $selectedOptionIds) . ")")
            ->joinInner(['vg' => $this->getTable('eav_attribute_option_value')],
                $db->quoteInto("`vg`.`option_id` = `o`.`option_id` AND `vg`.`store_id` = ?", 0), null)
            ->joinLeft(['vs' => $this->getTable('eav_attribute_option_value')],
                $db->quoteInto("`vs`.`option_id` = `o`.`option_id` AND `vs`.`store_id` = ?",
                $this->getStoreId()), null)
            ->columns([
                'value' => new Zend_Db_Expr("`o`.`option_id`"),
                'label' => new Zend_Db_Expr("COALESCE(`vs`.`value`, `vg`.`value`)"),
                'is_selected' => new Zend_Db_Expr("1"),
            ]));
    }

    public function usesCustomSourceModel(Facet $facet) {
        /* @var $facet OptimizedFacet */
        if (!$this->attributesUsingCustomSourceModel) {
            $db = $this->getConnection();

            $this->attributesUsingCustomSourceModel = $db->fetchCol($db->select()
                ->from($this->getTable('eav_attribute'), 'attribute_id')
                ->where("`entity_type_id` = ?", 4)
                ->where("`frontend_input` = 'boolean' OR " .
                    "(`frontend_input` IN ('dropdown', 'multiselect') AND " .
                    "`source_model` IS NOT NULL AND " .
                    "`source_model` <> 'Magento\Eav\Model\Entity\Attribute\Source\Table')")
            );
        }

        return in_array($facet->getAttributeId(), $this->attributesUsingCustomSourceModel);
    }
}
