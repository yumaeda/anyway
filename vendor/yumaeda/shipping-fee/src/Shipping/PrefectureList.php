<?php
namespace Yumaeda\Shipping;

/**
 * Represents a prefecture list in Japan
 *
 * @package  shipping-fee
 * @author   Yukitaka Maeda <yumaeda@gmail.com>
 * @version  1.0.0
 * @access   public
 * @see      https://github.com/yumaeda/shipping-fee
*/
class PrefectureList
{
    /**
     * @var string Contains key for Hokkaido prefecture
     */
    const HOKKAIDO_KEY = 'HOKKAIDO';

    /**
     * @var string Contains key for Aomori prefecture
     */
    const AOMORI_KEY = 'AOMORI';

    /**
     * @var string Contains key for Iwate prefecture
     */
    const IWATE_KEY  = 'IWATE';

    /**
     * @var string Contains key for Akita prefecture
     */
    const AKITA_KEY  = 'AKITA';

    /**
     * @var string Contains key for Miyagi prefecture
     */
    const MIYAGI_KEY = 'MIYAGI';

    /**
     * @var string Contains key for Yamagata prefecture
     */
    const YAMAGATA_KEY = 'YAMAGATA';

    /**
     * @var string Contains key for Fukushima prefecture
     */
    const FUKUSHIMA_KEY = 'FUKUSHIMA';

    /**
     * @var string Contains key for Ibaraki prefecture
     */
    const IBARAKI_KEY = 'IBARAKI';

    /**
     * @var string Contains key for Tochigi prefecture
     */
    const TOCHIGI_KEY = 'TOCHIGI';

    /**
     * @var string Contains key for Gunma prefecture
     */
    const GUNMA_KEY = 'GUNMA';

    /**
     * @var string Contains key for Saitama prefecture
     */
    const SAITAMA_KEY = 'SAITAMA';

    /**
     * @var string Contains key for Chiba prefecture
     */
    const CHIBA_KEY = 'CHIBA';

    /**
     * @var string Contains key for Tokyo prefecture
     */
    const TOKYO_KEY = 'TOKYO';

    /**
     * @var string Contains key for Kanagawa prefecture
     */
    const KANAGAWA_KEY = 'KANAGAWA';

    /**
     * @var string Contains key for Yamanashi prefecture
     */
    const YAMANASHI_KEY = 'YAMANASHI';

    /**
     * @var string Contains key for Niigata prefecture
     */
    const NIIGATA_KEY = 'NIIGATA';

    /**
     * @var string Contains key for Nagano prefecture
     */
    const NAGANO_KEY = 'NAGANO';

    /**
     * @var string Contains key for Gifu prefecture
     */
    const GIFU_KEY = 'GIFU';

    /**
     * @var string Contains key for Shizuoka prefecture
     */
    const SHIZUOKA_KEY = 'SHIZUOKA';

    /**
     * @var string Contains key for Aichi prefecture
     */
    const AICHI_KEY = 'AICHI';

    /**
     * @var string Contains key for Mie prefecture
     */
    const MIE_KEY = 'MIE';

    /**
     * @var string Contains key for Toyama prefecture
     */
    const TOYAMA_KEY = 'TOYAMA';

    /**
     * @var string Contains key for Ishikawa prefecture
     */
    const ISHIKAWA_KEY = 'ISHIKAWA';

    /**
     * @var string Contains key for Fukui prefecture
     */
    const FUKUI_KEY = 'FUKUI';

    /**
     * @var string Contains key for Shiga prefecture
     */
    const SHIGA_KEY = 'SHIGA';

    /**
     * @var string Contains key for Kyoto prefecture
     */
    const KYOTO_KEY = 'KYOTO';

    /**
     * @var string Contains key for Oosaka prefecture
     */
    const OOSAKA_KEY = 'OOSAKA';

    /**
     * @var string Contains key for Hyogo prefecture
     */
    const HYOGO_KEY = 'HYOGO';

    /**
     * @var string Contains key for Nara prefecture
     */
    const NARA_KEY = 'NARA';

    /**
     * @var string Contains key for Wakayama prefecture
     */
    const WAKAYAMA_KEY = 'WAKAYAMA';

    /**
     * @var string Contains key for Tottori prefecture
     */
    const TOTTORI_KEY = 'TOTTORI';

    /**
     * @var string Contains key for Shimane prefecture
     */
    const SHIMANE_KEY = 'SHIMANE';

    /**
     * @var string Contains key for Okayama prefecture
     */
    const OKAYAMA_KEY = 'OKAYAMA';

    /**
     * @var string Contains key for Hiroshima prefecture
     */
    const HIROSHIMA_KEY = 'HIROSHIMA';

    /**
     * @var string Contains key for Yaaguchi prefecture
     */
    const YAMAGUCHI_KEY = 'YAMAGUCHI';

    /**
     * @var string Contains key for Tokushima prefecture
     */
    const TOKUSHIMA_KEY = 'TOKUSHIMA';

    /**
     * @var string Contains key for Kagawa prefecture
     */
    const KAGAWA_KEY = 'KAGAWA';

    /**
     * @var string Contains key for Ehime prefecture
     */
    const EHIME_KEY = 'EHIME';

    /**
     * @var string Contains key for Kochi prefecture
     */
    const KOCHI_KEY = 'KOCHI';

    /**
     * @var string Contains key for Fukuoka prefecture
     */
    const FUKUOKA_KEY = 'FUKUOKA';

    /**
     * @var string Contains key for Saga prefecture
     */
    const SAGA_KEY = 'SAGA';

    /**
     * @var string Contains key for Nagasaki prefecture
     */
    const NAGASAKI_KEY = 'NAGASAKI';

    /**
     * @var string Contains key for Ooita prefecture
     */
    const OOITA_KEY = 'OOITA';

    /**
     * @var string Contains key for Kumamoto prefecture
     */
    const KUMAMOTO_KEY = 'KUMAMOTO';

    /**
     * @var string Contains key for Miyazaki prefecture
     */
    const MIYAZAKI_KEY = 'MIYAZAKI';

    /**
     * @var string Contains key for Kagoshima prefecture
     */
    const KAGOSHIMA_KEY = 'KAGOSHIMA';

    /**
     * @var string Contains key for Okinawa prefecture
     */
    const OKINAWA_KEY = 'OKINAWA';

    /**
     * @var int Contains index of the prefecture name
     */
    const NAME_INDEX = 0;

    /**
     * @var int Contains index of the prefecture region
     */
    const REGION_INDEX = 1;

    /**
     * @var array $prefectures Contains list fo prefectures
     */
    private $prefectures;
    
    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->prefectures = [
            self::HOKKAIDO_KEY  => [ '北海道', RegionalFeeTable::HOKKAIDO_KEY ],
            self::AOMORI_KEY    => [ '青森県', RegionalFeeTable::TOHOKU_KEY ],
            self::IWATE_KEY     => [ '岩手県', RegionalFeeTable::TOHOKU_KEY ],
            self::AKITA_KEY     => [ '秋田県', RegionalFeeTable::TOHOKU_KEY ],
            self::MIYAGI_KEY    => [ '宮城県', RegionalFeeTable::TOHOKU_KEY ],
            self::YAMAGATA_KEY  => [ '山形県', RegionalFeeTable::TOHOKU_KEY ],
            self::FUKUSHIMA_KEY => [ '福島県', RegionalFeeTable::TOHOKU_KEY ],
            self::IBARAKI_KEY   => [ '茨城県', RegionalFeeTable::KANTO_KEY ],
            self::TOCHIGI_KEY   => [ '栃木県', RegionalFeeTable::KANTO_KEY ],
            self::GUNMA_KEY     => [ '群馬県', RegionalFeeTable::KANTO_KEY ],
            self::SAITAMA_KEY   => [ '埼玉県', RegionalFeeTable::KANTO_KEY ],
            self::CHIBA_KEY     => [ '千葉県', RegionalFeeTable::KANTO_KEY ],
            self::TOKYO_KEY     => [ '東京都', RegionalFeeTable::KANTO_KEY ],
            self::KANAGAWA_KEY  => [ '神奈川県', RegionalFeeTable::KANTO_KEY ],
            self::YAMANASHI_KEY => [ '山梨県', RegionalFeeTable::KANTO_KEY ],
            self::NIIGATA_KEY   => [ '新潟県', RegionalFeeTable::SHINETSU_KEY ],
            self::NAGANO_KEY    => [ '長野県', RegionalFeeTable::SHINETSU_KEY ],
            self::GIFU_KEY      => [ '岐阜県', RegionalFeeTable::TOKAI_KEY ],
            self::SHIZUOKA_KEY  => [ '静岡県', RegionalFeeTable::TOKAI_KEY ],
            self::AICHI_KEY     => [ '愛知県', RegionalFeeTable::TOKAI_KEY ],
            self::MIE_KEY       => [ '三重県', RegionalFeeTable::TOKAI_KEY ],
            self::TOYAMA_KEY    => [ '富山県', RegionalFeeTable::HOKURIKU_KEY ],
            self::ISHIKAWA_KEY  => [ '石川県', RegionalFeeTable::HOKURIKU_KEY ],
            self::FUKUI_KEY     => [ '福井県', RegionalFeeTable::HOKURIKU_KEY ],
            self::SHIGA_KEY     => [ '滋賀県', RegionalFeeTable::KANSAI_KEY ],
            self::KYOTO_KEY     => [ '京都府', RegionalFeeTable::KANSAI_KEY ],
            self::OOSAKA_KEY    => [ '大阪府', RegionalFeeTable::KANSAI_KEY ],
            self::HYOGO_KEY     => [ '兵庫県', RegionalFeeTable::KANSAI_KEY ],
            self::NARA_KEY      => [ '奈良県', RegionalFeeTable::KANSAI_KEY ],
            self::WAKAYAMA_KEY  => [ '和歌山県', RegionalFeeTable::KANSAI_KEY ],
            self::TOTTORI_KEY   => [ '鳥取県', RegionalFeeTable::CYUGOKU_KEY ],
            self::SHIMANE_KEY   => [ '島根県', RegionalFeeTable::CYUGOKU_KEY ],
            self::OKAYAMA_KEY   => [ '岡山県', RegionalFeeTable::CYUGOKU_KEY ],
            self::HIROSHIMA_KEY => [ '広島県', RegionalFeeTable::CYUGOKU_KEY ],
            self::YAMAGUCHI_KEY => [ '山口県', RegionalFeeTable::CYUGOKU_KEY ],
            self::TOKUSHIMA_KEY => [ '徳島県', RegionalFeeTable::SHIKOKU_KEY ],
            self::KAGAWA_KEY    => [ '香川県', RegionalFeeTable::SHIKOKU_KEY ],
            self::EHIME_KEY     => [ '愛媛県', RegionalFeeTable::SHIKOKU_KEY ],
            self::KOCHI_KEY     => [ '高知県', RegionalFeeTable::SHIKOKU_KEY ],
            self::FUKUOKA_KEY   => [ '福岡県', RegionalFeeTable::KYUSYU_KEY ],
            self::SAGA_KEY      => [ '佐賀県', RegionalFeeTable::KYUSYU_KEY ],
            self::NAGASAKI_KEY  => [ '長崎県', RegionalFeeTable::KYUSYU_KEY ],
            self::OOITA_KEY     => [ '大分県', RegionalFeeTable::KYUSYU_KEY ],
            self::KUMAMOTO_KEY  => [ '熊本県', RegionalFeeTable::KYUSYU_KEY ],
            self::MIYAZAKI_KEY  => [ '宮崎県', RegionalFeeTable::KYUSYU_KEY ],
            self::KAGOSHIMA_KEY => [ '鹿児島県', RegionalFeeTable::KYUSYU_KEY ],
            self::OKINAWA_KEY   => [ '沖縄県', RegionalFeeTable::OKINAWA_KEY ],
        ];
    }

    /**
     * Returns name of the prefecture
     *
     * @access public
     * @param  string $key Index key for the prefecture
     * @return string Name of the prefecture 
     */
    public function getName($key)
    {
        return $this->prefectures[$key][self::NAME_INDEX];
    }

    /**
     * Returns region key for the prefecture
     *
     * @access public
     * @param  string $prefecture_key Index key for the prefecture
     * @return string Region key for the prefecture
     */
    public function getRegionKey($prefecture_key)
    {
        return $this->prefectures[$prefecture_key][self::REGION_INDEX];
    }
}

