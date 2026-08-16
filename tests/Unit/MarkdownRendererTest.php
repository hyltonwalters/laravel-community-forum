<?php

namespace Tests\Unit;

use App\Support\MarkdownRenderer;
use PHPUnit\Framework\TestCase;

class MarkdownRendererTest extends TestCase
{
    public function test_it_renders_markdown_and_strips_raw_html()
    {
        $renderer = new MarkdownRenderer();

        $html = $renderer->render("# Hello\n\n<script>alert('x')</script>\n\n[unsafe](javascript:alert('x'))");

        $this->assertStringContainsString('<h1>Hello</h1>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }
}
