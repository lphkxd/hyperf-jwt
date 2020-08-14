### 基于Hyperf(https://doc.hyperf.io/#/zh/README) 框架的 jwt 鉴权

<p align="center">
    <a href="https://github.com/lphkxd/hyperf-jwt/releases"><img src="https://poser.pugx.org/mzh/hyperf-jwt/v/stable" alt="Stable Version"></a>
    <a href="https://travis-ci.org/mzh/hyperf-jwt"><img src="https://travis-ci.org/mzh/hyperf-jwt.svg?branch=master" alt="Build Status"></a>
    <a href="https://packagist.org/packages/mzh/hyperf-jwt"><img src="https://poser.pugx.org/mzh/hyperf-jwt/downloads" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/mzh/hyperf-jwt"><img src="https://poser.pugx.org/mzh/hyperf-jwt/d/monthly" alt="Monthly Downloads"></a>
    <a href="https://www.php.net"><img src="https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000" alt="Php Version"></a>
    <a href="https://github.com/swoole/swoole-src"><img src="https://img.shields.io/badge/swoole-%3E=4.5-brightgreen.svg?maxAge=2592000" alt="Swoole Version"></a>
    <a href="https://github.com/lphkxd/hyperf-jwt/blob/master/LICENSE"><img src="https://img.shields.io/github/license/lphkxd/hyperf-jwt.svg?maxAge=2592000" alt=" License"></a>
</p>


### 思路来源与 (https://github.com/phper666/jwt-auth)组件
### 重构sso单点登录token失效逻辑
### 说明：

> `hyperf-jwt` 支持单点登录、多点登录、支持注销 token(token会失效)、支持refresh换取新token 失效老token  
  
> 单点登录：只会有一个 token 生效，一旦刷新 token ，前面生成的 token 都会失效，一般以用户 id 来做区分  
  
> 多点登录：token 不做限制
  
> 单点登录原理：token版本号，`JWT` 单点登录必须用到 aud（接收方） 默认字段，`aud` 字段的值默认为用户 id。当生成 token 时，会更新白名单uid的key值为当前的版本号，但是如果是调用 `refreshToken` 来刷新 token 或者调用 `logout` 注销token，默认前面生成的 token 都会失效。  
  如果开启单点登录模式，每次验证时候会查询当前uid的对应key是否和当前的版本号对应
  
> 多点登录原理：暂未实现

> token 不做限制原理：token 不做限制，在 token 有效的时间内都能使用


### 使用：
##### 1、安装依赖 
```shell
composer require mzh/hyperf-jwt
``` 

##### 2、发布配置
```shell
php bin/hyperf.php jwt:publish --config
```

##### 3、jwt配置
去配置 `config/autoload/jwt.php` 文件或者在配置文件 `.env` 里配置
```shell
# 务必改为你自己的字符串
JWT_SECRET=hyperf
#token过期时间，单位为秒
JWT_TTL=60
```
更多的配置请到 `config/autoload/jwt.php` 查看

##### 4、模拟登录获取token
```shell
<?php

namespace App\Controller;
use \Mzh\JwtAuth\Jwt;
class IndexController extends Controller
{
    # 模拟登录,获取token
    public function login(Jwt $jwt)
    {
           #用法1 对象模式

            $jwtData = new JwtBuilder();
            $jwtData->setIssuer('api');
            #... 设置更多token属性

            #... 设置data数据
            $jwtData->setJwtData(['uid' => 123,'type' => 1111,'group' => 1]);

            #返回 JwtBuilder对象
            $tokenObj = $jwt->createToken($jwtData);

            #获取生成的token 
            $tokenObj->getToken();  

            #用法2 传入数组 
            #返回 JwtBuilder对象
            $tokenObj = $jwt->createToken(['uid' => $id,'type' => $type,'group' => $group]);

            #获取生成的token 
            $tokenObj->getToken();  


          #获取刷新token 传入数组  第一个参数为数据，第二个参数为类型，默认是access 可以定义为 refersh 或者其他类型自定义
            #返回 JwtBuilder对象  
            $tokenObj = $jwt->createToken(['uid' => $id,'type' => $type,'group' => $group],Jwt::SCOPE_REFRESH);

            #获取生成的token 
            $tokenObj->getToken();  
       
        return $tokenObj->getToken();
    }
}
```
注意：支持传入用户对象获取 token，支持token类型，

##### 5、使用例子参考  https://github.com/lphkxd/hyperf-admin 


##### 6、建议
> 目前 `jwt` 抛出的异常目前有两种类型 `Mzh\JwtAuth\Exception\TokenValidException` 和 `Mzh\JwtAuth\Exception\JWTException,TokenValidException` 异常为 token 验证失败的异常，会抛出 `401` ,`JWTException` 异常会抛出 `500`，最好你们自己在项目异常重新返回错误信息
