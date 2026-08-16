<?php

namespace App\Support;

use App\Models\Article;
use App\Support\Markdown\FencedCodeRenderer;
use App\Support\Markdown\FigureParagraphRenderer;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Renderer\HtmlDecorator;

/**
 * The article body pipeline. Four shapes the plain converter does not give
 * us, all required by `.blog-prose`: tables in a scroll container, fenced
 * code in a titled card, lone images as figures, `rel="noopener"` outbound.
 */
final class ArticleMarkdown
{
    public static function render(string $markdown): string
    {
        return (string) self::converter()->convert($markdown);
    }

    /**
     * Rendered body for one article in one locale. Keyed on `updated_at`, so
     * an edit invalidates itself and nothing has to remember to flush.
     */
    public static function renderArticle(Article $article, string $locale): string
    {
        $markdown = $article->getTranslation('content', $locale);

        if ($markdown === '') {
            return '';
        }

        $key = sprintf('article:%s:%s:%s', $article->id, $locale, $article->updated_at?->timestamp ?? 0);

        return Cache::rememberForever($key, fn () => self::render($markdown));
    }

    private static function converter(): MarkdownConverter
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        $environment = new Environment([
            'external_link' => [
                'internal_hosts' => $host,
                'open_in_new_window' => false,
                'noopener' => 'external',
                'noreferrer' => '',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $environment->addExtension(new ExternalLinkExtension);

        // Priority 10 beats the extensions' own renderers, which register at 0.
        $environment->addRenderer(Table::class, new HtmlDecorator(new TableRenderer, 'div', ['class' => 'blog-table']), 10);
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer, 10);
        $environment->addRenderer(Paragraph::class, new FigureParagraphRenderer, 10);

        return new MarkdownConverter($environment);
    }
}
