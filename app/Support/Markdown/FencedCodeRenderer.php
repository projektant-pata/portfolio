<?php

namespace App\Support\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders a fenced block as the design's `.blog-code` card: an optional
 * language bar over the `<pre>`. The info string is the label.
 */
final class FencedCodeRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        \assert($node instanceof FencedCode);

        $language = trim(explode(' ', $node->getInfo() ?? '', 2)[0]);

        $code = new HtmlElement('code', [], htmlspecialchars($node->getLiteral(), ENT_QUOTES, 'UTF-8'));
        $pre = new HtmlElement('pre', [], $code);

        $children = $language === ''
            ? [$pre]
            : [new HtmlElement('div', ['class' => 'blog-code-bar'], new HtmlElement('span', [], $language)), $pre];

        return new HtmlElement('div', ['class' => 'blog-code'], $children);
    }
}
