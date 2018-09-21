<?php
namespace Yumaeda\Shipping;

/**
 * Calculates Yamato's shipping fee upon delivery in Japan based on various conditions.
 *
 * @package  shipping-fee
 * @author   Yukitaka Maeda <yumaeda@gmail.com>
 * @version  1.0.0
 * @access   public
 * @see      https://github.com/yumaeda/shipping-fee
*/
class YamatoShippingFee extends ShippingFee
{
    /**
     * @var int Contains maximum bottle count for using a refrigerated delivery for 1 box
     */
    const MAX_BOTTLE_COUNT_FOR_REFRIGERATED_DELIVERY = 9;

    /**
     * Constructor
     *
     * @access public
     * @param  string $prefecture_key Key for the prefecture to deliver
     * @param  int $bottle_qty Number of bottles for shipment
     * @param  bool $is_refrigerated Whether to use refrigerated delivery or not
     * @param  int $price The aount of purchase price
     * @return void
     */
    public function __construct($prefecture_key, $bottle_qty, $is_refrigerated, $price)
    {
        parent::__construct($prefecture_key, $bottle_qty, $is_refrigerated, $price);
    }

    /**
     * Gets maximum number of bottles for 1 delivery box
     *
     * @access protected
     * @return int maximum numberr of bottles
     */
    protected function getMaxBottleCount()
    {
        return $this->is_refrigerated ?
            self::MAX_BOTTLE_COUNT_FOR_REFRIGERATED_DELIVERY :
            parent::getMaxBottleCount();
    }
}

