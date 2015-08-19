<?php
namespace Luyu\Wechat\Pay;

/**
 * 支付订单
 * Class Order
 * @author Luyu Zhang<goocarlos@gmail.com>
 * @package Luyu\Wechat\Pay
 */
class Order
{

    /**
     * 公众账号ID
     * @var string $appid
     */
    public $appid;

    /**
     * 商户号
     * @var string $mch_id
     */
    public $mch_id;

    /**
     * 设备号
     * @var string $device_info
     */
    public $device_info;

    /**
     * 随机字符串
     * @var string $nonce_str
     */
    public $nonce_str;

    /**
     * 随机字符串
     * @var string $sign
     */
    public $sign;

    /**
     * 商品描述
     * @var string
     */
    public $body;

    /**
     * 商品详情
     * @var string
     */
    public $detail;

    /**
     * 附加数据
     * @var string
     */
    public $attach;

    /**
     * 商户订单号
     * @var string
     */
    public $out_trade_no;

    /**
     * 货币类型
     * @var string string
     */
    public $fee_type;

    /**
     * 总金额
     * @var
     */
    public $total_fee;

    /**
     * APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
     * @var string
     */
    public $spbill_create_ip;

    /**
     * 订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
     * @var string(14)
     * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_2
     */
    public $time_start;

    /**
     * 订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则
     * @var string(14)
     * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_2
     */
    public $time_expire;

    /**
     * 商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
     * @var string
     * @see https://pay.weixin.qq.com/wiki/doc/api/sp_coupon.php?chapter=12_1
     */
    public $goods_tag;

    /**
     * 接收微信支付异步通知回调地址
     * @var string
     */
    public $notify_url;

    /**
     * 取值如下：JSAPI，NATIVE，APP，WAP,详细说明见参数规定
     * @var string
     */
    public $trade_type;

    /**
     * trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
     * @var string
     */
    public $product_id;

    /**
     * 指定支付方式
     * no_credit--指定不能使用信用卡支付
     * @var string
     */
    public $limit_pay;

    /**
     * 用户标识
     * trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。下单前需要调用【网页授权获取用户信息】接口获取到用户的Openid。企业号请使用【企业号OAuth2.0接口】获取企业号内成员userid，再调用【企业号userid转openid接口】进行转换
     * @var string
     */
    public $openid;


    function __construct()
    {
        $this->device_info = 'WEB';
        $this->fee_type = 'CNY';
        $this->trade_type = 'JSAPI';
    }

    /**
     * 检查 JSAPI 模式的参数完整性
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @return bool
     */
    public function checkJsApiAndOpenId()
    {

        if ($this->trade_type == 'JSAPI' and empty($this->openid)) {
            return false;
        } else {
            return true;
        }
    }

}