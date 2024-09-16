<?php



namespace Addify\RestrictOrderByCustomer\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;

use Magento\Framework\Data\OptionSourceInterface;



/**

 * Topic status functionality model

 *

 * @api

 * @since 100.0.2

 */

class ProductOptions extends AbstractSource implements SourceInterface, OptionSourceInterface
{

    /**#@+

     * Topic ProductOptions values

     */

    const ALL_PRODUCTS = '1';

    const INDIVIDUAL_PRODUCTS = '2';



    /**

     * Retrieve option array with empty value

     *

     * @return string[]

     */

    public function getAllOptions()
    {

        $result = [];

        foreach (self::getOptionArray() as $index => $value) {

            $result[] = ['value' => $index, 'label' => $value];

        }

        return $result;
    }



    /**

     * Retrieve option array

     *

     * @return string[]

     */

    public static function getOptionArray()
    {

        return [self::ALL_PRODUCTS => __('All Products'),
                self::INDIVIDUAL_PRODUCTS => __('Individual Products')];

    }





}