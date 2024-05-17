<?php

uses(\PKP\tests\classes\security\authorization\PolicyTestCase::class);
use PKP\security\authorization\AuthorizationPolicy;
use PKP\security\authorization\PKPPublicAccessPolicy;


test('p k p public access policy', function () {
    // Mock a request to the permitted operation.
    $request = $this->getMockRequest('permittedOperation');

    // Instantiate the policy.
    $policy = new PKPPublicAccessPolicy($request, 'permittedOperation');

    // Test default message.
    self::assertEquals('user.authorization.privateOperation', $policy->getAdvice(AuthorizationPolicy::AUTHORIZATION_ADVICE_DENY_MESSAGE));

    // Test getters.
    self::assertEquals($request, $policy->getRequest());
    self::assertEquals(['permittedOperation'], $policy->getOperations());

    // Test the effect with a public operation.
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $policy->effect());

    // Test the effect with a private operation
    $request = $this->getMockRequest('privateOperation');
    $policy = new PKPPublicAccessPolicy($request, 'permittedOperation');
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $policy->effect());
});
