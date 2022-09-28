<?php

namespace U9\DingtalkBot;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * Class DingtalkBot
 *
 * @package U9\DingtalkBot
 */
class DingtalkBot
{
    /**
     * @var array
     */
    private $config;

    /**
     * DingtalkBot constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('dingtalkbot');
    }

    /**
     * @param $configName
     *
     * @return $this
     */
    public function setConfig($configName)
    {
        $this->config = Config::get($configName);
        return $this;
    }

    /**
     * @param string $message
     * @param string $to
     * @param array $atMobiles
     * @param bool $isAtAll
     *
     * @return bool
     */
    public function send($message, $to = 'default', $atMobiles = [], $isAtAll = false)
    {
        $serverIP = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());

        if ($message instanceof \Exception) {
            $message = 'Message: ' . $message->getMessage() . PHP_EOL
                . 'File: ' . $message->getFile() . PHP_EOL
                . 'Line: ' . $message->getLine();
        }

        $message = '⚠️ ' . Carbon::now()->toDateTimeString() . PHP_EOL
            . 'ServerIP: ' . $serverIP . PHP_EOL
            . 'Project: ' . $this->config['project'] . PHP_EOL
            . $message . PHP_EOL
            . 'RequestID: ' . (defined("X_REQUEST_ID") ? X_REQUEST_ID : 'None');

        $webhook = $this->config['bot'][$to] ?? $this->config['bot']['default'];

        $content = json_encode([
            'msgtype' => 'text',
            'text' => [
                'content' => $message
            ],
            'at' => [
                'atMobiles' => $atMobiles,
                'isAtAll' => $isAtAll
            ],
        ]);

        $res = $this->curl($webhook, $content);

        if ($res['statusCode'] !== 200) {
            Log::error("DingTalk Bot Error! 消息发送失败, 通知群: {$to} 消息: {$message} 错误:" . json_encode($res));
            return false;
        }

        return true;
    }

    /**
     * 发送自定义消息
     *
     * @param $message
     * @param $to
     * @return bool
     */
    public function sendCustom($message, $to)
    {
        $webhook = array_key_exists($to, $this->config['bot']) ? $this->config['bot'][$to] : $this->config['bot']['default'];
        $content = json_encode(['msgtype' => 'text','text' => array ('content' => $message)]);
        $res = $this->curl($webhook, $content);

        if ($res['statusCode'] !== 200) {
            Log::error('DingTalk Bot Custom! 消息发送失败, 通知群:'.$to.' 消息:'.$message.' 错误:'.json_encode($res));
            return false;
        }
        return true;
    }

    /**
     * @param string $webhook
     * @param string $content
     *
     * @return array
     */
    private function curl($webhook, $content)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhook);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $return = [];
        $return['statusCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 500;
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $return['rawHeader'] = substr($response, 0, $headerSize);
        $return['rawBody'] = substr($response, $headerSize);
        $return['body'] = json_decode($return['rawBody'], true) ?: [];
        curl_close($ch);

        return $return;
    }
}
