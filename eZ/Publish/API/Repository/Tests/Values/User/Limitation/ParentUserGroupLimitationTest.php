<?php

/**
 * File containing the ParentUserGroupLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation
 * @group integration
 * @group limitation
 */
class ParentUserGroupLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a ParentUserGroupLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation
     */
    public function testParentUserGroupLimitationAllow()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();

        $parentUserGroupId = $this->generateId('location', 4);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        $currentUser = $userService->loadUser(
            $permissionResolver->getCurrentUserReference()->getUserId()
        );

        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-GB');
        $userGroupCreate->setField('name', 'Shared wiki');

        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $userService->loadUserGroup(
                $parentUserGroupId
            )
        );

        // Assign system user and example user to same group
        $userService->assignUserToUserGroup($user, $userGroup);
        $userService->assignUserToUserGroup($currentUser, $userGroup);

        $roleService = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentUserGroupLimitation(
                [
                    'limitationValues' => [true],
                ]
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier('Editor'),
            $policyCreate
        );

        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct('content', 'read')
        );

        $roleService->assignRoleToUserGroup($role, $userGroup);

        $permissionResolver->setCurrentUserReference($user);

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue('title')->text
        );
    }

    /**
     * Tests a ParentUserGroupLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation
     */
    public function testParentUserGroupLimitationForbid()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();

        $parentUserGroupId = $this->generateId('location', 4);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-GB');
        $userGroupCreate->setField('name', 'Shared wiki');

        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $userService->loadUserGroup(
                $parentUserGroupId
            )
        );

        // Assign only example user to new group
        $userService->assignUserToUserGroup($user, $userGroup);

        $roleService = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentUserGroupLimitation(
                [
                    'limitationValues' => [true],
                ]
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier('Editor'),
            $policyCreate
        );

        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct('content', 'read')
        );

        $roleService->assignRoleToUserGroup($role, $userGroup);

        $permissionResolver->setCurrentUserReference($user);

        $this->createWikiPageDraft();
        /* END: Use Case */
    }
}
