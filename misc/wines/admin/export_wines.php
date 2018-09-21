<?php

// -------------------------------------------------------------------
// CSVデータをShift_JIS(CP932)に変換する関数
//
// * Retrieved from http://kantaro-cgi.com/blog/php/save_csv_by_sjis.html
// -------------------------------------------------------------------
function arr2csv($fields)
{
    $fp = fopen('php://temp', 'r+b');
    foreach($fields as $field)
    {
        fputcsv($fp, $field);
    }

    rewind($fp);

    $tmp = str_replace(PHP_EOL, "\r\n", stream_get_contents($fp));
    return mb_convert_encoding($tmp, 'SJIS-win', 'UTF-8');
}

function getCategory($strType)
{
    $strCategory = '';

    switch ($i)
    {
    case 'Mousseux':
        $strCategory = 'スパークリング';
        break;
    case 'Champagne':
        $strCategory = 'シャンパーニュ';
        break;
    case 'Blanc':
        $strCategory = '白ワイン';
        break;
    case 'Rosé':
        $strCategory = 'ロゼワイン';
        break;
    case 'Rouge':
        $strCategory = '赤ワイン';
        break;
    }
}

$rgstrItemCsvHeader = array(
    'path',
    'name',
    'code',
    'sub-code',
    'original-price',
    'price',
    'sale-price',
    'options',
    'headline',
    'caption',
    'abstract',
    'explanation',
    'additional1',
    'additional2',
    'additional3',
    'relevant-links',
    'ship-weight',
    'taxable',
    'release-date',
    'temporary-point-term',
    'point-code',
    'meta-desc',
    'template',
    'sale-period-start',
    'sale-period-end',
    'sale-limit',
    'sp-code',
    'brand-code',
    'yahoo-product-code',
    'product-code',
    'jan',
    'isbn',
    'delivery',
    'astk-code',
    'condition',
    'product-category',
    'spec1',
    'spec2',
    'spec3',
    'spec4',
    'spec5',
    'spec6',
    'spec7',
    'spec8',
    'spec9',
    'spec10',
    'display',
    'sort',
    'sp-additional');

$rgstrStockCsvHeader = array(
    'code',
    'sub-code',
    'quantity',
    'allow-overdraft'
);

$categoryHash = array(
    'Mousseux'       => 'スパークリング',
    'Mousseux Rosé'  => 'スパークリング・ロゼ',
    'Champagne'      => 'シャンパーニュ',
    'Champagne Rosé' => 'シャンパーニュ・ロゼ',
    'Blanc'          => '白ワイン',
    'Rosé'           => 'ロゼワイン',
    'Rouge'          => '赤ワイン',
    'Doux'           => '甘口ワイン',
    'Food'           => '食品'
);

$productCategoryHash = array(
    'Mousseux'       => '1373',
    'Mousseux Rosé'  => '1373',
    'Champagne'      => '1373',
    'Champagne Rosé' => '1373',
    'Blanc'          => '1372',
    'Rosé'           => '1374',
    'Rouge'          => '1371',
    'Doux'           => '1376',
    'Food'           => ''
);

$countryHash = array(
    'France'       => 'フランス',
    'South Africa' => '南アフリカ'
);

$frenchRegionHash = array(
    'Alsace'             => 'アルザス地方',
    'Vallée de la Loire' => 'ロワール河流域',
    'Bordeaux'           => 'ボルドー地方',
    'Sud-Ouest'          => '南西地方',
    'Bourgogne'          => 'ブルゴーニュ地方',
    'Jura'               => 'ジュラ地方',
    'Savoie'             => 'サヴォワ地方',
    'Vallée du Rhône'    => 'ローヌ河流域',
    'Languedoc'          => 'ラングドック地方',
    'Roussillon'         => 'ルーション地方',
    'Provence'           => 'プロヴァンス地方',
    'Corse'              => 'コルシカ島'
);


$itemFilePath = './wines.csv';
$stockFilePath = './stocks.csv';
if (file_exists($itemFilePath))
{
    unlink($itemFilePath);
}

if (file_exists($stockFilePath))
{
    unlink($stockFilePath);
}

$itemData[]  = $rgstrItemCsvHeader;
$stockData[] = $rgstrStockCsvHeader;

require_once('../../restaurant/common.php');

$result = mysqli_query($dbc, "CALL get_wines('', '', '')");
if ($result !== FALSE)
{
    while ($row = mysqli_fetch_assoc($result))
    {
        if ($row['apply'] != 'SO') 
        {
            $strHeadline = $row['catch_copy'];
            if (mb_strlen($strHeadline) > 30)
            {
                $strHeadline = '';
            }

            $strName = $row['vintage'] . ' ' . $row['combined_name_jpn'] . ' / ' . $row['producer_jpn'];
            if (mb_strlen($strName) > 70)
            {
                $strName = mb_substr($strName, 0, 70) . '...';
            }

            $strType        = $row['type'];
            $strCountry     = $row['country'];
            $strRegion      = $row['region'];
            $strExplanation =
                '国: '           . $strCountry . "\n" .
                '地方: '         . $strRegion  . "\n" .
                'ブドウ品種: '   . $row['cepage']   . "\n" .
                'インポーター: ' . $row['importer'] . "\n";

            $strCaption     = '';
            $strReleaseDate = '';
            $strPattern     = '/^[0-9]{1,2}\.[0-9]{1,2}$/';
            if (preg_match($strPattern, $row['etc']))
            {
                $rgint          = explode('.', $row['etc'], 2);
                $strReleaseDate = date('Y');
                $strReleaseDate .= ((intval($rgint[0]) < 10) ? ('0' . $rgint[0]) : $rgint[0]);
                $strReleaseDate .= ((intval($rgint[1]) < 10) ? ('0' . $rgint[1]) : $rgint[1]);
                $strCaption     = '<p style="color:red;font-size:14px;">' . ($row['etc'] . '入荷予定。入荷日の翌日以降の発送となります。' . '</p>');
            }

            $strPath = array_key_exists($strType, $categoryHash) ? $categoryHash[$strType] : 'その他のワイン';
            if (array_key_exists($strCountry, $countryHash))
            {
                $strPath .= "\n" . $countryHash[$strCountry];
            }

            if (($strCountry == 'France') && array_key_exists($strRegion, $frenchRegionHash))
            {
                $strPath .= "\n" . $countryHash[$strCountry] . ':' . $frenchRegionHash[$strRegion];
            }

            $strProductCategory = '';
            if (array_key_exists($strType, $productCategoryHash))
            {
                $strProductCategory = $productCategoryHash[$strType];
            }

            // Creates an array for item CSV.
            $tmpArray = array();
            $tmpArray[] = $strPath;               // path
            $tmpArray[] = $strName;               // name
            $tmpArray[] = $row['barcode_number']; // code
            $tmpArray[] = '';                     // subcode
            $tmpArray[] = $row['price'];          // original-price
            $tmpArray[] = $row['price'];          // price
            $tmpArray[] = '';                     // sale-price
            $tmpArray[] = '';                     // options
            $tmpArray[] = $strHeadline;           // headline
            $tmpArray[] = $strCaption;            // caption
            $tmpArray[] = '';                     // abstract
            $tmpArray[] = $strExplanation;        // explanation
            $tmpArray[] = '';                     // additional1
            $tmpArray[] = '';                     // additional2
            $tmpArray[] = '';                     // additional3
            $tmpArray[] = '';                     // relevant-links
            $tmpArray[] = '';                     // ship-weight
            $tmpArray[] = '1';                    // taxable
            $tmpArray[] = $strReleaseDate;        // release-date
            $tmpArray[] = '';                     // temporary-point-term
            $tmpArray[] = '';                     // point-code
            $tmpArray[] = '';                     // meta-desc
            $tmpArray[] = 'IT03';                 // template
            $tmpArray[] = '';                     // sale-period-start
            $tmpArray[] = '';                     // sale-period-end
            $tmpArray[] = '';                     // sale-limit
            $tmpArray[] = '';                     // sp-code
            $tmpArray[] = '';                     // brand-code
            $tmpArray[] = '';                     // yahoo-product-code
            $tmpArray[] = '';                     // product-code
            $tmpArray[] = '';                     // jan
            $tmpArray[] = '';                     // isbn
            $tmpArray[] = '0';                    // delivery
            $tmpArray[] = '0';                    // astk-code
            $tmpArray[] = '0';                    // condition
            $tmpArray[] = $strProductCategory;    // product-category
            $tmpArray[] = '';                     // spec1
            $tmpArray[] = '';                     // spec2
            $tmpArray[] = '';                     // spec3
            $tmpArray[] = '';                     // spec4
            $tmpArray[] = '';                     // spec5
            $tmpArray[] = '';                     // spec6
            $tmpArray[] = '';                     // spec7
            $tmpArray[] = '';                     // spec8
            $tmpArray[] = '';                     // spec9
            $tmpArray[] = '';                     // spec10
            $tmpArray[] = '1';                    // display
            $tmpArray[] = '99';                   // sort
            $tmpArray[] = '';                     // sp-additional
            $itemData[] = $tmpArray;

            // Creates an array for stock CSV.
            $tmpArray    = array();
            $tmpArray[]  = $row['barcode_number']; // code
            $tmpArray[]  = '';                     // sub-code
            $tmpArray[]  = $row['stock'];          // quantity
            $tmpArray[]  = '0';                    // allow-overdraft
            $stockData[] = $tmpArray;
        }
    }

    $fp = fopen($itemFilePath, 'w');
    fwrite($fp, arr2csv($itemData));
    fclose($fp);

    $fp = fopen($stockFilePath, 'w');
    fwrite($fp, arr2csv($stockData));
    fclose($fp);

    mysqli_free_result($result);
    include_once('./send_attachments.php');
}
else
{
    echo 'Failed to export CSVs :(';
}

?>
