<?php

uses(\PKP\tests\classes\security\authorization\PolicyTestCase::class);
use APP\core\Application;
use PHPUnit\Framework\MockObject\MockObject;
use PKP\security\authorization\AuthorizationDecisionManager;
use PKP\security\authorization\AuthorizationPolicy;
use PKP\security\authorization\PolicySet;


beforeEach(function () {
    $this->decisionManager = new AuthorizationDecisionManager();
});

test('decide if no policy applies', function () {
    // Mock a policy that doesn't apply.
    $mockPolicy = $this->getMockBuilder(AuthorizationPolicy::class)
        ->onlyMethods(['applies'])
        ->getMock();
    $mockPolicy->expects($this->any())
        ->method('applies')
        ->will($this->returnValue(false));
    $this->decisionManager->addPolicy($mockPolicy);

    // The default decision should be "deny".
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $this->decisionManager->decide());

    // Try a non-default decision.
    $this->decisionManager->setDecisionIfNoPolicyApplies(AuthorizationPolicy::AUTHORIZATION_PERMIT);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $this->decisionManager->decide());
});

test('authorization messages', function () {
    // Create policies that deny access.
    $denyPolicy1 = new AuthorizationPolicy('message 1');
    $denyPolicy2 = new AuthorizationPolicy('message 2');

    // Mock a policy that permits access.
    $permitPolicy = $this->getMockBuilder(AuthorizationPolicy::class)
        ->onlyMethods(['effect'])
        ->setConstructorArgs(['message 3'])
        ->getMock();
    $permitPolicy->expects($this->any())
        ->method('effect')
        ->will($this->returnValue(AuthorizationPolicy::AUTHORIZATION_PERMIT));

    // Create a permit overrides policy set to make sure that
    // all policies will be tested even if several deny access.
    $policySet = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);
    $policySet->addPolicy($denyPolicy1);
    $policySet->addPolicy($denyPolicy2);
    $policySet->addPolicy($permitPolicy);

    // Let the decision manager decide the policy set.
    $this->decisionManager->addPolicy($policySet);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $this->decisionManager->decide());

    // Check that the messages for the policies that denied access
    // can be retrieved from the decision manager.
    self::assertEquals(['message 1', 'message 2'], $this->decisionManager->getAuthorizationMessages());
});

test('authorization context', function () {
    // Create a test environment that can be used to
    // manipulate the authorization context.
    $this->decisionManager->addPolicy($this->getAuthorizationContextManipulationPolicy());

    // Make sure that the authorization context is initially empty.
    self::assertNull($this->decisionManager->getAuthorizedContextObject(Application::ASSOC_TYPE_USER_GROUP));

    // Check whether the authorized context is correctly returned from the policy.
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $this->decisionManager->decide());
    self::assertInstanceOf('PKP\userGroup\UserGroup', $this->decisionManager->getAuthorizedContextObject(Application::ASSOC_TYPE_USER_GROUP));
});

test('decide', function () {
    // We have to test policies and policy sets
    // as well as different combining algorithms.
    $denyPolicy = new AuthorizationPolicy();
    $permitPolicy = $this->getMockBuilder(AuthorizationPolicy::class)
        ->onlyMethods(['effect'])
        ->getMock();
    $permitPolicy->expects($this->any())
        ->method('effect')
        ->will($this->returnCallback($this->mockEffect(...)));

    // deny overrides
    // - permit policy
    // - deny policy
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($permitPolicy);
    $decisionManager->addPolicy($denyPolicy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $decisionManager->decide());

    // deny overrides
    // - permit policy
    // - permit policy
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($permitPolicy);
    $decisionManager->addPolicy($permitPolicy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $decisionManager->decide());

    // deny overrides
    // - permit policy
    // - allow overrides
    // -- deny policy
    // -- deny policy
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($permitPolicy);
    $policySet = new PolicySet();
    $policySet->addPolicy($denyPolicy);
    $policySet->addPolicy($denyPolicy);
    $decisionManager->addPolicy($policySet);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $decisionManager->decide());

    // deny overrides
    // - permit policy
    // - allow overrides
    // -- deny policy
    // -- permit policy
    $decisionManager = new AuthorizationDecisionManager();
    $decisionManager->addPolicy($permitPolicy);
    $policySet = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);
    $policySet->addPolicy($denyPolicy);
    $policySet->addPolicy($permitPolicy);
    $decisionManager->addPolicy($policySet);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $decisionManager->decide());
});

test('call on deny', function () {
    // Create a policy with a call-on-deny advice.
    /** @var AuthorizationPolicy|MockObject */
    $policy = $this->getMockBuilder(AuthorizationPolicy::class)
        ->addMethods(['callOnDeny'])
        ->getMock();
    $policy->expects($this->once())
        ->method('callOnDeny')
        ->will($this->returnCallback(mockCallOnDeny(...)));
    $callOnDenyAdvice = [
        $policy,
        'callOnDeny',
        ['argument']
    ];
    $policy->setAdvice(AuthorizationPolicy::AUTHORIZATION_ADVICE_CALL_ON_DENY, $callOnDenyAdvice);

    // Configure and execute the decision manager.
    $this->decisionManager->addPolicy($policy);
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $this->decisionManager->decide());
});

/**
 * Mock method used in testCallOnDeny() to test the
 * call-on-deny feature.
 *
 * @param string $argument
 */
function mockCallOnDeny($argument)
{
    // Test whether the argument was correctly passed
    // on to this method.
    self::assertEquals('argument', $argument);
}
