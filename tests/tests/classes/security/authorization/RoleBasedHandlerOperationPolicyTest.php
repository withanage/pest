<?php

uses(\PKP\tests\classes\security\authorization\PolicyTestCase::class);
use PKP\security\authorization\AuthorizationDecisionManager;
use PKP\security\authorization\AuthorizationPolicy;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\security\Role;


private const ROLE_ID_NON_AUTHORIZED = 0x7777;

test('role authorization', function () {
    // Construct the user roles array.
    $userRoles = [Role::ROLE_ID_SITE_ADMIN, self::ROLE_ID_TEST];

    // Test the user-group/role policy with a default
    // authorized request.
    $request = $this->getMockRequest('permittedOperation');
    $rolePolicy = new PolicySet(PolicySet::COMBINING_DENY_OVERRIDES);
    $rolePolicy->addPolicy($this->getAuthorizationContextManipulationPolicy());
    $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, [self::ROLE_ID_TEST], 'permittedOperation'));
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($rolePolicy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $decisionManager->decide());

    // Test the user-group/role policy with a non-authorized role.
    $rolePolicy = new PolicySet(PolicySet::COMBINING_DENY_OVERRIDES);
    $rolePolicy->addPolicy($this->getAuthorizationContextManipulationPolicy());
    $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, self::ROLE_ID_NON_AUTHORIZED, 'permittedOperation'));
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($rolePolicy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $decisionManager->decide());

    // Test the policy with an authorized role but a non-authorized operation.
    $request = $this->getMockRequest('privateOperation');
    $rolePolicy = new PolicySet(PolicySet::COMBINING_DENY_OVERRIDES);
    $rolePolicy->addPolicy($this->getAuthorizationContextManipulationPolicy());
    $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, Role::ROLE_ID_SITE_ADMIN, 'permittedOperation'));
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($rolePolicy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $decisionManager->decide());

    // Test the "all roles must match" feature.
    $request = $this->getMockRequest('permittedOperation');
    $rolePolicy = new PolicySet(PolicySet::COMBINING_DENY_OVERRIDES);
    $rolePolicy->addPolicy($this->getAuthorizationContextManipulationPolicy());
    $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, [Role::ROLE_ID_SITE_ADMIN, self::ROLE_ID_TEST], 'permittedOperation', 'some.message', true));
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($rolePolicy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $decisionManager->decide());

    // Test again the "all roles must match" feature but this time
    // with one role not matching.
    $rolePolicy = new PolicySet(PolicySet::COMBINING_DENY_OVERRIDES);
    $rolePolicy->addPolicy($this->getAuthorizationContextManipulationPolicy());
    $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, [self::ROLE_ID_TEST, Role::ROLE_ID_SITE_ADMIN, self::ROLE_ID_NON_AUTHORIZED], 'permittedOperation', 'some.message', true));
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($rolePolicy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $decisionManager->decide());
});
