<?php
namespace Yumaeda\Shipping;

/**
 * Calculates shipping fee upon delivery in Japan based on various conditions.
 *
 * @package  shipping-fee
 * @author   Yukitaka Maeda <yumaeda@gmail.com>
 * @version  1.0.0
 * @access   public
 * @see      https://github.com/yumaeda/shipping-fee
*/
class ShippingFee
{
    /**
     * @var int Contains a cost for using a refrigerated delivery for 1 box
     */
    const REFRIGERATED_DELIVERY_COST = 300;

    /**
     * @var int Contains a cost for using a refrigerated delivery for 1 box
     */
    const DEFAULT_FREE_SHIPPING_PRICE = 15000;

    /**
     * @var int Maximum number of bottles for 1 box
     */
    const MAX_BOTTLE_COUNT = 12;

    protected $prefectures;
    protected $regional_fees;
    protected $prefecture_key;
    protected $bottle_qty;
    protected $is_refrigerated;
    protected $price;
    protected $free_shipping_price;

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
        $this->prefecture_key = $prefecture_key;
        $this->bottle_qty = $bottle_qty;
        $this->is_refrigerated = $is_refrigerated;
        $this->price = $price;
        $this->free_shipping_price = self::DEFAULT_FREE_SHIPPING_PRICE;

        $this->prefectures = new PrefectureList();
        $this->regional_fees = new RegionalFeeTable();
    }

    /**
     * Sets shipping fee for the region
     *
     * @access public
     * @param  string $key Index key for the region
     * @param  int $fee Shipping fee for the region
     * @return void
     */
    public function setRegionalFee($region_key, $fee)
    {
        $this->regional_fees->setFee($region_key, $fee);
    }

    /**
     * Gets the fee for using refrigerated delivery for the specified
     * number of bottles
     *
     * @access public
     * @param  int $price Price eligible for free shipping (1 box)
     * @return void
     */
    protected function getMaxBottleCount()
    {
        return self::MAX_BOTTLE_COUNT;
    }

    /**
     * Get the number of boxes needed in order to ship the
     * specified number of bottles.
     *
     * @access protected
     * @param  int $bottle_qty Number of bottles to ship
     * @param  bool $is_cool Whether to use refrigerated delivery or not
     * @return int number of boxes needed
     */
    protected function getBoxCount($bottle_qty, $is_refrigerated)
    {
        $box_count = 0;

        if ($bottle_qty > 0)
        {
            $max_bottle_count = $this->getMaxBottleCount($is_refrigerated);
            $box_count = floor($bottle_qty / $max_bottle_count);
            $remaining_bottles = ($bottle_qty % $max_bottle_count);

            if ($remaining_bottles > 0)
            {
                ++$box_count;
            }
        }

        return $box_count;
    }

    /**
     * Get the number of boxes eligible for free shipping
     *
     * @access protected
     * @return int number of free boxes
     */
    protected function getFreeBoxCount()
    {
        $free_box_count = ($this->price > 0) ?
            floor($this->price / $this->free_shipping_price) :
            0;

        return $free_box_count;
    }

    /**
     * Gets the fee for using refrigerated delivery for the specified
     * number of bottles
     *
     * @access public
     * @param  int $price Price eligible for free shipping (1 box)
     * @return void
     */
    public function setFreeShippingPrice($price)
    {
        $this->free_shipping_price = $price;
    }

    /**
     * Calculates the total amount of shipping fee required
     *
     * @access public
     * @return int Total amount of shipping fee
     */
    public function calculate()
    {
        $box_count = $this->getBoxCount($this->bottle_qty, $this->is_refrigerated);
        $free_box_count = $this->getFreeBoxCount();
        if ($free_box_count > $box_count) {
            $free_box_count = $box_count;
        }
        $box_count -= $free_box_count;

        $additional_fee = 0;
        $region_key = $this->prefectures->getRegionKey($this->prefecture_key);
        $fee = $this->regional_fees->getFee($region_key);

        if (($region_key == RegionalFeeTable::HOKKAIDO_KEY) ||
            ($region_key == RegionalFeeTable::KYUSYU_KEY) ||
            ($region_key == RegionalFeeTable::OKINAWA_KEY)) {
            $kanto_fee = $this->regional_fees->getFee(RegionalFeeTable::KANTO_KEY);
            $additional_fee = $free_box_count * ($fee - $kanto_fee);
        }

        return (int)(($fee * $box_count) + $additional_fee);
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
        return self::REFRIGERATED_DELIVERY_COST * $this->getBoxCount($bottle_qty, true);
    }
}

