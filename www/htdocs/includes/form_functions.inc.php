<?php

function closeBeginTag($name, $example, $errors, $options)
{
    if (!empty($options) && is_array($options))
    {
        foreach ($options as $attrKey => $attrValue)
        {
            echo " $attrKey=\"$attrValue\"";
        }
    }

    echo ' />';

    $strExample = !empty($example) ? "&nbsp;&nbsp;例）$example" : '';
    if (array_key_exists($name, $errors))
    {
        echo '<br /><span class="errorSpan">' . $errors[$name] . $strExample . '</span>';
    }
    elseif ($strExample !== '')
    {
        echo '<span class="exampleSpan">' . $strExample . '</span>';
    }
}

function create_form_input($name, $type, $example = '', $errors = array(), $values = 'POST', $options = array())
{
    $value = false;
    if ($values === 'SESSION')
    {
        if (isset($_SESSION[$name]))
        {
            $value = htmlspecialchars($_SESSION[$name], ENT_QUOTES, 'UTF-8');
        }
    }
    elseif ($values === 'POST')
    {
        if (isset($_POST[$name]))
        {
            $value = htmlspecialchars($_POST[$name], ENT_QUOTES, 'UTF-8');
        }
    }

    if (($type === 'text') || ($type === 'password') || ($type === 'email'))
    {
        echo '<input type="' . $type . '" name="' . $name . '" id="' . $name . '"';
        if ($value)
        {
            echo ' value="' . $value . '"';
        }

        if ($name === 'wareki_year')
        {
            echo ' maxlength=="2"';
        }
 
        closeBeginTag($name, $example, $errors, $options);
    }
    elseif ($type === 'bool')
    {
        $strAttr = '';

        if ($name === 'refrigerated')
        {
            if (($_SERVER['REQUEST_METHOD'] === 'GET') && !isset($_SESSION[$name]))
            {
                $intMonth = intval(date('n'));
                if (($intMonth >= 4) && ($intMonth <= 10))
                {
                    $value = 1;
                }
            }
        }

        if ($value == 1)
        {
            $strAttr = ' checked="checked"';
        }

        echo '<input type="checkbox" name="' . $name . '" value="1" id="' . $name . '"' . $strAttr . ' />';
    }
    elseif ($type === 'note')
    {
        echo
            '<textarea name="' . $name . '" id="' . $name . '" placeholder="' . $example . '" rows="5" style="width:100%">' .
                $value .
            '</textarea>';
    }
    elseif ($type === 'select')
    {
        if (($name === 'prefecture') || ($name === 'cc_prefecture') || ($name === 'delivery_prefecture'))
        {
            $rgstrData = array(
                '北海道',
                '青森県',
                '岩手県',
                '宮城県',
                '秋田県',
                '山形県',
                '福島県',
                '茨城県',
                '栃木県',
                '群馬県',
                '埼玉県',
                '千葉県',
                '東京都',
                '神奈川県',
                '新潟県',
                '富山県',
                '石川県',
                '福井県',
                '山梨県',
                '長野県',
                '岐阜県',
                '静岡県',
                '愛知県',
                '三重県',
                '滋賀県',
                '京都府',
                '大阪府',
                '兵庫県',
                '奈良県',
                '和歌山県',
                '鳥取県',
                '島根県',
                '岡山県',
                '広島県',
                '山口県',
                '徳島県',
                '香川県',
                '愛媛県',
                '高知県',
                '福岡県',
                '佐賀県',
                '長崎県',
                '熊本県',
                '大分県',
                '宮崎県',
                '鹿児島県',
                '沖縄県'
            );
        }
        elseif ($name === 'delivery_option')
        {
            $rgstrData = array(
                'ご自宅に配送',
                'ご自宅以外に配送',
                '店頭引き取り'
            );
        }
        elseif ($name === 'wareki')
        {
            $rgstrData = array(
                '大正',
                '昭和',
                '平成'
            );
        }
        elseif ($name === 'delivery_company')
        {
            $rgstrData = array(
                'ヤマト運輸'
                // '佐川急便'
            );
        }
        elseif ($name === 'delivery_time')
        {
            $rgstrData = array(
                '指定なし',
                '午前中（8:00〜12:00）',
                '14:00 ～ 16:00',
                '16:00 ～ 18:00',
                '18:00 ～ 20:00',
                '19:00 ～ 21:00'
            );

            if (($values === 'SESSION') && isset($_SESSION['delivery_company']))
            {
                if ($_SESSION['delivery_company'] == '佐川急便')
                {
                    $rgstrData = array(
                        '指定なし',
                        '午前中（8:00〜12:00）',
                        '12:00 ～ 14:00',
                        '14:00 ～ 16:00',
                        '16:00 ～ 18:00',
                        '18:00 ～ 20:00',
                        '19:00 ～ 21:00'
                    );
                }
            }
        }
        elseif ($name === 'delivery_date')
        {
            $intCartType = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;
            if ($intCartType == 2)
            {
                $rgstrData = array(
                    date('Y年m月d日', strtotime("+12 days")),
                    date('Y年m月d日', strtotime("+13 days")),
                    date('Y年m月d日', strtotime("+14 days")),
                    date('Y年m月d日', strtotime("+15 days")),
                    date('Y年m月d日', strtotime("+16 days")),
                    date('Y年m月d日', strtotime("+17 days")),
                    date('Y年m月d日', strtotime("+18 days")),
                    date('Y年m月d日', strtotime("+19 days")),
                    date('Y年m月d日', strtotime("+20 days")),
                    date('Y年m月d日', strtotime("+21 days")),
                    date('Y年m月d日', strtotime("+22 days")),
                    date('Y年m月d日', strtotime("+23 days")),
                    date('Y年m月d日', strtotime("+24 days")),
                    date('Y年m月d日', strtotime("+25 days")),
                    date('Y年m月d日', strtotime("+26 days")),
                    date('Y年m月d日', strtotime("+27 days")),
                    date('Y年m月d日', strtotime("+28 days")),
                    date('Y年m月d日', strtotime("+29 days")),
                    date('Y年m月d日', strtotime("+30 days")),
                    date('Y年m月d日', strtotime("+31 days")),
                    date('Y年m月d日', strtotime("+32 days")),
                    date('Y年m月d日', strtotime("+33 days")),
                    date('Y年m月d日', strtotime("+34 days")),
                    date('Y年m月d日', strtotime("+35 days"))
                );
            }
            else
            {
                $rgstrData = array('指定なし');
                for ($i = 3; $i <= 10; ++$i)
                {
                    $rgstrData[] = date('Y年m月d日', strtotime("+$i days"));
                }
            }
        }
        elseif ($name === 'expMonth')
        {
            $rgstrData = array(
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12
            );
        }
        elseif ($name === 'expYear')
        {
            $rgstrData = array(
                date('Y', strtotime("+5 days")),
                date('Y', strtotime("+1 year")),
                date('Y', strtotime("+2 years")),
                date('Y', strtotime("+3 years")),
                date('Y', strtotime("+4 years")),
                date('Y', strtotime("+5 years")),
                date('Y', strtotime("+6 years")),
                date('Y', strtotime("+7 years")),
                date('Y', strtotime("+8 years")),
                date('Y', strtotime("+9 years")),
                date('Y', strtotime("+10 years"))
            );
        }

        echo '<select id="' . $name . '" name="' . $name . '">';
        foreach ($rgstrData as $strData)
        {
            echo '<option value="' . $strData . '"';
            if ($value === $strData)
            {
                echo ' selected="selected"';
            }
            echo ">$strData</option>\n";
        }
        echo '</select>';

        if (array_key_exists($name, $errors))
        {
            echo '<br /><span style="color:red;">' . $errors[$name] . '</span>';
        }
    }
}

function create_date_of_birth_input($errors, $values)
{
    create_form_input('wareki', 'select', '', array(), $values, array());
    echo '&nbsp;&nbsp;';
    create_form_input('wareki_year', 'text', '', array(), $values, array( 'maxlength' => 2, 'size' => 2, 'autocomplete' => 'off'));
    echo '&nbsp;年&nbsp;';
    create_form_input('wareki_month', 'text', '', array(), $values, array( 'maxlength' => 2, 'size' => 2, 'autocomplete' => 'off'));
    echo '&nbsp;月&nbsp;';
    create_form_input('wareki_date', 'text', '', array(), $values, array( 'maxlength' => 2, 'size' => 2, 'autocomplete' => 'off'));
    echo '&nbsp;日&nbsp;';

    if (array_key_exists('wareki', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['wareki'] . '</span>';
    }
}

function create_full_name_input($errors, $values, $fDelivery = FALSE)
{
    $strDeliveryPrefix = $fDelivery ? 'delivery_' : '';

    echo '姓:&nbsp;';
    create_form_input($strDeliveryPrefix . 'last_name', 'text', '', array(), $values, array());
    echo '&nbsp;&nbsp;';
    echo '名:&nbsp;';
    create_form_input($strDeliveryPrefix . 'first_name', 'text', '', array(), $values, array());

    if (array_key_exists('name', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['name'] . '</span>';
    }
}

function create_full_phonetic_input($errors, $values, $fDelivery = FALSE)
{
    $strDeliveryPrefix = $fDelivery ? 'delivery_' : '';

    echo 'せい:&nbsp;';
    create_form_input($strDeliveryPrefix . 'last_phonetic', 'text', '', array(), $values, array());
    echo '&nbsp;&nbsp;';
    echo 'めい:&nbsp;';
    create_form_input($strDeliveryPrefix . 'first_phonetic', 'text', '', array(), $values, array());

    if (array_key_exists('phonetic', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['phonetic'] . '</span>';
    }
}

function create_post_code_input($errors)
{
    create_form_input('post_code_1', 'text', '', array(), 'POST', array( 'maxlength' => 3, 'size' => 3, 'autocomplete' => 'off'));
    echo '-';
    create_form_input('post_code_2', 'text', '', array(), 'POST', array( 'maxlength' => 4, 'size' => 4, 'autocomplete' => 'off'));

    if (array_key_exists('post_code', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['post_code'] . '</span>';
    }
}

function create_credit_card_number_input($errors)
{
    create_form_input('card_number_1', 'text', '', array(), 'POST', array( 'maxlength' => 4, 'size' => 4, 'autocomplete' => 'off'));
    echo '-';
    create_form_input('card_number_2', 'text', '', array(), 'POST', array( 'maxlength' => 4, 'size' => 4, 'autocomplete' => 'off'));
    echo '-';
    create_form_input('card_number_3', 'text', '', array(), 'POST', array( 'maxlength' => 4, 'size' => 4, 'autocomplete' => 'off'));
    echo '-';
    create_form_input('card_number_4', 'text', '', array(), 'POST', array( 'maxlength' => 4, 'size' => 4, 'autocomplete' => 'off'));

    if (array_key_exists('card_number', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['card_number'] . '</span>';
    }
}

function create_expiration_input($errors)
{
    create_form_input('expMonth', 'select', '', array(), 'POST', array());
    echo '月';
    create_form_input('expYear', 'select', '', array(), 'POST', array());
    echo '年';

    if (array_key_exists('expiration', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['expiration'] . '</span>';
    }
}


// Functions for English forms.

function create_full_name_eng_input($errors, $values, $fDelivery = FALSE)
{
    $strDeliveryPrefix = $fDelivery ? 'delivery_' : '';

    echo 'Last Name:&nbsp;';
    create_form_input($strDeliveryPrefix . 'last_name', 'text', '', array(), $values, array());
    echo '&nbsp;&nbsp;';
    echo 'First Name:&nbsp;';
    create_form_input($strDeliveryPrefix . 'first_name', 'text', '', array(), $values, array());

    if (array_key_exists('name', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['name'] . '</span>';
    }
}

function create_expiration_eng_input($errors)
{
    create_form_input('expMonth', 'select', '', array(), 'POST', array());
    echo '&nbsp;/&nbsp;';
    create_form_input('expYear', 'select', '', array(), 'POST', array());

    if (array_key_exists('expiration', $errors))
    {
        echo '<br /><span style="color:red;">' . $errors['expiration'] . '</span>';
    }
}
