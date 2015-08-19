# Wechat Pay

试图封装一个轻量优雅的微信支付类。

目前支持 JSSDK 方式支付的下单和回调，后续会进一步完善，以支持更多的接口能力。

## 创建支付订单

```
namespace Luyu\Wechat\Pay;

$wxPayClient = new WxPayClient(
    $appId,
    $appSecret,
    $mchId,
    $mchKey,
    $notifyUrl,
    $sslCertPath,
    $sslKeyPath);

$wxPayOrder = $wxPayClient->placeOrder($payment->payment_no,
    $amount,
    $title,
    $buyerOpenId,
    $buyerIp);

var_dump($wxPayOrder);
```

## 生成微信 JSSDK 入参

调用该方法可以生成提供给[微信 JSSDK 发起微信支付请求](http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html#.E5.BE.AE.E4.BF.A1.E6.94.AF.E4.BB.98)的入参，因微信的签名限制，仅可使用一次。

```
namespace Luyu\Wechat\Pay;

// 微信返回的预支付 ID
$prepayId = $wxPayOrder->prepay_id;
$wxPayParams = $wxPayClient->getParamsOfJSSDK($prepayId);
var_dump($wxPayParams);
```

### 返回以下结构


```
    "wx_pay_params": {
      "appId": "wx63f1441d71adc9c4",
      "nonceStr": "jAqnmVoNZy2SxKdO",
      "package": "prepay_id:wx201508182030004e166de4b40607473421",
      "signType": "MD5",
      "timeStamp": 1439901000,
      "paySign": "A0EC84699B51C8C04F487243E65D7692"
    }
```

## 验证支付结果

以下范例代码在提供给微信的 notify 回调地址中实现，捕捉微信 POST 来的 XML 数据包验证有效性。

```
namespace Luyu\Wechat\Pay;

// Laravel 中有效，原生 PHP 请使用 php://input
$postData = $request->getContent();

$notifyXMLArray = $wxPayClient->arrayFromXML($content);
$notifyXML      = $wxPayClient->notifyFromArray($notifyXMLArray);

// 验证签名
if (!$wxPayClient->verifyNotify($notifyXMLArray, true)) {
    return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败或支付结果为失败]]></return_msg></xml>';
}

// 验证付款结果
if (!$notifyXML->isRequestSuccess()) {
    return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[验证成功，业务失败]]></return_msg></xml>';
}

// 验证业务结果
if (!$notifyXML->isTradeSuccess()) {
    return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[验证成功，支付失败]]></return_msg></xml>';
}

// 成功
return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[验证成功，支付成功]]></return_msg></xml>';
```