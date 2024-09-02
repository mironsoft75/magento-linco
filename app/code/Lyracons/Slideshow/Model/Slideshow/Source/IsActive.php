<?php
namespace Lyracons\Slideshow\Model\Slideshow\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $slideshow;

    /**
     * Constructor
     *
     * @param \Lyracons\Slideshow\Model\Slideshow $slideshow
     */
    public function __construct(\Lyracons\Slideshow\Model\Slideshow $slideshow)
    {
        $this->slideshow = $slideshow;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->slideshow->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
