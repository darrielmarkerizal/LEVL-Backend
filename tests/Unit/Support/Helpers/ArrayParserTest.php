<?php

use App\Support\Helpers\ArrayParser;

describe('ArrayParser', function () {
    describe('parseFilter', function () {
        it('returns array as-is', function () {
            $input = ['tag1', 'tag2', 'tag3'];
            $result = ArrayParser::parseFilter($input);

            expect($result)->toBe($input);
        });

        it('parses JSON string array', function () {
            $input = '["tag1","tag2","tag3"]';
            $result = ArrayParser::parseFilter($input);

            expect($result)->toBe(['tag1', 'tag2', 'tag3']);
        });

        it('parses URL-encoded JSON string', function () {
            $input = '%5B%22tag1%22%2C%22tag2%22%5D';
            $result = ArrayParser::parseFilter($input);

            expect($result)->toBe(['tag1', 'tag2']);
        });

        it('wraps single string value in array', function () {
            $input = 'single-tag';
            $result = ArrayParser::parseFilter($input);

            expect($result)->toBe(['single-tag']);
        });

        it('returns empty array for empty string', function () {
            $result = ArrayParser::parseFilter('');

            expect($result)->toBe([]);
        });

        it('returns empty array for whitespace string', function () {
            $result = ArrayParser::parseFilter('   ');

            expect($result)->toBe([]);
        });

        it('returns empty array for null', function () {
            $result = ArrayParser::parseFilter(null);

            expect($result)->toBe([]);
        });

        it('handles JSON array with spaces', function () {
            $input = '["tag 1", "tag 2"]';
            $result = ArrayParser::parseFilter($input);

            expect($result)->toBe(['tag 1', 'tag 2']);
        });
    });

    describe('parseCommaSeparated', function () {
        it('parses comma-separated string', function () {
            $input = 'tag1,tag2,tag3';
            $result = ArrayParser::parseCommaSeparated($input);

            expect($result)->toBe(['tag1', 'tag2', 'tag3']);
        });

        it('trims whitespace by default', function () {
            $input = 'tag1, tag2 , tag3';
            $result = ArrayParser::parseCommaSeparated($input);

            expect($result)->toBe(['tag1', 'tag2', 'tag3']);
        });

        it('preserves whitespace when trimming disabled', function () {
            $input = 'tag1, tag2 , tag3';
            $result = ArrayParser::parseCommaSeparated($input, trimValues: false);

            expect($result)->toBe(['tag1', ' tag2 ', ' tag3']);
        });

        it('returns array as-is', function () {
            $input = ['tag1', 'tag2'];
            $result = ArrayParser::parseCommaSeparated($input);

            expect($result)->toBe($input);
        });

        it('returns empty array for empty string', function () {
            $result = ArrayParser::parseCommaSeparated('');

            expect($result)->toBe([]);
        });
    });

    describe('ensureArray', function () {
        it('wraps scalar value in array', function () {
            expect(ArrayParser::ensureArray('value'))->toBe(['value']);
            expect(ArrayParser::ensureArray(123))->toBe([123]);
        });

        it('returns array as-is', function () {
            $input = ['value1', 'value2'];
            expect(ArrayParser::ensureArray($input))->toBe($input);
        });

        it('returns empty array for null', function () {
            expect(ArrayParser::ensureArray(null))->toBe([]);
        });

        it('returns empty array for empty string', function () {
            expect(ArrayParser::ensureArray(''))->toBe([]);
        });
    });

    describe('parsePipeSeparated', function () {
        it('parses pipe-separated string', function () {
            $input = 'option1|option2|option3';
            $result = ArrayParser::parsePipeSeparated($input);

            expect($result)->toBe(['option1', 'option2', 'option3']);
        });

        it('returns array as-is', function () {
            $input = ['option1', 'option2'];
            $result = ArrayParser::parsePipeSeparated($input);

            expect($result)->toBe($input);
        });

        it('returns empty array for empty string', function () {
            $result = ArrayParser::parsePipeSeparated('');

            expect($result)->toBe([]);
        });
    });
});
