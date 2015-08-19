<?php
namespace Luyu\Wechat\Pay;

/**
 * 支付回调对象
 * Class NotifyXML
 * @author Luyu Zhang<goocarlos@gmail.com>
 */
class NotifyXML
{
    public $appid;

    public $mch_id;

    /**
     * 商家数据包
     * @var string
     */
    public $attach;

    public $bank_type;

    public $fee_type;

    /**
     * 是否关注公众账号
     * @var string
     */
    public $is_subscribe;

    /**
     * 随机字符串
     * @var string
     */
    public $nonce_str;

    public $openid;

    /**
     * 商户订单号
     * @var string
     */
    public $out_trade_no;

    public $return_code;

    public $return_msg;

    public $result_code;

    public $sign;

    public $sub_mch_id;

    public $time_end;

    public $total_fee;

    public $trade_type;

    /**
     * 微信支付订单号
     * @var string
     */
    public $transaction_id;


    /**
     * @param $resultArray
     */
    function __construct($resultArray)
    {

        if (!isset($resultArray['return_code'])) {
            throw new WechatPayException("[WeixinPay]传入的不是合法的微信支付 XML");
        }

        $this->return_code = $resultArray['return_code'];

        if (isset($resultArray['return_msg'])) {
            $this->return_msg = $resultArray['return_msg'];
        }

        if (isset($resultArray['appid'])) {
            $this->appid = $resultArray['appid'];
        }

        if (isset($resultArray['mch_id'])) {
            $this->mch_id = $resultArray['mch_id'];
        }

        if (isset($resultArray['attach'])) {
            $this->attach = $resultArray['attach'];
        }

        if (isset($resultArray['bank_type'])) {
            $this->bank_type = $resultArray['bank_type'];
        }

        if (isset($resultArray['fee_type'])) {
            $this->fee_type = $resultArray['fee_type'];
        }

        if (isset($resultArray['is_subscribe'])) {
            $this->is_subscribe = $resultArray['is_subscribe'];
        }

        if (isset($resultArray['nonce_str'])) {
            $this->nonce_str = $resultArray['nonce_str'];
        }

        if (isset($resultArray['sign'])) {
            $this->sign = $resultArray['sign'];
        }

        if (isset($resultArray['result_code'])) {
            $this->result_code = $resultArray['result_code'];
        }

        if (isset($resultArray['openid'])) {
            $this->openid = $resultArray['openid'];
        }

        if (isset($resultArray['trade_type'])) {
            $this->trade_type = $resultArray['trade_type'];
        }

        if (isset($resultArray['transaction_id'])) {
            $this->transaction_id = $resultArray['transaction_id'];
        }

        if (isset($resultArray['total_fee'])) {
            $this->total_fee = $resultArray['total_fee'];
        }

        if (isset($resultArray['out_trade_no'])) {
            $this->out_trade_no = $resultArray['out_trade_no'];
        }

        if (isset($resultArray['sub_mch_id'])) {
            $this->sub_mch_id = $resultArray['sub_mch_id'];
        }

        if (isset($resultArray['time_end'])) {
            $this->time_end = $resultArray['time_end'];
        }
    }

    /**
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @return bool
     */
    public function isRequestSuccess()
    {
        if ($this->return_code == 'FAIL') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @author Luyu Zhang<goocarlos@gmail.com>
     * @return bool
     */
    public function isTradeSuccess()
    {
        if ($this->result_code != 'SUCCESS') {
            return false;
        } else {
            return true;
        }
    }

}