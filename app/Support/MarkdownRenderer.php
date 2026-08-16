<?php

namespace App\Support;

use League\CommonMark\CommonMarkConverter;

class MarkdownRenderer
{
    /** @var CommonMarkConverter */
    private $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function render(string $markdown): string
    {
        return (string) $this->converter->convert($markdown);
    }
}
