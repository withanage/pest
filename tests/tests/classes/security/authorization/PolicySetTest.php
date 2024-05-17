<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\security\authorization\AuthorizationPolicy;
use PKP\security\authorization\PolicySet;

test('policy set', function () {
    // Test combining algorithm and default effect.
    $policySet = new PolicySet();
    self::assertEquals(PolicySet::COMBINING_DENY_OVERRIDES, $policySet->getCombiningAlgorithm());
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_DENY, $policySet->getEffectIfNoPolicyApplies());
    $policySet = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);
    $policySet->setEffectIfNoPolicyApplies(AuthorizationPolicy::AUTHORIZATION_PERMIT);
    self::assertEquals(PolicySet::COMBINING_PERMIT_OVERRIDES, $policySet->getCombiningAlgorithm());
    self::assertEquals(AuthorizationPolicy::AUTHORIZATION_PERMIT, $policySet->getEffectIfNoPolicyApplies());

    // Test adding policies.
    $policySet->addPolicy($policy1 = new AuthorizationPolicy('policy1'));
    $policySet->addPolicy($policy2 = new AuthorizationPolicy('policy2'));
    $policySet->addPolicy($policy3 = new AuthorizationPolicy('policy3'), $addToTop = true);
    self::assertEquals([$policy3, $policy1, $policy2], $policySet->getPolicies());
});
