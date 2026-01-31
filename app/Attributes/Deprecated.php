<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;

/**
 * Deprecated Attribute
 * 
 * Marks classes, methods, properties, or constants as deprecated.
 * Provides structured deprecation information including GUID tracking,
 * version information, and migration guidance.
 * 
 * @see https://www.php.net/manual/en/language.attributes.overview.php
 * 
 * Example usage:
 * ```php
 * #[Deprecated(
 *     guid: 'DEP-2024-001',
 *     message: 'Use the new implementation instead',
 *     since: 'v1.0.0',
 *     alternative: 'NewClass::newMethod()'
 * )]
 * public function oldMethod(): void
 * {
 *     // deprecated implementation
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS_CONSTANT)]
class Deprecated
{
    /**
     * Create a new Deprecated attribute instance.
     *
     * @param string $guid Unique identifier for tracking this deprecation (e.g., 'DEP-2024-001')
     * @param string $message Description of why this is deprecated
     * @param string|null $since Version or date when this was deprecated (e.g., 'v1.0.0', '2024-01-30')
     * @param string|null $alternative Recommended alternative to use instead
     * @param string|null $removeIn Version when this will be removed (e.g., 'v2.0.0')
     */
    public function __construct(
        public readonly string $guid,
        public readonly string $message,
        public readonly ?string $since = null,
        public readonly ?string $alternative = null,
        public readonly ?string $removeIn = null,
    ) {
    }

    /**
     * Get a formatted deprecation message.
     *
     * @return string
     */
    public function getFormattedMessage(): string
    {
        $parts = [];
        
        $parts[] = "DEPRECATED [{$this->guid}]";
        
        if ($this->since) {
            $parts[] = "since {$this->since}";
        }
        
        $parts[] = "- {$this->message}";
        
        if ($this->alternative) {
            $parts[] = "Use {$this->alternative} instead.";
        }
        
        if ($this->removeIn) {
            $parts[] = "Will be removed in {$this->removeIn}.";
        }
        
        return implode(' ', $parts);
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'guid' => $this->guid,
            'message' => $this->message,
            'since' => $this->since,
            'alternative' => $this->alternative,
            'removeIn' => $this->removeIn,
            'formattedMessage' => $this->getFormattedMessage(),
        ];
    }
}
