<?php

namespace Tanto\Tanto;

use Parsedown;
use Mni\FrontYAML\Parser;
use Tanto\Tanto\Contracts\Markdown as MarkdownContract;

class Markdown implements MarkdownContract
{
    protected $parsedownParser;
    protected $yamlParser;

    public function __construct(Parsedown $parsedownParser, Parser $yamlParser)
    {
        $this->parsedownParser = $parsedownParser;
        $this->yamlParser = $yamlParser;
    }

    /**
     * Parse markdown
     *
     * @param $text
     *
     * @return string
     */
    public function parse($text)
    {
        return $this->parsedownParser->text(
            $this->cleanLeadingSpace($text)
        );
    }

    /**
     * Parse markdown with YAML headers
     *
     * This method returns an array of: content as the first member and
     * YAML values as the second member.
     *
     * @param string $text
     *
     * @return array
     */
    public function parseWithYAML($text)
    {
        $parsed = $this->yamlParser->parse($text);

        return [$parsed->getContent(), $parsed->getYAML()];
    }

    /**
     * Remove initial leading space from each line
     *
     * Since @markdown can be placed inside any HTML element, there might
     * be leading space due to code editor indentation, here we trim it
     * to avoid compiling the whole markdown block as a code block.
     *
     * @param $text
     *
     * @return string
     */
    public function cleanLeadingSpace($text)
    {
        $i = 0;

        while (! $firstLine = explode("\n", $text)[$i]) {
            $i ++;
        }

        preg_match('/^( *)/', $firstLine, $matches);

        return preg_replace('/^[ ]{'.strlen($matches[1]).'}/m', '', $text);
    }
}