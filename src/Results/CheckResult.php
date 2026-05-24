<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Results;

use JsonSerializable;

final readonly class CheckResult implements JsonSerializable
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public CheckStatus $status,
        public string $checkKey,
        public string $category,
        public string $title,
        public string $message,
        public array $details = [],
        public ?string $suggestion = null,
    ) {}

    /**
     * @param  array<string, mixed>  $details
     */
    public static function pass(
        string $checkKey,
        string $category,
        string $title,
        string $message,
        ?string $suggestion = null,
        array $details = [],
    ): self {
        return new self(CheckStatus::Pass, $checkKey, $category, $title, $message, $details, $suggestion);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function warning(
        string $checkKey,
        string $category,
        string $title,
        string $message,
        ?string $suggestion = null,
        array $details = [],
    ): self {
        return new self(CheckStatus::Warning, $checkKey, $category, $title, $message, $details, $suggestion);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function fail(
        string $checkKey,
        string $category,
        string $title,
        string $message,
        ?string $suggestion = null,
        array $details = [],
    ): self {
        return new self(CheckStatus::Fail, $checkKey, $category, $title, $message, $details, $suggestion);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function skipped(
        string $checkKey,
        string $category,
        string $title,
        string $message,
        ?string $suggestion = null,
        array $details = [],
    ): self {
        return new self(CheckStatus::Skipped, $checkKey, $category, $title, $message, $details, $suggestion);
    }

    /**
     * @return array{
     *     status: string,
     *     check_key: string,
     *     category: string,
     *     title: string,
     *     message: string,
     *     details: array<string, mixed>,
     *     suggestion: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'check_key' => $this->checkKey,
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'details' => $this->details,
            'suggestion' => $this->suggestion,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
