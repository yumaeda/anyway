<?php

if (!isset($_REQUEST['pc_view']) || ($_REQUEST['pc_view'] != 1))
{
    $strUserAgent = $_SERVER['HTTP_USER_AGENT'];

    // Redirect iPhone/iPod visitors
    if(strpos($strUserAgent, 'Android') ||
       strpos($strUserAgent, 'iPhone') ||
       strpos($strUserAgent, 'iPod'))
    {
        header('Location: ./sp/index.php');
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title>ワインのAnyway-Grapes｜世田谷区 経堂</title>
        <meta name="description" content="東京都世田谷区の経堂にある、地下セラー型のワインショップです。気軽に楽しめるカジュアルなワインから、マニア垂涎の希少まで幅広いワインを取り揃えております。" />
        <meta name="keywords" content="ワイン ショップ 小田急 東京 経堂 世田谷 Wine Shop Anyway Grapes" />
        <link rel="stylesheet" type="text/css" href="./index.min.css" />
    </head>
    <body>
        <header-block></header-block>
        <index-content></index-content>
        <navigation-block></navigation-block>
        <div class="instagram"></div>
        <div class="navigationPane"></div>
        <iframe id="access-map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3242.033145362007!2d139.634685!3d35.65155499999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018f3be02000bab%3A0xa52fa7406d77ee45!2sConceptual+Wine+Boutique+Anyway-Grapes!5e0!3m2!1sja!2sjp!4v1410321668722" width="100%" height="400" scrolling="no" frameborder="0"></iframe>
        <footer-block></footer-block>
        <iframe id="wholesale" src="./pages/business_sale.html" width="100%" height="600" frameborder="0" style="display:none;"></iframe>
    </body>
</html>

<script src="./plugins/riot+compiler.min.js"></script>
<script type="riot/tag" src="./index.tag"></script>
<script>

riot.mount('index-content',    { title: 'Content' })
riot.mount('header-block',     { title: 'Header' })
riot.mount('navigation-block', { title: 'Navigation' })
riot.mount('footer-block',     { title: 'Footer' })

</script>
<script src="http://code.jquery.com/jquery.js"></script>
<script type="text/javascript" src="./index.min.js"></script> 

