<?php

$curDirPath = dirname(__FILE__);
$lastAlertDateFilePath = "$curDirPath/stock_records/lastSync.txt";
$lastAlertDate         = file_get_contents($lastAlertDateFilePath);
if (date('Y-m-d') == $lastAlertDate)
{
    exit('Restocked alert was already sent today.');
}

require_once("$curDirPath/defines.php");
require_once("$curDirPath/../../includes/config.inc.php");
require_once(MYSQL);

function generateRestockedItemsText($wineHash)
{
    $text       = '';
    $strPadding = '　';

    foreach ($wineHash as $key => $rgobjWine)
    {
        $text .=
"

-------------------------
  $key
-------------------------";

        foreach ($rgobjWine as $objWine)
        {
            $code     = $objWine['barcode_number'];
            $itemName = $objWine['vintage'] . ' ' . $objWine['name_jpn'];

            $text .=
"

$itemName
[http://anyway-grapes.jp/store/index.php?pc_view=1&submenu=wine_detail&id=$code]";
        }
    }

    return $text;
}

function generateRestockedItemsHtml($wineHash)
{
    $html = '';

    foreach ($wineHash as $key => $rgobjWine)
    {
        $html .= '
<table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
    <thead style="border-bottom:solid 1px rgb(82,82,82);font-size:14px;font-weight:bold;">
        <tr>
            <td style="padding:4px;">' .  htmlentities($key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>
        </tr>
    </thead>
    <tbody style="font-size:12px;">
';

        $i = 0;
        foreach ($rgobjWine as $objWine)
        {
            $code     = $objWine['barcode_number'];
            $itemName = $objWine['vintage'] . ' ' . $objWine['name_jpn'];

            if ($i % 2)
            {
                $html .= '<tr style="background-color:rgb(240, 240, 240);">';
            }
            else
            {
                $html .= '<tr>';
            }

            $html .= '
            <td style="padding:4px;">
                <a href="http://anyway-grapes.jp/store/index.php?pc_view=1&submenu=wine_detail&id=' . $code . '">' . $itemName . '</a>
            </td>
        </tr>';

            ++$i;
        }

    $html .= '
    </tbody>
</table>';
    }

    return $html;
}

$rgobjWine = mysqli_query($dbc, "CALL get_restocked_wines()");
if ($rgobjWine !== FALSE)
{
    $cWine = mysqli_num_rows($rgobjWine);
    if ($cWine > 0)
    {
        $allWineHash = array();

        while ($row = mysqli_fetch_array($rgobjWine, MYSQLI_ASSOC))
        {
            $key   = $row['producer'];
            $value = $row['barcode_number'];
            if (!array_key_exists($key, $allWineHash))
            {
                $allWineHash[$key] = array();
            }

            array_push($allWineHash[$key], $row);
        }

        prepareNextQuery($dbc);

        $rgobjEmail = mysqli_query($dbc, "CALL get_emails_for_restock_alert()");
        if ($rgobjEmail !== FALSE)
        {
	    $cEmail = mysqli_num_rows($rgobjEmail);
	    if ($cEmail > 0)
	    {
		require_once(E_MAIL);

		$subject = 'Anyway-Grapes: 入荷商品のご案内';
		$from    = 'noreply@anyway-grapes.jp';

		while ($objEmail = mysqli_fetch_array($rgobjEmail, MYSQLI_ASSOC))
		{
		    prepareNextQuery($dbc);

		    $restockedWineHash = array();
		    $email             = $objEmail['email'];
		    $rgobjProducer     = mysqli_query($dbc, "CALL get_favorite_producers_by_email('$email')");
		    if (($rgobjProducer !== FALSE) && (mysqli_num_rows($rgobjProducer) > 0))
		    {
			while ($objProducer = mysqli_fetch_array($rgobjProducer, MYSQLI_ASSOC))
			{
                            $strProducer = $objProducer['producer'];
			    if (array_key_exists($strProducer, $allWineHash))
			    {
				$restockedWineHash[$strProducer] = $allWineHash[$strProducer];
			    }
			}

			mysqli_free_result($rgobjProducer);
		    }

                    if (count($restockedWineHash) > 0)
		    {
			include("$curDirPath/../../mails/text/restock_alert_mail_body.php");
                        sendTextMail(
                            $email,
                            $from,
                            $from,
                            $subject,
                            $textMessage
                        );

                        /*
			include("$curDirPath/../../mails/html/restock_alert_mail_body.php");
			sendMultipartMail(
			    $email,
			    $from,
			    $from,
			    $subject,
			    $textMessage,
			    $htmlMessage
			);
                         */
		    }
		}
	    }

	    mysqli_free_result($rgobjEmail);
	}
    }

    mysqli_free_result($rgobjWine);
}

// Record the date when the alert was sent.
file_put_contents($lastAlertDateFilePath, date('Y-m-d'));

?>
