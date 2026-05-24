<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Support;

final readonly class CheckContext
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        public string $environment,
        public bool $ci,
        public bool $json,
        public array $config,
    ) {}
}
