<?php

namespace App\Support\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * A paragraph holding nothing but one image becomes a `<figure>`, with the
 * image's alt text promoted to the caption — the handoff's rule for in-body
 * photographs. Every other paragraph renders as usual.
 */
final class FigureParagraphRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement|string
    {
        \assert($node instanceof Paragraph);

        $image = $this->loneImage($node);

        if (! $image instanceof Image) {
            return new HtmlElement('p', [], $childRenderer->renderNodes($node->children()));
        }

        $alt = $this->altText($image);

        $img = new HtmlElement('img', array_filter([
            'src' => $image->getUrl(),
            'alt' => $alt,
            'loading' => 'lazy',
        ], fn ($value) => $value !== null), '', true);

        $children = $alt === ''
            ? [$img]
            : [$img, new HtmlElement('figcaption', [], $alt)];

        return new HtmlElement('figure', [], $children);
    }

    private function loneImage(Paragraph $node): ?Image
    {
        $children = $node->children();

        if (count($children) !== 1) {
            return null;
        }

        return $children[0] instanceof Image ? $children[0] : null;
    }

    private function altText(Image $image): string
    {
        $text = '';

        foreach ($image->children() as $child) {
            if ($child instanceof Text) {
                $text .= $child->getLiteral();
            }
        }

        return trim($text);
    }
}
