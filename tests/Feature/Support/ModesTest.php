<?php

use EragLaravelDisposableEmail\Support\Modes;

it('parses single and combined validation modes', function () {
    expect(Modes::parse(['rfc']))->toBe(['rfc'])
        ->and(Modes::parse([' rfc ', 'dns,spoof', 'rfc']))->toBe(['rfc', 'dns', 'spoof']);
});

it('rejects unsupported validation modes', function () {
    Modes::parse(['unknown']);
})->throws(InvalidArgumentException::class, 'Unsupported disposable email validation mode [unknown].');
