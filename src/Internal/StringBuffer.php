<?php

namespace FumeApp\ModelTyper\Internal;

/**
 * A simple wrapper for collecting command output output
 */
class StringBuffer
{
    private string $content = '';

    private string $indent = '';

    public function __construct() {}

    public function write(string $content, bool $indent = true): self
    {
        $this->content .= (($indent && ! ctype_space($content)) ? $this->indent : null) . $content;

        return $this;
    }

    public function writeLn(?string $content = null): self
    {
        return $this->write($content . PHP_EOL);
    }

    public function prepend(string $content)
    {
        $this->content = $content . $this->content;
    }

    public function setIndentLevel(int $indent)
    {
        $this->indent = str_repeat(' ', $indent);
    }

    public function getIndent(): string
    {
        return $this->indent;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function trim(): self
    {
        $this->content = trim($this->content);

        return $this;
    }

    public function printLn(): string
    {
        return $this->content . PHP_EOL;
    }
}
