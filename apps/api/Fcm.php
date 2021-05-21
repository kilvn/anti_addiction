<?php

use Zttp\Zttp;

use Helpers\Fcm as FcmUtil;
use Helpers\Util;

/**
 * 网络游戏防沉迷实名认证系统 后端接口
 * document uri: https://wlc.nppa.gov.cn/2021/02/25/16e2520acd9f4404897ed1a5b8fd1240.pdf
 * Class Fcm
 */
class Fcm extends Base
{
    protected static self $instance;
    protected string $env = 'dev';
    protected string $test_key = '';
    protected array $header_data = [];

    public function __construct()
    {
        parent::__construct();

        $this->timestamp *= 1000;
        $this->env = $_ENV['APP_ENV'];
        $this->header_data = [
            'Content-Type' => 'application/json; charset=utf-8',
            'appId' => $_ENV['FCM_APPID'],
            'bizId' => $_ENV['FCM_BIZID'],
            'timestamps' => $this->timestamp,
            'sign' => '',
        ];

        if ($this->env == 'dev' and isset($this->reqData['test_key'])) {
            $this->test_key = $this->reqData['test_key'];
        }
    }

    /**
     * Get the instance of self.
     *
     * @return self
     */
    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 实名认证提交
     */
    public function idcard_check()
    {
        $api_data = [
            'ai' => $this->reqData['ai'] ?? '',
            'name' => $this->reqData['name'] ?? '',
            'idNum' => $this->reqData['idNum'] ?? '',
        ];

        if (empty($api_data['ai'])) {
            Util::jsonReturn(400, '参数 ai 不能为空');
        }

        if (empty($api_data['name'])) {
            Util::jsonReturn(400, '参数 name 不能为空');
        }

        if (empty($api_data['idNum'])) {
            Util::jsonReturn(400, '参数 idNum 不能为空');
        }

        $api_key = $_ENV['FCM_IDCARD_CHECK_KEY'];
        if ($this->env == 'dev' and strlen($this->test_key)) {
            $api_key = $this->test_key;
        }
        $urls = [
            'production' => 'https://api.wlc.nppa.gov.cn/idcard/authentication/check',
            'dev' => 'https://wlc.nppa.gov.cn/test/authentication/check/' . $api_key,
        ];
        $body = FcmUtil::aesEncode($_ENV['FCM_APPSECRET'], $api_data);
        $this->header_data['sign'] = FcmUtil::createSign($_ENV['FCM_APPSECRET'], $this->header_data, $body);
//        dump($this->header_data);exit;

        $response = Zttp::timeout(60)->withHeaders($this->header_data)->post($urls[$this->env], $body);

        $status = $response->status();
        $is_ok = $response->isOk();

        if ($status != 200 or !$is_ok) {
            Util::jsonReturn(401, MSG_CODE[401]);
        }

        $json = $response->json();

        if ($json['errcode'] != 0) {
            Util::jsonReturn(400, MSG_CODE[400], $json);
        }

        Util::jsonReturn(200, MSG_CODE[200], $json['data']['result']);

//        {
//            "code": 200,
//            "msg": "request successed.",
//            "data": {
//                "status": 2,
//                "pi": null
//            }
//        }
    }

    /**
     * 实名认证查询
     */
    public function idcard_query()
    {
        $api_data = [
            'ai' => $this->reqData['ai'] ?? '',
        ];

        if (empty($api_data['ai'])) {
            Util::jsonReturn(400, '参数 ai 不能为空');
        }

        $api_key = $_ENV['FCM_IDCARD_QUERY_KEY'];
        if ($this->env == 'dev' and strlen($this->test_key)) {
            $api_key = $this->test_key;
        }
        $urls = [
            'production' => 'http://api2.wlc.nppa.gov.cn/idcard/authentication/query',
            'dev' => 'https://wlc.nppa.gov.cn/test/authentication/query/' . $api_key,
        ];
        unset($this->header_data['Content-Type']);
        $this->header_data['sign'] = FcmUtil::createSign($_ENV['FCM_APPSECRET'], array_merge($this->header_data, $api_data));

        $url = $urls[$this->env] . '?' . http_build_query($api_data);
        $response = Zttp::timeout(60)->withHeaders($this->header_data)->get($url);

        $status = $response->status();
        $is_ok = $response->isOk();

        if ($status != 200 or !$is_ok) {
            Util::jsonReturn(401, MSG_CODE[401]);
        }

        $json = $response->json();

        if ($json['errcode'] != 0) {
            Util::jsonReturn(400, MSG_CODE[400], $json);
        }

        Util::jsonReturn(200, MSG_CODE[200], $json['data']['result']);

//        {
//            "code": 400,
//            "msg": "Api business error.",
//            "data": {
//                "errcode": 2003,
//                "errmsg": "BUS AUTH CODE NO AUTH RECODE"
//            }
//        }
    }

    /**
     * 用户行为数据上报
     */
    public function behavior()
    {
        $api_data = [
            'collections' => [
                [
                    'no' => $this->reqData['no'] ?? '',
                    'si' => $this->reqData['si'] ?? '',
                    'bt' => $this->reqData['bt'] ?? '',
                    'ot' => $this->reqData['ot'] ?? $this->timestamp,
                    'ct' => $this->reqData['ct'] ?? '',
                    'di' => $this->reqData['di'] ?? '',
                    'pi' => $this->reqData['pi'] ?? '',
                ]
            ]
        ];

        $api_key = $_ENV['FCM_BEHAVIOR_KEY'];
        if ($this->env == 'dev' and strlen($this->test_key)) {
            $api_key = $this->test_key;
        }
        $urls = [
            'production' => 'http://api2.wlc.nppa.gov.cn/behavior/collection/loginout',
            'dev' => 'https://wlc.nppa.gov.cn/test/collection/loginout/' . $api_key,
        ];
        $body = FcmUtil::aesEncode($_ENV['FCM_APPSECRET'], $api_data);
        $this->header_data['sign'] = FcmUtil::createSign($_ENV['FCM_APPSECRET'], $this->header_data, $body);

        $response = Zttp::timeout(60)->withHeaders($this->header_data)->post($urls[$this->env], $body);

        $status = $response->status();
        $is_ok = $response->isOk();

        if ($status != 200 or !$is_ok) {
            Util::jsonReturn(401, MSG_CODE[401]);
        }

        $json = $response->json();

        if ($json['errcode'] != 0) {
            Util::jsonReturn(400, MSG_CODE[400], $json);
        }

        Util::jsonReturn(200, MSG_CODE[200], $json);

//        {
//            "code": 200,
//            "msg": "request successed.",
//            "data": null
//        }
    }
}
