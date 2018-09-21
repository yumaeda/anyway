<?php
namespace Yumaeda\Shipping;

/**
 * Holds fee for each regions in Japan
 *
 * Example usage:
 *
 * @package  shipping-fee
 * @author   Yukitaka Maeda <yumaeda@gmail.com>
 * @version  1.0.0
 * @access   public
 * @see      https://github.com/yumaeda/shipping-fee
*/
class RegionalFeeTable
{
    /**
     * @var string Contains key for Hokkaido region
     */
    const HOKKAIDO_KEY = 'HOKKAIDO';

    /**
     * @var string Contains key for Tohoku region
     */
    const TOHOKU_KEY = 'TOHOKU';

    /**
     * @var string Contains key for Kanto region
     */
    const KANTO_KEY = 'KANTO';

    /**
     * @var string Contains key for Shinetsu region
     */
    const SHINETSU_KEY = 'SHINETSU';

    /**
     * @var string Contains key for Tokai region
     */
    const TOKAI_KEY = 'TOKAI';

    /**
     * @var string Contains key for Hokuriku region
     */
    const HOKURIKU_KEY = 'HOKURIKU';

    /**
     * @var string Contains key for Kansai region
     */
    const KANSAI_KEY = 'KANSAI';

    /**
     * @var string Contains key for Cyugoku region
     */
    const CYUGOKU_KEY = 'CYUGOKU';

    /**
     * @var string Contains key for Shikoku region
     */
    const SHIKOKU_KEY = 'SHIKOKU';

    /**
     * @var string Contains key for Kyusyu region
     */
    const KYUSYU_KEY = 'KYUSYU';

    /**
     * @var string Contains key for Okinawa region
     */
    const OKINAWA_KEY = 'OKINAWA';

    /**
     * @var int Contains index of the region name
     */
    const NAME_INDEX = 0;

    /**
     * @var int Contains index of the region fee
     */
    const FEE_INDEX = 1;

    /**
     * @var int Contains maximum shipping fee
     */
    const MAX_FEE = 999999;

    /**
     * @var array $regional_fees Contains shipping info of the regional_fees
     */
    private $regional_fees;
    
    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->regional_fees = [
            self::HOKKAIDO_KEY => [ '北海道', self::MAX_FEE ],
            self::TOHOKU_KEY   => [ '東北', self::MAX_FEE ],
            self::KANTO_KEY    => [ '関東', self::MAX_FEE ],
            self::SHINETSU_KEY => [ '信越', self::MAX_FEE ],
            self::TOKAI_KEY    => [ '東海', self::MAX_FEE ],
            self::HOKURIKU_KEY => [ '北陸', self::MAX_FEE ],
            self::KANSAI_KEY   => [ '関西', self::MAX_FEE ],
            self::CYUGOKU_KEY  => [ '中国', self::MAX_FEE ],
            self::SHIKOKU_KEY  => [ '四国', self::MAX_FEE ],
            self::KYUSYU_KEY   => [ '九州', self::MAX_FEE ],
            self::OKINAWA_KEY  => [ '沖縄', self::MAX_FEE ],
        ];
    }

    /**
     * Returns name of the region
     *
     * @access public
     * @param  string $key Index key for the region
     * @return string Name of the region
     */
    public function getName($key)
    {
        return $this->regional_fees[$key][self::NAME_INDEX];
    }

    /**
     * Returns shipping fee for the region
     *
     * @access public
     * @param  string $key Index key for the region
     * @return int Shipping fee for the region
     */
    public function getFee($key)
    {
        return $this->regional_fees[$key][self::FEE_INDEX];
    }

    /**
     * Sets shipping fee for the region
     *
     * @access public
     * @param  string $key Index key for the region
     * @param  int $fee Shipping fee for the region
     * @return void
     */
    public function setFee($key, $fee)
    {
        $this->regional_fees[$key][self::FEE_INDEX] = $fee;
    }
}

