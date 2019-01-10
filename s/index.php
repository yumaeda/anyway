<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    exit();
}

$userName = '';
if (isset($_SESSION['user_id']) &&
    isset($_SESSION['user_name']))
{
    $userName = $_SESSION['user_name'];
}

// Get tax rate from config file.
$config = include('../config.php');
$taxRate = $config['tax']['rate'];

echo '
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>ワインのAnyway-Grapes｜世田谷区 経堂</title>
        <link rel="stylesheet" href="//code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.css" />
        <style type="text/css">

        .ui-icon-cart:after
        {
            background-image: url("shopping-cart.png");
            background-color: transparent;
            background-size: contain;
        }

        .ui-icon-phone:after
        {
            background-image: url("phone.png");
            background-color: transparent;
            background-size: contain;
        }


        .wrapper { -webkit-overflow-scrolling: touch; overflow-y: scroll; }


        div#findByPrice li, div#findByType li, div#findByVintage li
        {
            padding: 5px;
        }

        .ui-listview>.ui-li-has-thumb>.ui-btn, .ui-listview>.ui-li-static.ui-li-has-thumb
        {
            min-height: 100px;
        }

        .ui-mobile a img
        {
            position: absolute;
            top: 0;
            bottom: 0;
            margin: auto;
        }

        .jpnSmallText
        {
            font-family: "KozGoPro-Light", "小塚ゴシック Pro L", "ヒラギノ角ゴ ProN W3", "Hiragino Kaku Gothic ProN", "游ゴシック", YuGothic, "メイリオ", Meiryo, sans-serif;
            font-size: 9px;
        }

        .jpnMediumText
        {
            font-family: "KozGoPro-Light", "小塚ゴシック Pro L", "ヒラギノ角ゴ ProN W3", "Hiragino Kaku Gothic ProN", "游ゴシック", YuGothic, "メイリオ", Meiryo, sans-serif;
            font-size: 11px;
        }

        .labelText
        {
            white-space: nowrap;
        }

        .priceText
        {
            text-align: right;
            color: darkred;
            font-size: 16px;
        }

        .originalPriceText
        {
            font-size: 12px;
            text-decoration: line-through;
        }

        #mainPanel
        {
            font-family: "KozGoPro-Light", "小塚ゴシック Pro L", "ヒラギノ角ゴ ProN W3", "Hiragino Kaku Gothic ProN", "游ゴシック", YuGothic, "メイリオ", Meiryo, sans-serif;
            padding: 15px;
        }

        span#dialogMessageSpan
        {
            color: green;
            font-size: 14px;
        }

        .controlgroup-textinput
        {
            padding-top: .22em;
            padding-bottom: .22em;
        }


        /* Make the panel contents scrollable */
        .ui-panel-inner
        {
            position: absolute;
            top: 1px;
            left: 0;
            right: 0;
            bottom: 0px;

            overflow: scroll;
            -webkit-overflow-scrolling: touch;
        }


        /* Wine Set Detail Dialog */

        .ui-icon-cart:after
        {
            background-image: url("shopping-cart.png");
            background-color: transparent;
            background-size: contain;
        }

        div#header img
        {
            width: 100%;
        }

        div#header, div#footer
        {
            text-align: center;
        }

        div#setDetailBtn
        {
            border: 1px solid rgb(224, 224, 224);

            padding: 15px;
            margin: 15px auto 0 auto;
        }

        div.commentPane
        {
            border: 1px solid rgb(224, 224, 224);
            height: 100px;
            overflow: auto;
            padding: 10px;
        }

        span.totalPriceText
        {
            text-decoration: line-through;
        }

        span.setPriceText
        {
            font-size: 17px;
            color: darkred;
        }

        img#addToCartImg
        {
            vertical-align: bottom;
            cursor: pointer;
        }

        img#addToCartImg:hover
        {
            opacity: 0.5;
        }

        </style>
    </head>
    <body>
        <div data-role="page" data-title="ワインのAnyway-Grapes｜世田谷区 経堂" data-dom-cache="true" id="mainPage">
            <div data-role="panel" id="qualityPanel" data-position="left" data-display="overlay" data-position-fixed="true">
                <div></div>
            </div> 
            <div data-role="panel" id="companyPanel" data-position="left" data-display="overlay" data-position-fixed="true">
                <div></div>
            </div> 
            <div data-role="panel" id="paymentPanel" data-position="left" data-display="overlay" data-position-fixed="true">
                <div></div>
            </div> 
            <div data-role="panel" id="shippingPanel" data-position="left" data-display="overlay" data-position-fixed="true">
                <div></div>
            </div> 
            <div data-role="panel" id="privacyPanel" data-position="left" data-display="overlay" data-position-fixed="true">
                <div></div>
            </div> 
            <div data-role="panel" id="cmTransactionPanel" data-position="left" data-display="overlay" data-position-fixed="true">
                <div></div>
            </div> 
            <div data-role="panel" id="businessSalePanel" data-position="left" data-display="overlay" data-position-fixed="true">
                <div></div>
            </div> 
            <div data-role="panel" id="mainPanel" data-position="left" data-display="overlay" data-position-fixed="true">
            </div> 
            <div data-role="header" data-position="fixed" style="text-align:center;" data-theme="b">
                <img border="0" src="http://anyway-grapes.jp/images/_logo.png" alt="Anyway-Grapes.JP" style="float:center;display:inline;width:150px;" />
                <div data-role="controlgroup" data-type="horizontal">
                    <input type="text" id="search-control-group" data-wrapper-class="controlgroup-textinput ui-btn">
                    <button id="searchBtn">検索</button>
                </div>
                <div>
                    <a href="https://anyway-grapes.jp/cart.php" class="ui-btn ui-btn-b ui-btn-icon-left ui-icon-cart" data-ajax="false">カート</a>';

    if (!empty($userName))
    {
        echo '&nbsp;&nbsp;' . $userName . '様&nbsp;&nbsp<a href="../logout.php" data-ajax="false">ログアウト</a>';
    }
    else
    {
        echo '&nbsp;&nbsp;<a href="../login.php" data-ajax="false">ログイン</a>';
    }

    echo '
                </div>
            </div>
            <div role="main" class="ui-content">
                <a href="http://anyway-grapes.jp/store/index.php?pc_view=1" class="ui-btn ui-mini ui-btn-b" data-ajax="false">表示をPC用に切り替える</a>
                <br />
                <img id="signupImg" src="../images/banners/membership.jpg" style="width:100%;" />
                <div data-role="controlgroup" id="findWineTab">
                    <span>ワインを探す</span>
                    <div data-role="collapsible" data-collapsed="false" id="findByType">
                        <h3>種類</h3>
                        <div data-role="controlgroup">
                            <a class="ui-btn" id="sparklingLnk" href="#" data-ajax="false">スパークリング・ワイン</a>
                            <a class="ui-btn" id="champagneLnk" href="#" data-ajax="false">シャンパーニュ</a>
                            <a class="ui-btn" id="whiteLnk"     href="#" data-ajax="false">白ワイン</a>
                            <a class="ui-btn" id="redLnk"       href="#" data-ajax="false">赤ワイン</a>
                            <a class="ui-btn" id="roseLnk"      href="#" data-ajax="false">ロゼワイン</a>
                        </div>
                    </div>
                    <div data-role="collapsible" data-collapsed="false" id="findByPrice">
                        <h3>価格</h3>
                        <div data-role="controlgroup">
                            <a class="ui-btn" id="oneThousandRangeLnk"   href="#" data-ajax="false">1000円台</a>
                            <a class="ui-btn" id="twoThousandRangeLnk"   href="#" data-ajax="false">2000円台</a>
                            <a class="ui-btn" id="threeThousandRangeLnk" href="#" data-ajax="false">3000円台</a>
                            <a class="ui-btn" id="fiveThousandRangeLnk"  href="#" data-ajax="false">5000円台</a>
                            <a class="ui-btn" id="moreThan10000YenLnk"   href="#" data-ajax="false">10000円以上</a>
                        </div>
                    </div>
                    <div data-role="collapsible" data-collapsed="false" id="findByVintage">
                        <h3>年代</h3>
                        <div data-role="controlgroup">
                            <a class="ui-btn" id="sixtiesLnk"   href="#" data-ajax="false">1960年代</a>
                            <a class="ui-btn" id="seventiesLnk" href="#" data-ajax="false">1970年代</a>
                            <a class="ui-btn" id="eightiesLnk"  href="#" data-ajax="false">1980年代</a>
                            <a class="ui-btn" id="nintiesLnk"   href="#" data-ajax="false">1990年代</a>
                            <a class="ui-btn" id="doubleOsLnk"  href="#" data-ajax="false">2000年代</a>
                        </div>
                    </div>
                    <div data-role="collapsible" data-collapsed="false" id="findByCountry">
                        <h3>国</h3>
                        <div data-role="controlgroup">
                            <div data-role="collapsible" data-collapsed="false">
                                <ul data-role="listview" data-split-icon="info" data-split-theme="d">
                                     <li>
                                         <a href="#" id="franceLnk" data-ajax="false">フランス</a><a href="http://anyway-grapes.jp/producers/france/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="italyLnk" data-ajax="false">イタリア</a><a href="http://anyway-grapes.jp/producers/italy/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="germanyLnk" data-ajax="false">ドイツ</a><a href="http://anyway-grapes.jp/producers/germany/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="austriaLnk" data-ajax="false">オーストリア</a><a href="http://anyway-grapes.jp/producers/austria/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="spainLnk" data-ajax="false">スペイン</a><a href="http://anyway-grapes.jp/producers/spain/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="portugalLnk" data-ajax="false">ポルトガル</a><a href="http://anyway-grapes.jp/producers/portugal/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="americaLnk" data-ajax="false">アメリカ合衆国</a><a href="http://anyway-grapes.jp/producers/united-states/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="southAfricaLnk" data-ajax="false">南アフリカ</a><a href="http://anyway-grapes.jp/producers/south-africa/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="australiaLnk" data-ajax="false">オーストラリア</a><a href="http://anyway-grapes.jp/producers/australia/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="newZealandLnk" data-ajax="false">ニュージーランド</a><a href="http://anyway-grapes.jp/producers/new-zealand/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="croatiaLnk" data-ajax="false">クロアチア</a><a href="http://anyway-grapes.jp/producers/croatia/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="moldovaLnk" data-ajax="false">モルドバ</a><a href="http://anyway-grapes.jp/producers/moldova/" data-ajax="false"></a>
                                     </li>
                                 </ul>
                             </div>
                        </div>
                    </div>
                    <div data-role="collapsible" data-collapsed="true" id="findByRegion">
                    <h3>フランスの地方</h3>
                        <div data-role="controlgroup">
                            <div data-role="collapsible" data-collapsed="false">
                                <ul data-role="listview" data-split-icon="info" data-split-theme="d">
                                     <li>
                                         <a href="#" id="champagneLnk" data-ajax="false">シャンパーニュ地方</a><a href="http://anyway-grapes.jp/producers/france/champagne/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="alsaceLnk" data-ajax="false">アルザス地方</a><a href="http://anyway-grapes.jp/producers/france/alsace/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="loireLnk" data-ajax="false">ロワール河流域</a><a href="http://anyway-grapes.jp/producers/france/vallee-de-la-loire/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="bordeauxLnk" data-ajax="false">ボルドー地方</a><a href="http://anyway-grapes.jp/producers/france/bordeaux/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="southWestLnk" data-ajax="false">南西地方</a><a href="http://anyway-grapes.jp/producers/france/sud-ouest/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="bourgogneLnk" data-ajax="false">ブルゴーニュ地方</a><a href="http://anyway-grapes.jp/producers/france/bourgogne/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="juraLnk" data-ajax="false">ジュラ地方</a><a href="http://anyway-grapes.jp/producers/france/jura/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="savoieLnk" data-ajax="false">サヴォワ地方</a><a href="http://anyway-grapes.jp/producers/france/savoie/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="rhoneLnk" data-ajax="false">ローヌ河流域</a><a href="http://anyway-grapes.jp/producers/france/vallee-du-rhone/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="languedocLnk" data-ajax="false">ラングドック地方</a><a href="http://anyway-grapes.jp/producers/france/languedoc-et-roussillon/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="roussillonLnk" data-ajax="false">ルーション地方</a><a href="http://anyway-grapes.jp/producers/france/languedoc-et-roussillon/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="provenceLnk" data-ajax="false">プロヴァンス地方</a><a href="http://anyway-grapes.jp/producers/france/provence/" data-ajax="false"></a>
                                     </li>
                                     <li>
                                         <a href="#" id="corseLnk" data-ajax="false">コルシカ島</a><a href="http://anyway-grapes.jp/producers/france/corse/" data-ajax="false"></a>
                                     </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <br />
                <div data-role="controlgroup">
                    <span>カテゴリー</span>
<!--
                    <a id="wineSetPageLnk"         href="#" class="ui-btn ui-btn-icon-right ui-icon-carat-r">送料無料セット</a>
-->
                    <a id="rankingPageLnk"         href="#" class="ui-btn ui-btn-icon-right ui-icon-carat-r">ランキング</a>
                </div>
                <br />
                <div data-role="controlgroup">
                    <span>アクセス</span>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3242.033145362007!2d139.634685!3d35.65155499999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018f3be02000bab%3A0xa52fa7406d77ee45!2sConceptual+Wine+Boutique+Anyway-Grapes!5e0!3m2!1sja!2sjp!4v1410321668722" width="100%" scrolling="no" frameborder="0">
                    </iframe>
                </div>
                <div style="text-align:center;">
                    <a href="tel:03-6413-9737" class="ui-btn ui-mini ui-btn-b ui-btn-inline ui-btn-icon-left ui-icon-phone">03-6413-9737</a>
                    <a href="mailto:mail@anyway-grapes.jp" class="ui-btn ui-mini ui-btn-b ui-btn-inline">mail@anyway-grapes.jp</a><br />
                    12:00〜24:00（定休日：火曜）
                </div>
                <br />
                <div id="footerLinkPane" data-role="controlgroup">
                    <a id="qualityLnk"       href="#qualityPanel"       data-role="button" class="ui-btn ui-mini" data-inline="true">品質の保証</a>
                    <a id="companyLnk"       href="#companyPanel"       data-role="button" class="ui-btn ui-mini" data-inline="true">会社概要</a>
                    <a id="paymentLnk"       href="#paymentPanel"       data-role="button" class="ui-btn ui-mini" data-inline="true">支払い方法</a>
                    <a id="shippingLnk"      href="#shippingPanel"      data-role="button" class="ui-btn ui-mini" data-inline="true">送料</a>
                    <a id="privacyLnk"       href="#privacyPanel"       data-role="button" class="ui-btn ui-mini" data-inline="true">個人情報</a>
                    <a id="cmTransactionLnk" href="#cmTransactionPanel" data-role="button" class="ui-btn ui-mini" data-inline="true">特定商取引法</a>
                    <a id="businessSaleLnk"  href="#businessSalePanel"  data-role="button" class="ui-btn ui-mini" data-inline="true">業務販売</a>
                </div>
                <div style="text-align:center;">
                    <span style="font-size:11px;color:rgb(80,80,80);">&copy; 2015 Conceptual Wine Boutique Anyway-Grapes</span>
                </div>
            </div>
        </div>
        <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
        <script type="text/javascript">

        $(document).on("mobileinit", function()
        {
            // Workaround fix for Chrome43.
            $.fn.animationComplete = function(callback)
            {
                if ($.support.cssTransitions)
                {
                    var superfy = "WebKitTransitionEvent" in window ? "webkitAnimationEnd" : "animationend";
                    return $(this).one(superfy, callback);
                }
                else
                {
                    setTimeout(callback, 0);
                    return $(this);
                }
            };

            // Configure loader widget.
            $.mobile.loader.prototype.options.textonly    = false;
            $.mobile.loader.prototype.options.textVisible = true;
            $.mobile.loader.prototype.options.theme       = "a";
            $.mobile.loader.prototype.options.html        = "<span><img src=\"https://anyway-grapes.jp/producers/logo.png\" width=\"200\" /><h3>ワインの情報を読み込み中...。</h3></span>";
        });

        </script>
    </body>
</html>
';

?>

<script type="text/javascript">

rgobjSearchedWine = [];
iSearchedWine     = 0;

// Extracted from seiya.constants.js
var Constants =
{
    'DOT_SEPARATOR':    '・',

    'ja': {
        'TROCKEN_STR': 'トロッケン',
    },

    'fr': {
        'BORDEAUX_STR':  'Bordeaux',
        'CHAMPAGNE_STR': 'Champagne',
        'BOURGOGNE_STR': 'Bourgogne'
    }
};

// Copied from seiya.wineutility-0.1.js
var _appendToken = function(str, strToken, strDelimiter)
    {
        var strResult = str;

        if (strToken)
        {
            if (strResult && (strResult.length > 0))
            {
                strResult += strDelimiter;
            }

            strResult += strToken;
        }

        return strResult;
    };

// Copied from seiya.utility-0.1.js
function formatNumber(intNumber)
{
    // convert int to string.
    intNumber += '';

    var rgstrToken = intNumber.split('.');
    var intToken = rgstrToken[0];
    var decimalToken = (rgstrToken.length > 1) ? '.' + rgstrToken[1] : '';
    var rgx = /(\d+)(\d{3})/;

    while (rgx.test(intToken))
    {
        intToken = intToken.replace(rgx, '$1' + ',' + '$2');
    }

    return (intToken + decimalToken);
}

// Copied from seiya.constants.js
var wineTypeHash =
{
    'Mousseux':       { css: 'sparkling',     value: 0,  name: 'スパークリング・ワイン'     },
    'Mousseux Rouge': { css: 'sparkling',     value: 0,  name: 'スパークリング・赤ワイン'   },
    'Mousseux Rosé':  { css: 'sparkling',     value: 0,  name: 'スパークリング・ロゼワイン' },
    'Blanc':          { css: 'white',         value: 2,  name: '白ワイン'                   },
    'Orange':         { css: 'white',         value: 2,  name: 'オレンジワイン'             },
    'Gris':           { css: 'white',         value: 2,  name: 'グリ'                       },
    'Sherry':         { css: 'yellow',        value: 3,  name: 'シェリー'                   },
    'Jaune':          { css: 'yellow',        value: 3,  name: 'ヴァン・ジョーヌ'           },
    'Madeira':        { css: 'yellow',        value: 3,  name: 'マデイラ'                   },
    'Marsala':        { css: 'yellow',        value: 3,  name: 'マルサラ'                   },
    'Rouge':          { css: 'red',           value: 4,  name: '赤ワイン'                   },
    'Port':           { css: 'sweet',         value: 5,  name: 'ポートワイン'               },
    'Doux':           { css: 'sweet',         value: 5,  name: 'デザートワイン'             },
    'Rosé':           { css: 'rose',          value: 6,  name: 'ロゼワイン'                 },
    'Eau de Vie':     { css: 'brandy',        value: 7,  name: 'オー・ド・ヴィ'             },
    'Champagne':      { css: 'champagne',     value: 8,  name: 'シャンパーニュ'             },
    'Champagne Rosé': { css: 'champagneRose', value: 9,  name: 'シャンパーニュ・ロゼ'       },
    'Liqueur':        { css: 'liqueur',       value: 10, name: 'リキュール'                 },
    'Goods':          { css: 'goods',         value: 11, name: 'グッズ'                     },
    'Food':           { css: 'food',          value: 12, name: 'グロッサリー'               },
    'Beer':           { css: 'food',          value: 13, name: 'ビール'                     },
    'Cider':          { css: 'food',          value: 14, name: 'シードル'                   }
};

// Copied from seiya.country.js
var countryHash =
{
    'France':         { name: 'フランス',         value: 1, img: 'france.png' },
    'Austria':        { name: 'オーストリア',     value: 2, img: 'austria.png' },
    'Germany':        { name: 'ドイツ',           value: 3, img: 'germany.png' },
    'New Zealand':    { name: 'ニュージーランド', value: 4, img: 'new_zealand.png' },
    'Italy':          { name: 'イタリア',         value: 5, img: 'italy.png' },
    'United States':  { name: 'アメリカ合衆国',   value: 6, img: 'america.png' },
    'Spain':          { name: 'スペイン',         value: 7, img: 'spain.png' },
    'Japan':          { name: '日本',             value: 8, img: 'japan.png' },
    'Australia':      { name: 'オーストラリア',   value: 9, img: 'australia.png' },
    'South Africa':   { name: '南アフリカ',       value: 10, img: 'south_africa.png' },
    'United Kingdom': { name: 'イギリス',         value: 11, img: 'united_kingdom.png' },
    'Croatia':        { name: 'クロアチア',       value: 12, img: 'croatia.png' },
    'Portugal':       { name: 'ポルトガル',       value: 13, img: 'portugal.png' },
    'Canada':         { name: 'カナダ',           value: 14, img: 'canada.png' },
    'Ukraine':        { name: 'ウクライナ',       value: 15, img: 'ukraine.png' },
    'Argentina':      { name: 'アルゼンチン',     value: 16, img: 'argentina.png' },
    'Switzerland':    { name: 'スイス',           value: 17, img: 'switzerland.png' },
    'Bulgaria':       { name: 'ブルガリア',       value: 18, img: 'bulgaria.png' },
    'Hungary':        { name: 'ハンガリー',       value: 19, img: 'hungary.png' },
    'Lebanon':        { name: 'レバノン',         value: 20, img: 'lebanon.png' },
    'Chile':          { name: 'チリ',             value: 21, img: 'chile.png' },
    'Taiwan':         { name: '台湾',             value: 22, img: 'taiwan.png' },
    'Moldova':        { name: 'モルドヴァ',       value: 23, img: 'moldova.png' },
    'Rumania':        { name: 'ルーマニア',       value: 24, img: 'rumania.png' },
    'Greece':         { name: 'ギリシャ',         value: 25, img: 'greece.png' }
};

// Copied from seiya.wineutility-0.1.js
function isComingSoonItem(objWine)
{
    var fComingSoon = false,
        strEtc      = objWine.etc;   

    if (strEtc && strEtc.length > 0)
    {
         var rgstrToken = strEtc.split('.');
         if (rgstrToken.length == 2)
         {
             var intMonth    = parseInt(rgstrToken[0], 10),
                 intDate     = parseInt(rgstrToken[1], 10),
                 fComingSoon = ((intMonth != 'NaN') && (intDate != 'NaN'));
         }
    }

    return ((objWine.stock > 0) && (objWine.availability == 'Online') && fComingSoon);
}


function isAuthenticated()
{
    var fAuthenticated = false;

    $.ajax(
    {
        url:  '//anyway-grapes.jp/is_authenticated.php',
        async: false,
        type: 'GET',
        data: {},

        success: function(strResponse)
        {
            if (strResponse == 'TRUE')
            {
                fAuthenticated = true;
            }
        },

        error: function() {}
    });

    return fAuthenticated;
}

function generateImgHtml(strBarcode, intHeight)
{
    var imgFolderUrl = '//anyway-grapes.jp/images/wines/100px/',
        imgUrl       = imgFolderUrl + strBarcode + '.png',
        noImgUrl     = imgFolderUrl + 'no_wine_photo.png';

    return '<img style="max-height:' + intHeight + 'px;" src="' + imgUrl + '" onerror="this.src=\'' + noImgUrl + '\';" />';
}

function getMemberPrice(objWine)
{
    return (objWine.member_price > 0) ? objWine.member_price : objWine.price;
}

function generatePriceHtml(objWine, fShowMemberPrice)
{
    var price           = objWine.price;
        priceHtml       = '',
        taxExcludedHtml = '<span style="font-size:8px;">（税抜）</span>';

    if (fShowMemberPrice)
    {
        priceHtml =
            '<span style="font-size:10px;">通常価格：￥' + formatNumber(price) + taxExcludedHtml + '</span><br />' +
            '<span style="color:red;font-size:14px;">会員価格：￥' + formatNumber(getMemberPrice(objWine)) + taxExcludedHtml + '</span>';
    }
    else
    {
        priceHtml =
            '<span style="font-size:14px;">通常価格：￥' + formatNumber(price) + taxExcludedHtml + '</span><br />' +
            '<span style="color:red;font-size:10px;">会員価格：￥' + formatNumber(getMemberPrice(objWine)) + taxExcludedHtml + '</span>';
    }

    return priceHtml;
}

function renderWineDetailDialog(barcodeNumber, $targetDialog)
{
    $.ajax(
    {
        url: '//anyway-grapes.jp/laravel5.3/public/api/v1/wines/' + barcodeNumber,
        dataType: 'json',
        success: function(data)
        {
            var rgobjWine = data.wines;
            if (rgobjWine && rgobjWine.length == 1)
            {
                var objWine    = rgobjWine[0],
                    strComment = (objWine.original_comment != null) ? objWine.original_comment : '',
                    html       =
                    '<div data-role="header" data-theme="b" style="text-align:center;">' +
                        '<h3>' + objWine.combined_name_jpn + '</h3>' +
                    '</div>' +
                    '<div role="main" class="ui-content">' +
                        '<table>' +
                            '<tr>' +
                                '<td colspan="2" style="text-align:center;">' +
                                    generateImgHtml(barcodeNumber, 400) +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td colspan="2" class="jpnMediumText">' +
                                    strComment +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td colspan="2">&nbsp;</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td class="jpnMediumText labelText">種類:&nbsp;</td>' +
                                '<td class="jpnSmallText">' +
                                    (wineTypeHash[objWine.type] ? wineTypeHash[objWine.type].name : '') +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td class="jpnMediumText labelText">生産国:&nbsp;</td>' +
                                '<td class="jpnSmallText">' +
                                    countryHash[objWine.country].name +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td class="jpnMediumText labelText">生産者:&nbsp;</td>' +
                                '<td class="jpnSmallText">' +
                                    objWine.producer_jpn +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td class="jpnMediumText labelText">地域:&nbsp;</td>' +
                                '<td class="jpnSmallText">' +
                                    objWine.region_jpn +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td class="jpnMediumText labelText">品種:&nbsp;</td>' +
                                '<td class="jpnSmallText">' +
                                    objWine.cepage +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td class="jpnMediumText labelText">生産年:&nbsp;</td>' +
                                '<td class="jpnSmallText">' +
                                    objWine.vintage +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td class="jpnMediumText labelText">輸入元:&nbsp;</td>' +
                                '<td class="jpnSmallText">' +
                                    objWine.importer +
                                '</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td colspan="2">&nbsp;</td>' +
                            '</tr>' +
                            '<tr>' +
                                '<td colspan="2" style="text-align:center;">' +
                                    generatePriceHtml(objWine, isAuthenticated()) +
                                '</td>' +
                            '</tr>' +
                        '</table>';

                if ((objWine.stock > 0) && (objWine.availability == 'Online'))
                {
                    html +=
                        '<div data-role="controlgroup" style="text-align:center;">' +
                            '<input type="number" min="1" max="' + objWine.stock + '" value="1" />' +
                            '<img style="vertical-align:bottom;" id="addToCartImg" src="../../campaign/add_to_cart.png" title="カートに追加する。" />' +
                            '<br /><br /><span id="dialogMessageSpan"></span>';

                    if (isComingSoonItem(objWine))
                    {
                        html += '<br /><span>' + objWine.etc + '入荷予定。発送は入荷日の翌日以降になります。</span>';
                    }

                    html +=
                            '</div>';
                }

                html += '</div>';

                $targetDialog.html(html);
                $targetDialog.on('click', 'img#addToCartImg', function()
                {
                    var intQty = $(this).siblings('input').val();
                    onAddToCart(barcodeNumber, intQty);
                });
            }
        },

        error: function(){}
    });
}

function onAddToCart(barcode, intQty)
{
    $('span#dialogMessageSpan').html('');

    $.ajax(
    {
        url:  '../cart.php',
        type: 'POST',
        data:
        {
            action: 'add',
            pid:    barcode,
            qty:    intQty
        },

        success: function(strResponse)
        {
            $('span#dialogMessageSpan').html('カートに' + intQty + '本追加されました');
        },

        error: function()
        {
            console.error(barcode + ' cannot be added to the cart.');
        }
    });
}

function generateWineSetHeaderHtml(objWineSet)
{
    var html =
        '<div data-role="controlgroup">' +
           '<img src="../images/wine_sets/' + objWineSet.id + '.png" onerror="this.src=\'../images/wine_sets/no_wine_set_image.png\';" />' +
           '<div class="commentPane"><p>' + objWineSet.comment + '</p></div>' +
        '</div>';

    return html;
}

function generateWineSetFooterHtml(objWineSet)
{
    var html          = '',
        priceHtml     = '',
        intPrice      = objWineSet.price,
        discountPrice = objWineSet.set_price,
        intStockMax   = objWineSet.stock;

    if (intStockMax > 0)
    {

        if (intPrice >= discountPrice)
        {
            priceHtml +=
                '<span class="setPriceText">￥' + formatNumber(discountPrice) + '</span><span style="font-size:8px;">（税別）</span>';
        }
        else
        {
            priceHtml +=
                '<span class="priceText">￥' + formatNumber(intPrice) + '</span><span style="font-size:8px;">（税別）</span>';
        }

        html =
            'セット特価:&nbsp;&nbsp;' +
            priceHtml +
            '<br />' +
            '個数:&nbsp;&nbsp;' +
            '<input type="number" min="1" max="' + intStockMax + '" value="1" />' +
            '<img style="vertical-align:bottom;" id="addToCartImg" src="../../campaign/add_to_cart.png" title="カートに追加する。" />';
    }
    else
    {
        html = '<span class="soldOutText">完売しました。</span>';
    }

    html +=
        '<br />' +
        '<a href="http://anyway-grapes.jp/store/index.php?pc_view=1&submenu=wine_set&id=' + objWineSet.id + '" data-ajax="false">' +
            '<div id="setDetailBtn">PC版のセットの詳細ページを見る。</div>' +
        '</a>';

    return html;
}

function renderWineSetPage()
{
    $.mobile.loading('show', {});

    $.ajax(
    { 
        url: '//anyway-grapes.jp/laravel5.3/public/api/v1/wine-sets',
        type: 'GET',
        dataType: 'json', 
        data: { type: 0 },

        success: function(data)
        { 
            var objWineSet   = null,
                rgobjWineSet = data.wines,
                cWineSet     = rgobjWineSet.length,
                html         =
                '<div id="wineSetPage" data-role="page">' +
                    '<div data-role="panel" id="wineSetDetailDialog" data-position="right" data-display="overlay" data-position-fixed="true">' +
                        '<div></div>' +
                    '</div>' +
                    '<div data-role="header" data-theme="b" data-add-back-btn="true" data-position="fixed" data-back-btn-text="戻る" data-back-btn-theme="b">' +
                        '<a href="https://anyway-grapes.jp/cart.php" class="ui-btn ui-btn-b ui-btn-right ui-btn-icon-left ui-icon-cart" data-ajax="false">カート</a>' +
                        '<h1>送料無料セット</h1>' + 
                    '</div>' +
                    '<div data-role="content" class="ui-content">' +
                        '<ul id="wineSetList" data-role="listview">';

            var setPrice = 0;

            for (var i = 0; i < cWineSet; ++i)
            {
                objWineSet = rgobjWineSet[i];
                setPrice   = parseInt(objWineSet.set_price, 10);

                html +=
                    '<li class="ui-li-has-thumb">' +
                        '<a id="' + objWineSet.id + '" href="#wineSetDetailDialog" data-role="button" data-inline="true">' +
                            '<img src="../images/wine_sets/' + objWineSet.id + '.png" style="height:60px;" />' +
                            '<p class="jpnMediumText">' + objWineSet.name + '</p>' +
                            '<span class="priceText">￥' + formatNumber(setPrice) + '</span><span style="font-size:8px;">（税別）</span>' +
                        '</a>' +
                    '</li>';
            }

            html += '</ul></div></div>';

            $('body').append(html);
            $("ul#wineSetList").listview();

            // Dynamically renders the contents of the wine set detail dialog upon link click.
            $('ul#wineSetList').on('click', 'a', function(e)
            {
                var setId = this.id;

                $.ajax(
                {
                    url: '//anyway-grapes.jp/laravel5.3/public/api/v1/wine-sets/' + setId,
                    dataType: 'json',
                    success: function(data)
                    {
                        var rgobjWineSet = data.wines;
                        if (rgobjWineSet && rgobjWineSet.length == 1)
                        {
                            var objWineSet = rgobjWineSet[0];

                            var html  = 
                                '<div data-role="header" data-theme="b" style="text-align:center;">' +
                                    '<h3>' + objWineSet.name + '</h3>' +
                                '</div>' +
                                '<div role="main" class="ui-content">' +
                                    '<div id="header">' +
                                        generateWineSetHeaderHtml(objWineSet) +
                                    '</div>' +
                                    '<div id="' + setId + '" class="contents">' +
                                    '</div>' +
                                    '<div id="footer">' +
                                        generateWineSetFooterHtml(objWineSet) +
                                    '</div>' +
                                '</div>';

                            $('div#wineSetDetailDialog').html(html);
                            $('div#wineSetDetailDialog').on('click', 'img#addToCartImg', function()
                            {
                                var intQty  = $(this).siblings('input').val(),
                                    barcode = parseInt(setId, 10) + 50000;

                                onAddToCart(barcode, intQty);
                            });
                        }
                    },

                    error: function() {}
                });
            });

            // Hide the loading message and load the sale page.
            $.mobile.loading('hide', {});
            $.mobile.changePage('#wineSetPage', { transition: 'flip' });
        },

        error: function() {}
    });
}

function renderRankingPage()
{
    $.mobile.loading('show', {});

    $.ajax(
    {
        url: '//anyway-grapes.jp/laravel5.3/public/api/v1/ranking-wines',
        dataType: 'json',
        success: function(data)
        {
            var rgobjWine = data.wines,
                html      =
                '<div id="rankingPage" data-role="page">' +
                    '<div data-role="panel" id="rankingWineDetailDialog" data-position="right" data-display="overlay" data-position-fixed="true">' +
                        '<div></div>' +
                    '</div>' +
                    '<div data-role="header" data-theme="b" data-add-back-btn="true" data-position="fixed" data-back-btn-text="戻る" data-back-btn-theme="b">' +
                        '<a href="https://anyway-grapes.jp/cart.php" class="ui-btn ui-btn-b ui-btn-right ui-btn-icon-left ui-icon-cart" data-ajax="false">カート</a>' +
                        '<h1>ランキング</h1>' +
                    '</div>' +
                    '<div data-role="content" class="ui-content">' +
                        '<ul id="rankingList" data-role="listview">';

            var cWine   = rgobjWine.length,
                objWine = null;

            for (var i = 0; (i < cWine) && (i < 10); ++i)
            {
                objWine = rgobjWine[i];
                html +=
                   '<li class="ui-li-has-thumb">' +
                       '<a id="' + objWine.barcode_number + '" href="#rankingWineDetailDialog" data-role="button" data-inline="true">' +
                            generateImgHtml(objWine.barcode_number, 95) +
                            '👑 ' + (i + 1) + '<br />' +
                            '<p>' +
                                '<span class="jpnMediumText" style="white-space: normal;">' + objWine.combined_name_jpn + '</span>' +
                            '</p>' +
                            '<p class="ui-li-aside">￥' + formatNumber(objWine.price) + '(税別)</p>' +
                        '</a>' +
                    '</li>';
            }

            html += '</ul></div></div>';

            $('body').append(html);
            $("ul#rankingList").listview();
            $('ul#rankingList').on('click', 'a', function(e)
            {
                renderWineDetailDialog(this.id, $('div#rankingWineDetailDialog > div'));
            });

            // Hide the loading message and load the sale page.
            $.mobile.loading('hide', {});
            $.mobile.changePage('#rankingPage', { transition: 'flip' });
        },

        error: function() {}
    });
}

function generateListItemHtml(objWine, fAuthenticated)
{
    var html       = '',
        strCountry = countryHash[objWine.country].name ? countryHash[objWine.country].name : '',
        strType    = wineTypeHash[objWine.type] ? wineTypeHash[objWine.type].name : '',
        strVintage = (objWine.vintage != 'S.A.') ? objWine.vintage : '';

    html +=
        '<li class="ui-li-has-thumb">' +
            '<a id="' + objWine.barcode_number + '" href="#searchResultDialog" data-role="button" data-inline="true">' +
                generateImgHtml(objWine.barcode_number, 100) +
                '<span class="ui-li-aside jpnSmallText">[' + strCountry + '&nbsp;/&nbsp;' + strType + ']</span><br />' +
                '<span class="jpnMediumText">' + objWine.vintage + '年</span>' +
                '<p style="white-space:normal">' + objWine.combined_name_jpn + '</p>' +
                generatePriceHtml(objWine, fAuthenticated) +
            '</a>' +
        '</li>';

    return html;
}

function renderWineRequestResult(strUrl, objData, pageTitle)
{
    var fAuthenticated = isAuthenticated();

    $.mobile.loading('show', {});

    $.ajax(
    {
        url:      strUrl,
        data:     objData,
        dataType: 'json',
        success: function(rgobjWine)
        {
            var html =
                '<div id="searchResult" data-role="page">' +
                    '<div data-role="header" data-theme="b" data-add-back-btn="true" data-position="fixed" data-back-btn-text="戻る" data-back-btn-theme="b">' +
                        '<a href="https://anyway-grapes.jp/cart.php" class="ui-btn ui-btn-b ui-btn-right ui-btn-icon-left ui-icon-cart" data-ajax="false">カート</a>' +
                        '<h1>' + pageTitle + '</h1>' +
                    '</div>' +
                    '<div role="main" class="ui-content">' +
                        '<ul data-role="listview" data-inset="true">';

            var cMaxItemToShow = 25,
                cWine          = rgobjWine.length;

            for (var i = 0; (i < cMaxItemToShow) && (i < cWine); ++i)
            {
                html += generateListItemHtml(rgobjWine[i], fAuthenticated);
            }

            if (cWine > cMaxItemToShow)
            {
                rgobjSearchedWine = rgobjWine;
                iSearchedWine     = i;
                html += '<li class="showMoreListItem"><a href="#">もっと見る</a></li>';
            }

            html += '</ul></div></div>';

            $searchResultPage = $('div#searchResult');
            if ($searchResultPage.length > 0)
            {
                $searchResultPage.remove();
            }

            $('body').append(html);

            // Register event handler for the "Show More" button.
            $('div#searchResult').on('click', 'li.showMoreListItem > a', function()
            {
                $ul = $(this).closest('ul');
                $(this).closest('li').remove();

                var j           = iSearchedWine,
                    ulInnerHtml = '';

                while ((j < (iSearchedWine + cMaxItemToShow)) && (j < cWine))
                {
                    ulInnerHtml += generateListItemHtml(rgobjSearchedWine[j], fAuthenticated);
                    ++j;
                }

                if (j < cWine)
                {
                    iSearchedWine = j;
                    ulInnerHtml += '<li class="showMoreListItem"><a href="#">もっと見る</a></li>';
                }

                $ul.append(ulInnerHtml);
                $ul.listview('refresh');
            });

            $('div#searchResult div.ui-content ul').listview();
            $.mobile.loading('hide', {});
            $.mobile.changePage('#searchResult', { transition: 'flip' });
        },

        error: function(){}
   });
}


function generateWineItemListHtml(rgobjWine, filterBy, filterValue)
{
    var cMaxItemToShow = 25,
        cWineToRender  = 0,
        cWine          = rgobjWine.length;
        objWine        = null,
        actualValue    = '',
        html           = '<ul data-role="listview" data-inset="true" id="searchResultList"">',
        iWine          = -1,
        fAuthenticated = isAuthenticated();

    for (iWine = 0; (cWineToRender < cMaxItemToShow) && (iWine < cWine); ++iWine)
    {
        objWine     = rgobjWine[iWine];
        actualValue = (filterBy === 'type') ? objWine.type : objWine.country;

        if ((filterValue == 'All') || (filterValue == actualValue))
        {
            html += generateListItemHtml(objWine, fAuthenticated);
            ++cWineToRender;
        }
    }

    if (iWine < cWine)
    {
        rgobjSearchedWine = rgobjWine;
        iSearchedWine     = iWine;
        html += '<li class="showMoreListItem"><a href="#">もっと見る</a></li>';
    }

    return (html + '</ul>');
}

function generateCountryRadioButtonsHtml()
{
    var html = 
        '<fieldset data-role="controlgroup" data-theme="b" data-type="horizontal" style="text-align:center;" id="countrySelectRadio">' +
            '<div style="width:300px;margin:0 auto 0 auto;">' +
                '<input type="radio" name="countrySelectRadio" id="allCountryRadio" value="All" checked="checked">' +
                '<label for="allCountryRadio">全て</label>' +
                '<input type="radio" name="countrySelectRadio" id="franceRadio" value="France">' +
                '<label for="franceRadio">フランス</label>' +
                '<input type="radio" name="countrySelectRadio" id="italyRadio" value="Italy">' +
                '<label for="italyRadio">イタリア</label>' +
                '<input type="radio" name="countrySelectRadio" id="germanyRadio" value="Germany">' +
                '<label for="germanyRadio">ドイツ</label>' +
                '<input type="radio" name="countrySelectRadio" id="austriaRadio" value="Austria">' +
                '<label for="austriaRadio">オーストリア</label>' +
                '<input type="radio" name="countrySelectRadio" id="spainRadio" value="Spain">' +
                '<label for="spainRadio">スペイン</label>' +
                '<input type="radio" name="countrySelectRadio" id="portugalRadio" value="Portugal">' +
                '<label for="portugalRadio">ポルトガル</label>' +
                '<input type="radio" name="countrySelectRadio" id="americaRadio" value="United States">' +
                '<label for="americaRadio">アメリカ合衆国</label>' +
                '<input type="radio" name="countrySelectRadio" id="southAfricaRadio" value="South Africa">' +
                '<label for="southAfricaRadio">南アフリカ</label>' +
                '<input type="radio" name="countrySelectRadio" id="newZealandRadio" value="New Zealand">' +
                '<label for="newZealandRadio">ニュージーランド</label>' +
            '</div>' +
        '</fieldset>';

    return html;
}

function generateTypeRadioButtonsHtml()
{
    var html = 
        '<fieldset data-role="controlgroup" data-theme="b" data-type="horizontal" style="text-align:center;" id="typeSelectRadio">' +
            '<div style="width:225px;margin:0 auto 0 auto;">' +
                '<input type="radio" name="typeSelectRadio" id="allTypeRadio" value="All" checked="checked">' +
                '<label for="allTypeRadio">全て</label>' +
                '<input type="radio" name="typeSelectRadio" id="sparklingRadio" value="Mousseux">' +
                '<label for="sparklingRadio">泡</label>' +
                '<input type="radio" name="typeSelectRadio" id="whiteRadio" value="Blanc">' +
                '<label for="whiteRadio">白</label>' +
                '<input type="radio" name="typeSelectRadio" id="roseRadio" value="Rosé">' +
                '<label for="roseRadio">ロゼ</label>' +
                '<input type="radio" name="typeSelectRadio" id="redRadio" value="Rouge">' +
                '<label for="redRadio">赤</label>' +
            '</div>' +
        '</fieldset>';

    return html;
}

function renderSearchResult(pageTitle, strCondition, strOrderBy, filterBy)
{
    var radioButtonsHtml = (filterBy === 'country') ?
        generateCountryRadioButtonsHtml() :
        generateTypeRadioButtonsHtml();

    $.mobile.loading('show', {});

    $.ajax(
    {
        url: '../wines/get_items.php',
        data:
        {
            dbTable: 'wines',
            condition: '(availability = "Online" AND apply <> "DP" AND stock > 0 AND price > 0 AND type <> "Food" AND type <> "Goods") AND (' + strCondition + ')',
            orderBy: strOrderBy
        },

        dataType: 'json',
        success: function(rgobjWine)
        {
            var html =
                '<div id="searchResult" data-role="page">' +
                    '<div data-role="panel" id="searchResultDialog" data-position="right" data-display="overlay" data-position-fixed="true">' +
                        '<div></div>' +
                    '</div>' +
                    '<div data-role="header" data-theme="b" data-add-back-btn="true" data-position="fixed" data-back-btn-text="戻る" data-back-btn-theme="b">' +
                        '<a href="https://anyway-grapes.jp/cart.php" class="ui-btn ui-btn-b ui-btn-right ui-btn-icon-left ui-icon-cart" data-ajax="false">カート</a>' +
                        '<h1>' + pageTitle + '</h1>' +
                        radioButtonsHtml +
                    '</div>' +
                    '<div data-role="content" class="ui-content">';

            var cMaxItemToShow = 25,
                cWine          = rgobjWine.length;

            html += generateWineItemListHtml(rgobjWine, filterBy, 'All');
            html += '</div></div>';

            $searchResultPage = $('div#searchResult');
            if ($searchResultPage.length > 0)
            {
                $searchResultPage.remove();
            }

            $('body').append(html);

            // Register event handler for the "Show More" button.
            $('div#searchResult').on('click', 'li.showMoreListItem > a', function()
            {
                $ul = $(this).closest('ul');
                $(this).closest('li').remove();

                var j               = -1,
                    cWineToAdd      = 0,
                    ulInnerHtml     = '',
                    objSearchedWine = null,
                    filterValue     = $('div#searchResult label.ui-btn-active').siblings().val(),
                    actualValue     = '',
                    fAuthenticated  = isAuthenticated();

                for (j = iSearchedWine; (cWineToAdd < cMaxItemToShow) && (j < cWine); ++j)
                {
                    objSearchedWine = rgobjSearchedWine[j];
                    actualValue     = (filterBy === 'type') ? objSearchedWine.type : objSearchedWine.country;

                    if ((filterValue == 'All') || (filterValue == actualValue))
                    {
                        ulInnerHtml += generateListItemHtml(rgobjSearchedWine[j], fAuthenticated);
                        ++cWineToAdd;
                    }
                }

                if (j < cWine)
                {
                    iSearchedWine = j;
                    ulInnerHtml += '<li class="showMoreListItem"><a href="#">もっと見る</a></li>';
                }

                $ul.append(ulInnerHtml);
                $ul.listview('refresh');
            });

            $('div#searchResult div.ui-content ul').listview();
            $('div#searchResult div.ui-content ul').on('click', 'a', function(e)
            {
                renderWineDetailDialog(this.id, $('div#searchResultDialog > div'));
            });

            // Registers an event handler, which filter wines by type.
            $('div#searchResult').on('change', ':radio', function(e)
            {
                var filterValue = $(this).val(),
                    html        = generateWineItemListHtml(rgobjWine, filterBy, filterValue);

                $('ul#searchResultList').html(html);
                $('ul#searchResultList').listview();
            });

            $.mobile.loading('hide', {});
            $.mobile.changePage('#searchResult', { transition: 'flip' });
        },

        error: function(){}
   });
}

$('div#mainPage').on('pagecreate', function(event)
{
    $('div#mainPage').on('click', 'img#signupImg', function()
    {
        location.href = 'http://anyway-grapes.jp/store/index.php?pc_view=1&submenu=member_rules';
    });

    $('div#mainPage').on('click', 'button#searchBtn', function()
    {
        var strCondition = '',
            strInput     = $(this).siblings('div').find('input').val();

        strInput     = strInput.replace(/[-|　| |・]+/g, '_');
        strCondition =
            '(name LIKE "%' + strInput + '%")' +
            'OR' +
            '(name_jpn LIKE "%' + strInput + '%")' +
            'OR' +
            '(producer LIKE "%' + strInput + '%")' +
            'OR' +
            '(producer_jpn LIKE "%' + strInput + '%")' +
            'OR' +
            '(region LIKE "%' + strInput + '%")' +
            'OR' +
            '(region_jpn LIKE "%' + strInput + '%")' +
            'OR' +
            '(village LIKE "%' + strInput + '%")' +
            'OR' +
            '(village_jpn LIKE "%' + strInput + '%")' +
            'OR' +
            '(district LIKE "%' + strInput + '%")' +
            'OR' +
            '(district_jpn LIKE "%' + strInput + '%")' +
            'OR' +
            '(cepage LIKE "%' + strInput + '%")' +
            'OR' +
            '(importer LIKE "%' + strInput + '%")';
            
        renderSearchResult('検索結果', strCondition, 'price', 'type');
    });

    $('div#mainPage').on('click', 'a#rankingPageLnk', function()
    {
        if ($('div#rankingPage').length == 0)
        {
           renderRankingPage();
        }
        else
        {
            $.mobile.changePage('#rankingPage', { transition: 'flip' });
        }
    });
 
    $('div#mainPage').on('click', 'a#wineSetPageLnk', function()
    {
        if ($('div#wineSetPage').length == 0)
        {
            renderWineSetPage();
        }
        else
        {
            $.mobile.changePage('#wineSetPage', { transition: 'flip' });
        }
    });

    $('div#findByType').on('click', 'a', function()
    {
        var title        = '',
            strType      = '',
            strCondition = '',
            strFilter    = 'country';

        if (this.id == 'sparklingLnk')
        {
            title   = 'スパークリング・ワイン';
            strType = 'Mousseux';
        }
        else if (this.id == 'champagneLnk')
        {
            title   = 'シャンパーニュ';
            strType = 'Champagne';
        }
        else if (this.id == 'whiteLnk')
        {
            title   = '白ワイン';
            strType = 'Blanc';
        }
        else if (this.id == 'redLnk')
        {
            title   = '赤ワイン';
            strType = 'Rouge';
        }
        else if (this.id == 'roseLnk')
        {
            title   = 'ロゼワイン';
            strType = 'Rosé';
        }

        strCondition = "type='" + strType + "'";
        renderSearchResult(title, strCondition, 'price', strFilter);
    });

    $('div#findByVintage').on('click', 'a', function()
    {
        var title      = '',
            minVintage = 0,
            maxVintage = 0;

        if (this.id == 'sixtiesLnk')
        {
            title      = '1960年代';
            minVintage = 1960;
            maxVintage = 1969;
        }
        else if (this.id == 'seventiesLnk')
        {
            title = '1970年代';
            minVintage = 1970;
            maxVintage = 1979;
        }
        else if (this.id == 'eightiesLnk')
        {
            title = '1980年代';
            minVintage = 1980;
            maxVintage = 1989;
        }
        else if (this.id == 'nintiesLnk')
        {
            title = '1990年代';
            minVintage = 1990;
            maxVintage = 1999;
        }
        else if (this.id == 'doubleOsLnk')
        {
            title = '2000年代';
            minVintage = 2000;
            maxVintage = 3000;
        }

        strCondition = '(vintage >= ' + minVintage + ') AND (vintage <= ' + maxVintage + ')';
        renderSearchResult(title, strCondition, 'vintage', 'type');
    });

    $('div#findByPrice').on('click', 'a', function()
    {
        var title         = '',
            taxedMinPrice = 0,
            taxedMaxPrice = 0,
            taxRate       = <?= $taxRate ?>;

        if (this.id == 'oneThousandRangeLnk')
        {
            title         = '1000円台のワイン';
            taxedMinPrice = 1000;
            taxedMaxPrice = 1999;
        }
        else if (this.id == 'twoThousandRangeLnk')
        {
            title         = '2000円台のワイン';
            taxedMinPrice = 2000;
            taxedMaxPrice = 2999;
        }
        else if (this.id == 'threeThousandRangeLnk')
        {
            title         = '3000円台のワイン';
            taxedMinPrice = 3000;
            taxedMaxPrice = 3999;
        }
        if (this.id == 'fiveThousandRangeLnk')
        {
            title         = '5000円台のワイン';
            taxedMinPrice = 5000;
            taxedMaxPrice = 5999;
        }
        else if (this.id == 'moreThan10000YenLnk')
        {
            title         = '10000円以上のワイン';
            taxedMinPrice = 10000;
            taxedMaxPrice = 999999;
        }

        var minPrice     = Math.floor(taxedMinPrice / taxRate),
            maxPrice     = Math.floor(taxedMaxPrice / taxRate),
            strCondition = '(price >= ' + minPrice + ') AND (price <= ' + maxPrice + ')';

        renderSearchResult(title, strCondition, 'price', 'type');
    });

    $('div#findByCountry').on('click', 'a', function()
    {
        var strCountry = '';

        if (this.id == 'franceLnk')
        {
            strCountry = 'France';
        }
        else if (this.id == 'italyLnk')
        {
            strCountry = 'Italy';
        }
        else if (this.id == 'germanyLnk')
        {
            strCountry = 'Germany';
        }
        else if (this.id == 'austriaLnk')
        {
            strCountry = 'Austria';
        }
        else if (this.id == 'spainLnk')
        {
            strCountry = 'Spain';
        }
        else if (this.id == 'portugalLnk')
        {
            strCountry = 'Portugal';
        }
        else if (this.id == 'americaLnk')
        {
            strCountry = 'United States';
        }
        else if (this.id == 'southAfricaLnk')
        {
            strCountry = 'South Africa';
        }
        else if (this.id == 'australiaLnk')
        {
            strCountry = 'Australia';
        }
        else if (this.id == 'newZealandLnk')
        {
            strCountry = 'New Zealand';
        }
        else if (this.id == 'croatiaLnk')
        {
            strCountry = 'Croatia';
        }
        else if (this.id == 'moldovaLnk')
        {
            strCountry = 'Moldova';
        }

        var title        = countryHash[strCountry].name,
            strCondition = "(country = '" + strCountry + "')";

        renderSearchResult(title, strCondition, 'price', 'type');
    });

    $('div#findByRegion').on('click', 'a', function()
    {
        var title      = '',
            strCountry = 'France',
            strRegion  = '';

        if (this.id == 'champagneLnk')
        {
            title     = 'シャンパーニュ地方';
            strRegion = 'Champagne';
        }
        else if (this.id == 'alsaceLnk')
        {
            title     = 'アルザス地方';
            strRegion = 'Alsace';
        }
        else if (this.id == 'loireLnk')
        {
            title     = 'ロワール河流域';
            strRegion = 'Vallée de la Loire';
        }
        else if (this.id == 'bordeauxLnk')
        {
            title     = 'ボルドー';
            strRegion = 'Bordeaux';
        }
        else if (this.id == 'southWestLnk')
        {
            title     = '南西地方';
            strRegion = 'Sud-Ouest';
        }
        else if (this.id == 'bourgogneLnk')
        {
            title     = 'ブルゴーニュ地方';
            strRegion = 'Bourgogne';
        }
        else if (this.id == 'juraLnk')
        {
            title     = 'ジュラ地方';
            strRegion = 'Jura';
        }
        else if (this.id == 'savoieLnk')
        {
            title     = 'サヴォワ地方';
            strRegion = 'Savoie';
        }
        else if (this.id == 'rhoneLnk')
        {
            title     = 'ローヌ河流域';
            strRegion = 'Vallée du Rhône';
        }
        else if (this.id == 'languedocLnk')
        {
            title     = 'ラングドック地方';
            strRegion = 'Languedoc';
        }
        else if (this.id == 'roussillonLnk')
        {
            title     = 'ルーション地方';
            strRegion = 'Roussillon';
        }
        else if (this.id == 'provenceLnk')
        {
            title     = 'プロヴァンス地方';
            strRegion = 'Provence';
        }
        else if (this.id == 'corseLnk')
        {
            title     = 'コルシカ島';
            strRegion = 'Corse';
        }

        var strCondition = "((country = '" + strCountry + "') AND (region = '" + strRegion + "'))";
        renderSearchResult(title, strCondition, 'price', 'type');
    });

    $('div#footerLinkPane').on('click', 'a', function()
    {
        $('#mainPanel').html('<div></div>');

        var targetHtml = '';

        if (this.id == 'qualityLnk')
        {
            targetHtml = './inc/quality.html';
            $('#qualityPanel > div').load(targetHtml, function()
            {
            });
        }
        else if (this.id == 'companyLnk')
        {
            targetHtml = './inc/company.html';
            $('#companyPanel > div').load(targetHtml, function()
            {
            });
        }
        else if (this.id == 'shippingLnk')
        {
            targetHtml = './inc/shipping.html';
            $('#shippingPanel > div').load(targetHtml, function()
            {
            });
        }
        else if (this.id == 'paymentLnk')
        {
            targetHtml = './inc/payment.html';
            $('#paymentPanel > div').load(targetHtml, function()
            {
            });
        }
        else if (this.id == 'privacyLnk')
        {
            targetHtml = './inc/privacy.html';
            $('#privacyPanel > div').load(targetHtml, function()
            {
            });
        }
        else if (this.id == 'cmTransactionLnk')
        {
            targetHtml = './inc/cmTransaction.html';
            $('#cmTransactionPanel > div').load(targetHtml, function()
            {
            });
        }
        else if (this.id == 'businessSaleLnk')
        {
            targetHtml = './inc/business_sale.html';
            $('#businessSalePanel > div').load(targetHtml, function()
            {
            });
        }
    });
});

(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-44746254-4', 'auto');
ga('send', 'pageview');

</script>

<script async src="//code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.js"></script>

