<?php

test('whitelist has precedence over blacklist ', function () {
    $emailBlacklistedDomain = custom_blacklisted_email();

    // Adding an email from blacklisted domain to the whitelist
    config()->set('disposable-email.whitelist', [$emailBlacklistedDomain]);

    // Validator should NOT flag it as disposable email
    expect(validator(['email' => $emailBlacklistedDomain], ['email' => ['disposable_email']]))
        ->fails()
        ->toBeFalse();

    // Empty the whitelist set
    config()->set('disposable-email.whitelist', []);

    // Validator should flag this email as disposable
    expect(validator(['email' => $emailBlacklistedDomain], ['email' => ['disposable_email']]))
        ->fails()
        ->toBetrue();
});
