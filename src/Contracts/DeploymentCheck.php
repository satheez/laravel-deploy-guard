<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Contracts;

use Satheez\DeployGuard\Results\CheckResult;

interface DeploymentCheck
{
    public function key(): string;

    public function category(): string;

    public function description(): string;

    public function run(): CheckResult;
}
