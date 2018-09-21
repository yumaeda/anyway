<?php

require('./includes/config.inc.php');
require(MYSQL);

$inputErrors = array();
$userId      = startCartSession($dbc); 
$bodyHtml    = '';
if (isset($_SESSION['user_id']))
{
    $userEmail = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        $result = mysqli_query($dbc, "CALL get_favorite_producers_by_email('$userEmail')");
        if (($result !== FALSE) && (mysqli_num_rows($result) > 0))
        {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
            {
                $producer = $row['producer'];
                $bodyHtml .= "$producer<br />";
            }
        }
    }

    $pageTitle = 'お気に入りの生産者';
    include('./includes/header.html');
    include('./views/favorite_producers.html');
    include('./includes/footer.html');
}

mysqli_close($dbc);

?>
