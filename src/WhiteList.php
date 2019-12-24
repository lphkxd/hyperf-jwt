<?php

namespace Mzh\JwtAuth;

use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\Inject;

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
     * @Value("jwt.cache_prefix")
     * @var string
     */
    private $cache_prefix;

    /**
     * @Inject()
     * @var \Redis
     */
    protected $redis;

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
                $val = $this->redis->get($this->cache_prefix . $payload['scope'] . ":" . $payload['aud']);
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
        return $this->redis->set($this->cache_prefix . $type . ":" . $uid, $version);
    }

    /**
     * token 失效
     * @param $uid
     * @param string $type
     * @return bool
     */
    public function remove($uid, $type = Jwt::SCOPE_TOKEN)
    {
        return $this->redis->set($this->cache_prefix . $type . ":" . $uid, 0, 7200);
    }
}
