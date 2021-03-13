<?php

$PUBLIC_KEY = 'XXXX';
$PRIVATE_KEY = 'YYYY';

function _getTokenId($cardNumber, $expMonth, $expYear, $cvc)
{
    global $PUBLIC_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.pay.jp/v1/tokens');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "card%5Bnumber%5D=$cardNumber&card%5Bcvc%5D=$cvc&card%5Bexp_month%5D=$expMonth&card%5Bexp_year%5D=$expYear");

    $headers = array();
    $headers[] = 'Authorization: Basic ' . base64_encode("$PUBLIC_KEY:");
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $token = array();
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $token['error'] = array(
            'message' => curl_error($ch)
        );
    }
    else {
        $json_result = json_decode($result);
        if (!isset($json_result->error)) {
            $token['id'] = $json_result->id;
        } else {
            $token['error'] = array(
                'message' => $json_result->error->message,
                'code' => $json_result->error->code
            );
            error_log("Error Response: $result", 1, 'yumaeda@gmail.com');
        }
    }
    curl_close($ch);

    return $token;
}

function chargeWithPayjp($orderId, $totalPayment, $cardNumber, $expMonth, $expYear, $cvc, $fCapture)
{
    global $PRIVATE_KEY;

    $token = _getTokenId($cardNumber, $expMonth, $expYear, $cvc);
    if (!isset($token['id'])) {
        return json_decode(json_encode($token));
    }

    $curDirPath = dirname(__FILE__);
    require_once "$curDirPath/../includes/payjp-php/init.php";

    \Payjp\Payjp::setApiKey($PRIVATE_KEY);

    return \Payjp\Charge::create(array(
        'card' => $token['id'],
        'amount' => $totalPayment,
        'capture' => $fCapture,
        'currency' => 'jpy'
    ));
}

function convertPayjpErrorCodeToText($errorCode)
{
    $errorMessage = 'SYSTEM_ERROR';
    switch ($errorCode)
    {
        case 'incorrect_card_data': // いずれかのカード情報が誤っている
        case 'invalid_expiry_month': // 不正な有効期限月
        case 'invalid_expiry_year': // 不正な有効期限年
        case 'expired_card': // 有効期限切れ
            $errorMessage = '指定されたクレジットカードでは決済できませんでした。カード番号、セキュリティーコード、有効期限を確認のうえ再入力頂くか、「銀行振り込み」を選択して下さい。';
            break;
        case 'card_declined': // カード会社によって拒否されたカード
        case 'unacceptable_brand': // 対象のカードブランドが許可されていない
        case 'invalid_card': // 不正なカード
            $errorMessage = '指定されたカードはご利用頂けません。別のカードをご利用頂くか、カード発行会社へお問い合わせください。';
            break;
        case 'processing_error': // 決済ネットワーク上で生じたエラー
        case 'invalid_id': // 不正なID
        case 'no_api_key': // APIキーがセットされていない
        case 'invalid_api_key': // 不正なAPIキー
        case 'invalid_expiry_days': // 不正な失効日数
        case 'unnecessary_expiry_days': // 失効日数が不要なパラメーターである場合
        case 'invalid_flexible_id': // 不正なID指定
        case 'invalid_string_length': // 不正な文字列長
        case 'invalid_country': // 不正な国名コード
        case 'invalid_currency': // 不正な通貨コード
        case 'invalid_amount': // 不正な支払い金額
        case 'invalid_boolean': // 不正な論理値
        case 'no_allowed_param': // パラメーターが許可されていない場合
        case 'no_param': // パラメーターが何もセットされていない
        case 'invalid_querystring': // 不正なクエリー文字列
        case 'missing_param': // 必要なパラメーターがセットされていない
        case 'invalid_param_key': // 指定できない不正なパラメーターがある
        case 'failed_payment': // 指定した支払いが失敗している場合
        case 'invalid_amount_to_not_captured': // 確定されていない支払いに対して部分返金ができない
        case 'capture_amount_gt_net': // 支払い確定額が元の支払い額より大きい
        case 'already_captured': // すでに支払いが確定済み
        case 'cant_capture_refunded_charge': // 返金済みの支払いに対して支払い確定はできない
        case 'cant_reauth_refunded_charge': // 返金済みの支払いに対して再認証はできない
        case 'charge_expired': // 認証が失効している支払い
        case 'already_exist_id': // すでに存在しているID
        case 'token_already_used': // すでに使用済みのトークン
        case 'invalid_billing_day': // 不正な支払い実行日
        case 'too_many_metadata_keys': // metadataキーの登録上限(20)を超過している
        case 'invalid_metadata_key': // 不正なmetadataキー
        case 'invalid_metadata_value': // 不正なmetadataバリュー
        case 'test_card_on_livemode': // 本番モードのリクエストにテストカードが使用されている
        case 'not_activated_account': // 本番モードが許可されていないアカウント
        case 'too_many_test_request': // テストモードのリクエストリミットを超過している
        case 'payjp_wrong': // PAY.JPのサーバー側でエラーが発生している
        case 'pg_wrong': // 決済代行会社のサーバー側でエラーが発生している
        case 'not_found': // リクエスト先が存在しないことを示す
        case 'not_allowed_method': // 許可されていないHTTPメソッド
        case 'over_capacity': // レートリミットに到達
        default:
            $errorMessage = $SYSTEM_ERROR;
            break;
    }

    return $errorMessage;
}
