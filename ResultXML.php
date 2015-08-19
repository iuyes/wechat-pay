<?php
namespace Luyu\Wechat\Pay;

/**
 * 微信返回结果
 * Class ResultXML
 * @author Luyu Zhang<goocarlos@gmail.com>
 * @package Luyu\Wechat\Pay
 */
class ResultXML
{

    public $return_code;

    public $return_msg;

    public $appid;

    public $mch_id;

    public $nonce_str;

    public $sign;

    public $result_code;

    public $prepay_id;

    public $trade_type;

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

        if (isset($resultArray['nonce_str'])) {
            $this->nonce_str = $resultArray['nonce_str'];
        }

        if (isset($resultArray['sign'])) {
            $this->sign = $resultArray['sign'];
        }

        if (isset($resultArray['result_code'])) {
            $this->result_code = $resultArray['result_code'];
        }

        if (isset($resultArray['prepay_id'])) {
            $this->prepay_id = $resultArray['prepay_id'];
        }

        if (isset($resultArray['trade_type'])) {
            $this->trade_type = $resultArray['trade_type'];
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