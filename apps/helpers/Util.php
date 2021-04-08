<?php

namespace Helpers;

final class Util
{

    /**
     * 以JSON输出
     * @param int $code
     * @param string $msg
     * @param string $data
     */
    public static function jsonReturn($code = 200, $msg = '', $data = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
        exit;
    }

    /**
     * 保存日志log
     * @param $msg
     * @param string $log_name
     * @param int $wraps
     * @param int $log_type
     */
    public static function logs($msg, $log_name = 'master.log', $wraps = 1, $log_type = 3)
    {
        $log_root = $_ENV['LOG_PATH'];
        is_dir($log_root) or mkdir($log_root);

        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }

        $_wrap = "\r\n";
        $wrap = $_wrap;
        if ($wraps > 1) {
            for ($i = 1; $i < $wraps; $i++) {
                $wrap .= $_wrap;
            }
        }

        error_log($msg . $wrap, $log_type, $log_root . '/' . $log_name);
    }
}
