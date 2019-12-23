<?php

namespace Mzh\JwtAuth;

use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\Inject;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class WhiteList extends AbstractAnnotation
{

    /**
     * @Value("jwt.login_type")
     * @var string
     */
    protected $loginType;

    /**
     * @Value("jwt.sso_key")
     * @var string
     */
    protected $ssoKey;

    /**
     * @Inject
     * @var CacheInterface
     */
    public $storage;

    /**
     * 是否有效已经加入黑名单
     * @param array $payload
     * @return bool
     */
    public function effective(array $payload)
    {
        switch (true) {
            case ($this->loginType == 'mpop'):
                return true;
            case ($this->loginType == 'sso'):
                try {
                    $val = $this->storage->get($payload['scope'] . ":" . $payload['aud']);
                } catch (InvalidArgumentException $e) {
                    return false;
                }
                // 这里为什么要大于等于0，因为在刷新token时，缓存时间跟签发时间可能一致，详细请看刷新token方法
                return $payload['jti'] == $val;
            default:
                return false;
        }
    }

    /**
     * token 失效
     * @param $uid
     * @param $version
     * @param string $type
     * @return bool
     */
    public function add($uid, $version, $type = Jwt::SCOPE_TOKEN)
    {
        try {
            $this->storage->set($type . ":" . $uid, $version);
        } catch (InvalidArgumentException $e) {
        }
        return true;
    }


    /**
     * token 失效
     * @param $uid
     * @param string $type
     * @return bool
     */
    public function refreshToken($uid)
    {
        try {
            $this->storage->delete(Jwt::SCOPE_TOKEN . ":" . $uid);
            $this->storage->delete(Jwt::SCOPE_REFRESH . ":" . $uid);
        } catch (InvalidArgumentException $e) {
        }
        return true;
    }


    /**
     * token 失效
     * @param $uid
     * @param string $type
     * @return bool
     */
    public function remove($uid, $type = Jwt::SCOPE_TOKEN)
    {
        try {
            return $this->storage->set($type . ":" . $uid, 0, 7200);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
