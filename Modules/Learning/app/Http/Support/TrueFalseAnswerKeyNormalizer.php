<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Support;

final class TrueFalseAnswerKeyNormalizer
{
    public static function normalize(mixed $answerKey): ?array
    {
        if (is_array($answerKey)) {
            if (count($answerKey) !== 1) {
                return null;
            }
            $v = $answerKey[0];
            if (in_array($v, [0, 1, '0', '1'], true)) {
                return [(int) $v];
            }
            if ($v === true || $v === false) {
                return [$v ? 0 : 1];
            }

            return null;
        }

        if (is_bool($answerKey)) {
            return [$answerKey ? 0 : 1];
        }

        if ($answerKey === 'true') {
            return [0];
        }

        if ($answerKey === 'false') {
            return [1];
        }

        if (in_array($answerKey, [0, 1, '0', '1'], true)) {
            return [(int) $answerKey];
        }

        return null;
    }
}
