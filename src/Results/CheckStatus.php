<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Results;

enum CheckStatus: string
{
    case Pass = 'pass';
    case Warning = 'warning';
    case Fail = 'fail';
    case Skipped = 'skipped';
}
