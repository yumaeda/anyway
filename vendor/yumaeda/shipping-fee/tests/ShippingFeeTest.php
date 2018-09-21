<?php
use PHPUnit\Framework\TestCase;
use Yumaeda\Shipping\YamatoShippingFee;
use Yumaeda\Shipping\SagawaShippingFee;

class ShippingFeeTest extends TestCase
{
    public function testYamatoShippingFee()
    {
        $prefecture = 'TOKYO';
        $btl_qty    = 12;
        $is_cool    = true;
        $price      = 1000;

        $yamato_fee = new YamatoShippingFee($prefecture, $btl_qty, $is_cool, $price);
        $yamato_fee->setRegionalFee('HOKKAIDO', 1310);
        $yamato_fee->setRegionalFee('TOHOKU', 1010);
        $yamato_fee->setRegionalFee('KANTO', 910);
        $yamato_fee->setRegionalFee('SHINETSU', 910);
        $yamato_fee->setRegionalFee('TOKAI', 910);
        $yamato_fee->setRegionalFee('HOKURIKU', 910);
        $yamato_fee->setRegionalFee('KANSAI', 1010);
        $yamato_fee->setRegionalFee('CYUGOKU', 1110);
        $yamato_fee->setRegionalFee('SHIKOKU', 1110);
        $yamato_fee->setRegionalFee('KYUSYU', 1310);
        $yamato_fee->setRegionalFee('OKINAWA', 2800);
        $fee = $yamato_fee->calculate();

        $this->assertEquals($fee, 1820);
    }

    public function testSagawaShippingFee()
    {
        $prefecture = 'TOKYO';
        $btl_qty    = 12;
        $is_cool    = true;
        $price      = 1000;

        $sagawa_fee = new SagawaShippingFee($prefecture, $btl_qty, $is_cool, $price);
        $sagawa_fee->setRegionalFee('HOKKAIDO', 1310);
        $sagawa_fee->setRegionalFee('TOHOKU', 1010);
        $sagawa_fee->setRegionalFee('KANTO', 910);
        $sagawa_fee->setRegionalFee('SHINETSU', 910);
        $sagawa_fee->setRegionalFee('TOKAI', 910);
        $sagawa_fee->setRegionalFee('HOKURIKU', 910);
        $sagawa_fee->setRegionalFee('KANSAI', 1010);
        $sagawa_fee->setRegionalFee('CYUGOKU', 1110);
        $sagawa_fee->setRegionalFee('SHIKOKU', 1110);
        $sagawa_fee->setRegionalFee('KYUSYU', 1310);
        $sagawa_fee->setRegionalFee('OKINAWA', 2800);
        $fee = $sagawa_fee->calculate();

        $this->assertEquals($fee, 910);
    }
}

