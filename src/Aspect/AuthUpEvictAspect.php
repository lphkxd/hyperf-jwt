<?php
declare(strict_types=1);

namespace Mzh\JwtAuth\Aspect;

use App\Model\AuthGroup;
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
            $keys = $this->redis->keys(Jwt::PREFIX . "*") ?? [];
            $this->redis->del($keys);

            //刷新新的进缓存
            foreach ($list as $groupId => $item) {
                $this->redis->hMSet(Jwt::PREFIX . $groupId, $item['menu']);
                $this->redis->hMSet(Jwt::PREFIX_LOG . $groupId, $item['log']);
            }
        }
        return $result;
    }
}
