<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdatePolicyEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\Policy */
    private $policy;

    /** @var \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct */
    private $policyUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\Policy */
    private $updatedPolicy;

    public function __construct(
        Policy $updatedPolicy,
        Policy $policy,
        PolicyUpdateStruct $policyUpdateStruct
    ) {
        $this->policy = $policy;
        $this->policyUpdateStruct = $policyUpdateStruct;
        $this->updatedPolicy = $updatedPolicy;
    }

    public function getPolicy(): Policy
    {
        return $this->policy;
    }

    public function getPolicyUpdateStruct(): PolicyUpdateStruct
    {
        return $this->policyUpdateStruct;
    }

    public function getUpdatedPolicy(): Policy
    {
        return $this->updatedPolicy;
    }
}
