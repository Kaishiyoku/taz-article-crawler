<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleTitleActivity;
use Illuminate\Console\Command;

class FetchNewArticleTitleActivities extends Command
{
    private const ARTICLE_TITLE_CSS_SELECTOR = 'h1[itemprop="headline"] > span:last-of-type';

    private const ARTICLE_SUB_TITLE_CSS_SELECTOR = 'h1[itemprop="headline"] > span.kicker';

    private const ARTICLE_DESCRIPTION_CSS_SELECTOR = 'p[itemprop="description"]';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:activities {article?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches all new article title activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $articleGuid = $this->argument('article');

        $articles = $articleGuid ? Article::whereGuid($articleGuid) : Article::all();

        $articles->each(function (Article $article) {
            $crawler = crawlUrl($article->url);

            $title = getNodeText(filterXPath(self::ARTICLE_TITLE_CSS_SELECTOR, $crawler));
            $subTitle = getNodeText(filterXPath(self::ARTICLE_SUB_TITLE_CSS_SELECTOR, $crawler));
            $description = getNodeText(filterXPath(self::ARTICLE_DESCRIPTION_CSS_SELECTOR, $crawler));

            // Only create a new activity when the title or subtitle differ the latest saved one
            $article->articleTitleActivities()
                ->take(1)
                ->whereTitle($title)
                ->whereSubTitle($subTitle)
                ->firstOr(function () use ($article, $title, $subTitle, $description) {
                    $articleTitleActivity = new ArticleTitleActivity();
                    $articleTitleActivity->title = $title;
                    $articleTitleActivity->sub_title = $subTitle;
                    $articleTitleActivity->description = $description;

                    $article->articleTitleActivities()->save($articleTitleActivity);

                    $this->line("New title activity saved for article {$article->url}");
                });
        });
    }
}
