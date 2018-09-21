<?php
namespace Yumaeda\Shipping;

/**
 * Calculates Shipping fee for wine set upon delivery in Japan based on various conditions.
 *
 * @package  shipping-fee
 * @author   Yukitaka Maeda <yumaeda@gmail.com>
 * @version  1.0.0
 * @access   public
 * @see      https://github.com/yumaeda/shipping-fee
*/
class WineSetShippingFee extends YamatoShippingFee
{
    /**
     * @var int Contains a number of wine sets to be delivered
     */
    private $set_count;

    /**
     * Constructor
     *
     * @access public
     * @param  string $prefecture_key Key for the prefecture to deliver
     * @param  int $bottle_qty Number of bottles for shipment
     * @param  bool $is_refrigerated Whether to use refrigerated delivery or not
     * @param  int $set_count Number of the wine sets
     * @return void
     */
    public function __construct($prefecture_key, $bottle_qty, $is_refrigerated, $set_cout)
    {
        parent::__construct($prefecture_key, $bottle_qty, $is_refrigerated, 0);
        $this->set_count = $set_count;
    }

    /**
     * Get the number of boxes eligible for free shipping
     *
     * @access protected
     * @return int number of free boxes
     */
    protected getFreeBoxCount()
    {
        return $this->set_count;
    }
}

