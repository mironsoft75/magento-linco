<?php
namespace Lyracons\Slideshow\Model;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * Lyracons Slideshow Model
 *
 * @method \Lyracons\Slideshow\Model\ResourceModel\Slideshow _getResource()
 * @method \Lyracons\Slideshow\Model\ResourceModel\Slideshow getResource()
 */
class Slideshow extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    /*     * #@+
     * Slideshow's Statuses
     */

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /*     * #@- */

    /**
     * slideshow cache tag
     */
    const CACHE_TAG = 'lyracons_slideshow';

    /**
     * @var string
     */
    protected $_cacheTag = 'lyracons_slideshow';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'lyracons_slideshow';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lyracons\Slideshow\Model\ResourceModel\Slideshow');
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        return parent::load($id, $field);
    }

    /**
     * Check if slideshow identifier exist for specific store
     * return slideshow id if slideshow exists
     *
     * @param string $identifier     
     * @return int
     */
    public function checkIdentifier($identifier)
    {
        return $this->_getResource()->checkIdentifier($identifier);
    }

    /**
     * Prepare slideshow's statuses.
     * Available event slideshow_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData('slideshow_id');
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData('identifier');
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    public function isActive()
    {
        return $this->getData('is_active');
    }
}
