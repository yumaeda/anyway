<?php
namespace Yumaeda\Shipping;

/**
 * Calculates Sagawa's shipping fee upon delivery in Japan based on various conditions.
 *
 * @package  shipping-fee
 * @author   Yukitaka Maeda <yumaeda@gmail.com>
 * @version  1.0.0
 * @access   public
 * @see      https://github.com/yumaeda/shipping-fee
*/
class SagawaShippingFee extends ShippingFee
{
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
     * Gets the fee for using refrigerated delivery for the specified
     * number of bottles
     *
     * @access public
     * @param  int $bottle_qty Number of bottles to ship
     * @return int Fee for refrigerated delivery
     */
    public function getRefrigeratedDeliveryFee($bottle_qty)
    {
        $fee = 0;

        $size_counts = $this->getBoxSizeCounts($bottle_qty);
        $fee += (self::REFRIGERATED_DELIVERY_COST * $size_counts['60']);
        $fee += (self::REFRIGERATED_DELIVERY_COST * $size_counts['80']);
        $fee += (self::REFRIGERATED_DELIVERY_COST * $size_counts['140']);

        return $fee;
    }

    /**
     * Get number of boxes for 3 different sizes (60, 80, and 140)
     *
     * @access private
     * @param  int $bottle_qty Number of bottles to ship
     * @return array Number of boxes for 3 different sizes
     */
    private function getBoxSizeCounts($bottle_qty)
    {
        $size60 = 0;
        $size80 = 0;
        $size140 = 0;

        if ($bottle_qty > self::MAX_BOTTLE_COUNT) {
            $size140 = floor($bottle_qty / self::MAX_BOTTLE_COUNT);
        }

        $remaining_bottles = ($bottle_qty % self::MAX_BOTTLE_COUNT);

        if (($remaining_bottles === 1) || ($remaining_bottles === 2))
        {
            ++$size60;
        } elseif (($remaining_bottles >= 3) && ($remaining_bottles <= 6)) {
            ++$size80;
        } else {
            ++$size140;
        }

        return [
            '60'  => $size60,
            '80'  => $size80,
            '140' => $size140,
        ];
    }
}

