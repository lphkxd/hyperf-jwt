<?php
declare(strict_types=1);

namespace Mzh\JwtAuth\Aspect;

use App\Model\AuthGroup;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Mzh\JwtAuth\Annotations\AuthUpEvict;
use Mzh\JwtAuth\Jwt;

/**
 * 权限编辑。用户组编辑后更新权限验证缓存
 * @Aspect
 */
class AuthUpEvictAspect extends AbstractAspect
{
    public $annotations = [
        AuthUpEvict::class,
    ];

    /**
     * @Value("jwt.auth_prefix")
     * @var string
     */
    private $auth_prefix;

    /**
     * @Value("jwt.auth_log_prefix")
     * @var string
     */
    private $auth_log_prefix;

    /**
     * @Inject()
     * @var \Redis
     */
    protected $redis;

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        $result = $proceedingJoinPoint->process();
        //在更新之后处理
        if (isset($metadata->method[AuthUpEvict::class])) {
            /** @var AuthUpEvict $AuthUpEvict */
            $AuthUpEvict = $metadata->method[AuthUpEvict::class];
            $group_id = $AuthUpEvict->group;
            $list = AuthGroup::getGroupAuthUrl($group_id);
            //删除旧的
            //刷新新的进缓存
            foreach ($list as $groupId => $item) {
                $this->redis->del($this->auth_prefix . $groupId);
                $this->redis->hMSet($this->auth_prefix . $groupId, $item['menu']);
                $this->redis->hMSet($this->auth_log_prefix . $groupId, $item['log']);
            }
        }
        return $result;
    }
}
