<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleTitleActivity;
use Illuminate\Console\Command;

class FetchNewArticleTitleActivities extends Command
{
    private const ARTICLE_TITLE_CSS_SELECTOR = 'h1[itemprop="headline"] > span:last-of-type';

    private const ARTICLE_SUB_TITLE_CSS_SELECTOR = 'h1[itemprop="headline"] > span.kicker';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:fetch_new_article_title_activities {article?}';

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

            // Only create a new activity when the title or subtitle differ the latest saved one
            $article->articleTitleActivities()
                ->take(1)
                ->whereTitle($title)
                ->whereSubTitle($subTitle)
                ->firstOr(function () use ($article, $title, $subTitle) {
                    $articleTitleActivity = new ArticleTitleActivity();
                    $articleTitleActivity->title = $title;
                    $articleTitleActivity->sub_title = $subTitle;

                    $article->articleTitleActivities()->save($articleTitleActivity);

                    $this->line("New title activity saved for article {$article->url}");
                });
        });
    }
}
