<?php
namespace Luyu\Wechat\Pay;

use \Exception;

/**
 * 微信支付
 * Class WechatPay
 * @see https://github.com/biangbiang/wxpay-php
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=11_1#
 * @author Luyu Zhang<goocarlos@gmail.com>
 * @package Luyu\Wechat\Pay
 */
class Client
{

    protected $appId;
    protected $appSecret;
    protected $mchId;
    protected $mchKey;

    protected $notifyUrl   = 'http://weixin.qq.com';
    protected $sslCertPath = '';
    protected $sslKeyPath  = '';

    const API_UNIFIEDORDER_URL = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    public function __construct($appId, $appSecret, $mchId, $mchKey, $notifyUrl, $sslCertPath, $sslKeyPath)
    {
        $this->appId       = $appId;
        $this->appSecret   = $appSecret;
        $this->mchId       = $mchId;
        $this->mchKey      = $mchKey;
        $this->notifyUrl   = $notifyUrl;
        $this->sslCertPath = $sslCertPath;
        $this->sslKeyPath  = $sslKeyPath;

    }

    /**
     * 生成支付订单
     * 目前仅支持 JSAPI 方式的订单
     * @author Luyu Zhang<goocarlos@gmail.com>
     */
    public function placeOrder($orderNo, $totalFee, $orderBody, $userOpenId, $userIp)
    {

        $order = new Order();

        // 填充订单
        $order->out_trade_no     = $orderNo;
        $order->total_fee        = ceil($totalFee * 100);
        $order->body             = $orderBody;
        $order->openid           = $userOpenId;
        $order->spbill_create_ip = $userIp;

        // 填充商户参数
        $order->appid      = $this->appId;
        $order->mch_id     = $this->mchId;
        $order->notify_url = $this->notifyUrl;
        $order->nonce_str  = $this->getNonceString(32);
        $order->trade_type = 'JSAPI';

        // 检查数据完整性
        if (!$order->checkJsApiAndOpenId()) {
            throw new WechatPayException("[WeixinPay]trade_type为 JSAPI 时,openid 为必填参数");
        }

        // 参数拼串签名
        $sign        = $this->sign((array)$order);
        $order->sign = $sign;

        // 转 XML
        $xml = $this->toXML((array)$order);

        // POST
        $wxResultXML = $this->postXmlWithCurl($xml, self::API_UNIFIEDORDER_URL, false, 30);

        // XML 转 Array，读状态 FAIL || SUCCESS
        $wxResultXML = $this->arrayFromXML($wxResultXML);
        $wxResult    = new ResultXML($wxResultXML);

        $wxResult->isRequestSuccess();

        if (!$wxResult->isRequestSuccess()) {
            throw new Exception('[PaymentService]微信支付请求失败，错误：' . $wxResult->return_code . $wxResult->return_msg);
        }

        if (!$wxResult->isTradeSuccess()) {
            throw new Exception('[PaymentService]微信支付交易失败，错误：' . $wxResult->result_code);
        }

        return $wxResult;
    }

    /**
     * notify 数组转回调对象
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @param $notifyXMLArray
     * @return NotifyXML
     */
    public function notifyFromArray($notifyXMLArray)
    {
        $notify = new NotifyXML($notifyXMLArray);
        return $notify;
    }

    /**
     * 构建微信 JSSDK 传入参数
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @param $prepayId
     * @return \stdClass
     */
    public function getParamsOfJSSDK($prepayId)
    {
        // 构建对象
        $params            = new \stdClass();
        $params->appId     = $this->appId;
        $params->nonceStr  = $this->getNonceString(32);
        $params->package   = "prepay_id=" . $prepayId;
        $params->signType  = "MD5";
        $params->timeStamp = time();

        // 进行签名
        $params->paySign = $this->sign((array)$params);
        return $params;
    }

    /**
     * 验证支付结果签名
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @param array     $notifyXMLArray
     * @param bool|true $verifySign
     * @return bool
     */
    public function verifyNotify(array $notifyXMLArray, $verifySign = true)
    {
        // 验证 $notify 签名
        $originSign = $notifyXMLArray['sign'];
        unset($notifyXMLArray['sign']);

        $notifySign = $this->sign($notifyXMLArray);

        if ($verifySign == true && $originSign != $notifySign) {
            return false;
        }

        return true;
    }

    /**
     * POST XML 到接口 URL
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @param            $xml
     * @param            $url
     * @param bool|false $useCert
     * @param int        $timeout
     * @return mixed
     * @throws Exception
     */
    private function postXmlWithCurl($xml, $url, $useCert = false, $timeout = 30)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // TODO: 暂时关闭;
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);// 严格校验
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $this->sslCertPath);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $this->sslKeyPath);
        }

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //运行curl
        $data = curl_exec($ch);

        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WechatPayException("[WeixinPay]请求提交失败，错误代码: $error");
        }
    }

    /**
     * Array 转 XML
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @return string
     */
    private function toXML($parameters)
    {
        if (!is_array($parameters) || count($parameters) <= 0) {
            throw new WechatPayException("[WeixinPay]传入了非数组参数，或数组为空");
        }

        $xml = "<xml>";
        foreach ($parameters as $k => $v) {
            if (is_numeric($v)) {
                $xml .= "<" . $k . ">" . $v . "</" . $k . ">";
            } else {
                $xml .= "<" . $k . "><![CDATA[" . $v . "]]></" . $k . ">";
            }
        }
        $xml .= "</xml>";

        return $xml;
    }

    /**
     * XML 转 Array
     * @author: Luyu Zhang<goocarlos@gmail.com>
     * @param $xml
     * @return mixed
     * @throws WxPayException
     */
    public function arrayFromXML($xml)
    {
        if (!$xml) {
            throw new WechatPayException("[WeixinPay]传入了非 XML 参数，或 XML 为空。");
        }

        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }

    /**
     * 参数签名，拼接 Key 并转 MD5
     * @see https://pay.weixin.qq.com/wiki/tools/signverify/
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @param $parameters
     * @return string
     */
    private function sign($parameters)
    {

        if (!isset($parameters['appid']) and !isset($parameters['appId'])) {
            throw new WechatPayException("[WeixinPay]参与拼串签名的数组的数组不完整");
        }

        // 排序
        $formattedParameters = $this->toUrlParams($parameters);

        // 追加 Key
        $formattedParameters = $formattedParameters . "&key=" . $this->mchKey;

        // MD5 签名转大写
        $signString = strtoupper(hash('md5', $formattedParameters));

        return $signString;
    }

    /**
     * 拼接查询参数，数组转换为 "appid=foo&body=bar" 形式
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @param $parameters
     * @return string
     */
    private function toUrlParams($parameters)
    {
        $formattedParameters = '';
        ksort($parameters);

        foreach ($parameters as $k => $v) {
            // 空值不参与签名
            if ($v) {
                $formattedParameters .= "$k=$v&";
            }
        }

        $formattedParameters = trim($formattedParameters, "&");
        return $formattedParameters;
    }

    /**
     * 生成随机字符串
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @param int $length
     * @return string
     */
    private function getNonceString($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}