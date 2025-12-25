<?php
declare(strict_types=1);

namespace App\ContactMessage\Test\Interface\Sanitizer;

use App\ContactMessage\Interface\Sanitizer\CrudeSanitizer;
use PHPUnit\Framework\TestCase;

final class CrudeSanitizerTest extends TestCase
{
    private CrudeSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new CrudeSanitizer();
    }

    public function testAllowsLettersNumbersAndBasicPunctuation(): void
    {
        $input = "Abc Żółć 123\nTab\t.,!?;:'\"()[]{}-__@#$/%&*+=\\|/~`<>";
        $output = $this->sanitizer->sanitize($input);

        // Angle brackets are removed due to strip_tags, other punctuation should remain
        self::assertStringNotContainsString('<', $output);
        self::assertStringNotContainsString('>', $output);
        self::assertStringContainsString("Abc Żółć 123", $output);
        self::assertStringContainsString("Tab\t", $output);
        self::assertStringContainsString(".,!?;:'\"()[]{}-__@#$/%&*+=\\|/~`", $output);
    }

    public function testStripsControlCharsAndDisallowedSymbols(): void
    {
        // Include NUL, BEL, and some exotic symbols that are not in the allow-list (e.g., \u0000, \u0007, \u200B zero-width space)
        $input = "Good\x00Name\x07 with\u{200B} zero width and emoji 😀";
        $output = $this->sanitizer->sanitize($input);

        self::assertStringNotContainsString("\x00", $output);
        self::assertStringNotContainsString("\x07", $output);
        self::assertStringNotContainsString("\u{200B}", json_encode($output, JSON_THROW_ON_ERROR));
        // Emoji should be removed by the allow-list (not \p{L} or \p{N})
        self::assertStringNotContainsString('😀', $output);
        // Keeps the allowed letters and spaces
        self::assertStringContainsString('Good', $output);
        self::assertStringContainsString('Name', $output);
    }

    public function testStripsHtmlTagsAndKeepsInnerText(): void
    {
        $input = "<b>Hello</b> <script>alert('x')</script>World";
        $output = $this->sanitizer->sanitize($input);

        // strip_tags removes tags, the regex allows parentheses and quotes
        self::assertSame("Hello alert('x')World", $output);
    }

    public function testSanitizeAllMapsArray(): void
    {
        $input = [
            'fullName' => "John\r\nDoe",
            'email' => "john@example.com\x00",
            'message' => "Hi! 😀",
        ];

        $result = $this->sanitizer->sanitizeAll($input);

        self::assertSame("John\r\nDoe", $result['fullName']);
        self::assertSame('john@example.com', $result['email']);
        self::assertSame('Hi! ', $result['message']); // emoji removed
    }
}
