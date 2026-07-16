<?php

use EragLaravelDisposableEmail\Support\ResponseParser;

it('parses normalized domains from text', function () {
    $input = "Example.COM\nuser@test.dev\ninvalid\nexample.com\n";

    expect(ResponseParser::parse($input))->toBe(['example.com', 'test.dev']);
});

it('parses normalized domains from nested json', function () {
    $input = json_encode(['Example.COM', ['user@test.dev', 'invalid']], JSON_THROW_ON_ERROR);

    expect(ResponseParser::parse($input))->toBe(['example.com', 'test.dev']);
});
