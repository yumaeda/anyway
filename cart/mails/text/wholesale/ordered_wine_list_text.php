<?php

// --------------------------------
// [Required Variables]
//     $fIncludePrice
//     $strOrderedWines
//     $dbc
//     $totalWinePrice
//     $shippingFee
// --------------------------------

$strOrderedWinesBody = '';

if (!empty($strOrderedWines))
{
    $rgstrWine = explode('#;', $strOrderedWines);
    $cItem     = count($rgstrWine);

    for ($i = 0; $i < $cItem; ++$i)
    {
        $rgstrToken  = explode(':', $rgstrWine[$i]);
        if (count($rgstrToken) === 2)
        {
            $itemName   = '';
            $itemNumber = $rgstrToken[0];
            $quantity   = $rgstrToken[1];
            $amount     = 0;

            prepareNextQuery($dbc);
            $sqlResult = mysqli_query($dbc, "CALL get_business_customer_wine('$itemNumber')");

            if ($sqlResult !== FALSE)
            {
                $row      = mysqli_fetch_array($sqlResult, MYSQLI_ASSOC);
                $itemName = $row['vintage'] . ' ' . $row['combined_name_jpn'];
                $amount   = $row['price'];

                mysqli_free_result($sqlResult);
            }

            $strQuantity    = number_format($quantity);
            $strAmount      = number_format($amount);
            $strTotalAmount = number_format($quantity * $amount);

            $strOrderedWinesBody .= "

-------------------------------------------------------------
 [$itemNumber] $itemName
-------------------------------------------------------------
 数量: $strQuantity";

            if (isset($fIncludePrice) && $fIncludePrice)
            {
                $strOrderedWinesBody .= "
 単価: ￥$strAmount
 金額: ￥$strTotalAmount";
            }
        }
    }

    if (isset($fIncludePrice) && $fIncludePrice)
    {
        $unTaxedTotal    = ($totalWinePrice + $shippingFee);
        $grandTotal      = floor(1.08 * $unTaxedTotal);
        $tax             = $grandTotal - $unTaxedTotal;
        $strTotalPrice   = number_format($totalWinePrice);
        $strShippingFee  = number_format($shippingFee);
        $strTotalTax     = number_format($tax);
        $strTotalPayment = number_format($grandTotal);

        $strOrderedWinesBody .= "

╋━━━━━━━━━━━━━━━
┃　合計金額
╋━━━━━━━━━━━━━━━
 小計:　　　　　￥$strTotalPrice
 送料＆クール:　￥$strShippingFee
 消費税:　　　　￥$strTotalTax
_____________________________
 合計:　　　　　￥$strTotalPayment
";
    }
}
