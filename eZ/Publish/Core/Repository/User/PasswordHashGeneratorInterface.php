<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\User;

/**
 * @internal
 */
interface PasswordHashGeneratorInterface
{
    public function getHashType(): int;

    public function createPasswordHash(string $password, ?int $hashType = null): string;
}
