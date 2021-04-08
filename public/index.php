<?php declare(strict_types=1);

header("Access-Control-Allow-Origin:*");

define('MSG_CODE', [
    200 => 'Request successed.',
    400 => 'Api business error.',
    401 => 'Third-party remote service request failed.',
    404 => 'Request module not found.',
]);
define('APP_ROOT', dirname(__DIR__));

try {
    // 引入第三方扩展库
    require APP_ROOT . '/vendor/autoload.php';

    // 简单的路由解析
    $raw = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
//    dump($raw);exit;

    $group = empty($raw[0]) ? 'api' : $raw[0];
    $module = empty($raw[1]) ? 'index' : $raw[1];
    $action = empty($raw[2]) ? 'index' : $raw[2];

    if ($group == 'favicon.ico') {
        echo '';
        exit;
    }

    if (stristr($action, '?')) {
        $action = explode('?', $action)[0];
    }

    // 初始化ENV对象
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();

    $class = ucwords($module);

    $autoload_func = function ($class) use ($group) {
        $class = str_replace('\\', '/', $class);
        require APP_ROOT . '/apps/' . $group . '/' . $class . '.php';
    };

    spl_autoload_register($autoload_func);

    $class_object = new $class();
    $methods = get_class_methods($class_object);
    if (!in_array($action, $methods)) {
        \Helpers\Util::jsonReturn(404, MSG_CODE[404]);
    }

    return $class_object->$action();
} catch (Exception $exception) {
    \Helpers\Util::logs('Exception line: ' . $exception->getLine() . ' Msg: ' . $exception->getMessage(), 'error.log', 2);
    \Helpers\Util::jsonReturn(500, $exception->getMessage());
}
