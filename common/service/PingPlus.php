<?php
/**
 * 公共方法
 */
namespace common\service;

class PingPlus{
    //定义支付方式
    public static $channel_type = array(
        'alipay',       //支付宝手机支付
        'alipay_wap',   //支付宝手机网页支付
//         'alipay_qr',    //支付宝扫码支付
//         'apple_pay',    //apple_pay，国外支付
//         'bfb',          //百度钱包移动快捷支付
//         'bfb_wap',      //百度钱包手机网页支付
//         'upacp',        //银联全渠道支付
//         'upacp_wap',    //银联全渠道手机网页支付
//         'upmp',         //银联手机支付
//         'upmp_wap',     //银联手机网页支付
         'wx',           //微信支付
         'wx_pub',       //微信公众账号支付
         'wx_pub_qr',     //微信公众账号扫码支付
         'wx_wap',       //微信H5
    );
    
    /*************ping++ 返回错误定义**************/
    public static $error_desc = [
        '16986' => 'charge_closed',
        '16987' => 'charge_unexpected_status',
        '16988' => 'refund_wait_operation',
        '16989' => 'refund_refused',
        '16990' => 'refund_retry',
        '16991' => 'refund_manual_intervention',
        '16992' => 'refund_unexpected_status',
        '16993' => 'channel_connection_error',
        '16994' => 'channel_request_error',
        '16995' => 'channel_parse_error',
        '16996' => 'channel_sign_error',
        '16997' => 'invalid_request_error',
        '16998' => 'api_error',
        '16999' => 'channel_error'
    ];
}