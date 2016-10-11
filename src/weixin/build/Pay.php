<?php namespace hdphp\weixin\build;
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
use hdphp\weixin\Weixin;
//微信支付
class pay extends Weixin {
	//统一下单返回结果
	protected $order = [ ];

	/**
	 * 公众号支付
	 * @param $order
	 * $data说明
	 * $data['total_fee']=1;//支付金额单位分
	 * $data['body']='会员充值';//商品描述
	 * $data['out_trade_no']='会员充值';//定单号
	 */
	public function jsapi( $order ) {
		//支付完成时
		if ( q( 'get.done' ) ) {
			//支付成功后根据配置文件设置的链接地址跳转到成功页面
			echo "<script>location.replace('" . c( 'weixin.back_url' ) . "&code=SUCCESS')</script>";
			exit;
		} else {
			$res = $this->unifiedorder( $order );
			if ( $res['return_code'] != 'SUCCESS' ) {
				message( $res['return_msg'], c( 'weixin.back_url' ) . '&code=fail', 'error' );
			}
			if ( ! isset( $res['result_code'] ) || $res['result_code'] != 'SUCCESS' ) {
				message( $res['err_code_des'], c( 'weixin.back_url' ) . '&code=fail', 'error' );
			}
			$data['appId']     = c( 'weixin.appid' );
			$data['timeStamp'] = time();
			$data['nonceStr']  = $this->getRandStr( 16 );
			$data['package']   = "prepay_id=" . $res['prepay_id'];
			$data['signType']  = "MD5";
			$data['paySign']   = $this->makeSign( $data );
			$js
			                   = <<<sttr
<script>
    function onBridgeReady() {
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', {
                "appId": "{$data['appId']}",     //公众号名称，由商户传入
                "timeStamp": "{$data['timeStamp']}",//时间戳，自1970年以来的秒数
                "nonceStr": "{$data['nonceStr']}", //随机串
                "package": "{$data['package']}",
                "signType": "{$data['signType']}",         //微信签名方式：
                "paySign": "{$data['paySign']}" //微信签名
            },
            function (res) {
                if (res.err_msg == "get_brand_wcpay_request:ok") {
                    location.search += '&done=1';
                } else {
                    //alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
                    history.go(-1);
                }
            }
        );
    }
    if (typeof WeixinJSBridge == "undefined") {
        if (document.addEventListener) {
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        } else if (document.attachEvent) {
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    } else {
        onBridgeReady();
    }
</script>
sttr;
			die( $js );
		}
	}

	//统一下单
	protected function unifiedorder( $data ) {
		$data['appid']      = c( 'weixin.appid' );
		$data['mch_id']     = c( 'weixin.mch_id' );
		$data['notify_url'] = c( 'weixin.notify_url' );
		$data['nonce_str']  = $this->getRandStr( 16 );
		$data['trade_type'] = 'JSAPI';
		$data['openid']     = Weixin::instance( 'oauth' )->snsapiBase();
		$data['sign']       = $this->makeSign( $data );
		$xml                = Xml::toSimpleXml( $data );
		$res                = Curl::post( "https://api.mch.weixin.qq.com/pay/unifiedorder", $xml );

		return Xml::toSimpleArray( $res );
	}

	//支付成功后的通知信息
	public function getNotifyMessage() {
		return Xml::toSimpleArray( $GLOBALS['HTTP_RAW_POST_DATA'] );
	}
}