<?

$curDirPath = dirname(__FILE__);
include_once("$curDirPath/../../../restaurant/common.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $url = [];

    $result = mysqli_query($dbc, "CALL get_producer_detail('')");
    if ($result !== FALSE)
    {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
        {
            $producerPage = new \AnywayGrapes\App\ProducerPage($row);
            $url[] = $producerPage->getUrl();
        }

        mysqli_free_result($result);
    }

    $sitemap = new \Vendor\Seiya\SEO\Sitemap($url);
    $sitemap->write('../../../sitemap.xml');
}

mysqli_close($dbc);

echo 'Finished refreshing sitemap.xml :)';

?>
