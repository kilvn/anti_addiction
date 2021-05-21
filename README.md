# 网络游戏防沉迷实名认证系统 SDK For PHP

自用临时写的，现在分享出来，需要php7以上。

方法写在 `apps/api` 下， `apps/helpers` 为脚手架。

目前集成了 `zttp` `env` 扩展库，后期根据需求计划增加`mysql`扩展。

#### Install

```shell
composer update -vvv
cp env.example .env
```

项目入口 `public/index.php`

nginx 伪静态规则：

```
try_files $uri $uri/ /index.php$is_args$args;
```

#### Use

防沉迷系统官方文档地址：https://wlc.nppa.gov.cn/fcm_company/index.html

防沉迷系统的配置在 `.env` 文件中。

#### 接口说明

除了第三个接口传参有改变，前两个接口传参和官方接口相同，返回值都包裹在了本接口返回值 `data` 键下。

请求接口时只需要传`body`参数，`header`头和密文服务器都处理了。

本API返回值说明：

|   |   |
| ------------ | ------------ |
| 状态码|说明|
| 200|请求成功|
| 400|第三方API非成功返回值|
| 401|第三方API远程请求失败|
| 404|接口不存在|

0. 查询本地redis库，ai实名认证状态
   - POST
   - http://xxx.com/api/fcm/get_auth_status
   - 连接本地redis查询当前ai实名认证状态


1. 认证提交
    - POST
    - http://xxx.com/api/fcm/idcard_check


2. 认证查询
    - GET
    - https://xxx.com/api/fcm/idcard_query?ai=


3. 行为上报
    - POST
    - https://xxx.com/api/fcm/behavior

    - 传参需要注意：去除了多维数组格式，传参数时直接传后面的值
    ```js
    {'no': '','si': '',......}
    ```
