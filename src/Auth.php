<?php
declare(strict_types=1);

namespace Mzh\JwtAuth;

use Hyperf\Di\Annotation\Inject;
use Redis;

/**
 * PHP实现jwt
 */
class Auth
{
    const prefix = 'auth:';
    const prefixLogAuth = 'auth_log:';
}
