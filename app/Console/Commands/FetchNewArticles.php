<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Kaishiyoku\HeraRssCrawler\HeraRssCrawler;
use Kaishiyoku\HeraRssCrawler\Models\Rss\FeedItem;
use Symfony\Component\DomCrawler\Crawler;

class FetchNewArticles extends Command
{
    private const AUTHOR_LINK_CSS_SELECTOR = 'div[itemprop="author"] > a[rel="author"].author';

    private const AUTHOR_NAME_CSS_SELECTOR = 'div[itemprop="author"] > a[rel="author"].author > h4[itemprop="name"]';

    private const AUTHOR_JOB_TITLE_CSS_SELECTOR = 'div[itemprop="author"] > a[rel="author"].author > h5[itemprop="jobTitle"]';

    private const AUTHOR_DESCRIPTION_CSS_SELECTOR = 'div.sect_profile-descr > p.sectbody';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:fetch_new_articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches all new articles via RSS feed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rssCrawler = new HeraRssCrawler();

        $feed = $rssCrawler->parseFeed(env('RSS_FEED_URL'));

        $newArticles = $feed->getFeedItems()->reduce(function (Collection $carry, FeedItem $feedItem) {
            $guid = (int) filter_var($feedItem->getId(), FILTER_SANITIZE_NUMBER_INT);
            $url = config('crawler.base_url') . '!' . $guid;

            $articleCrawler = new Crawler(Http::get($url)->body());

            $authorNameNode = $articleCrawler->filterXPath(toXPath(self::AUTHOR_NAME_CSS_SELECTOR));

            $author = $authorNameNode->count() === 0
                ? Author::getDefaultAuthor()
                : Author::whereName($authorNameNode->text())
                    ->firstOr($this->fetchNewAuthorFn($articleCrawler, $authorNameNode, $feedItem));

            if (Article::find($guid) === null) {
                $this->line("Adding new article {$feedItem->getPermalink()}");

                $article = new Article();
                $article->guid = $guid;
                $article->author_id = $author->id;
                $article->url = $url;

                $article->save();

                return $carry->merge([$article]);
            }

            return $carry;
        }, collect());

        $this->line("Added {$newArticles->count()} new articles.");
    }

    private function fetchNewAuthorFn(Crawler $articleCrawler, $authorNameNode, $feedItem): Closure
    {
        return function () use ($articleCrawler, $authorNameNode, $feedItem) {
            $jobTitleNode = $articleCrawler->filterXPath(toXPath(self::AUTHOR_JOB_TITLE_CSS_SELECTOR));

            $newAuthor = new Author();
            $newAuthor->name = $authorNameNode->text();
            $newAuthor->job_title = getNodeText($jobTitleNode);

            $authorUrl = env('BASE_URL') . ltrim($articleCrawler->filterXPath(toXPath(self::AUTHOR_LINK_CSS_SELECTOR))->attr('href'), '/');

            $authorCrawler = new Crawler(Http::get($authorUrl)->body());
            $authorDescriptionNode = $authorCrawler->filterXPath(toXPath(self::AUTHOR_DESCRIPTION_CSS_SELECTOR));

            $newAuthor->description = getNodeText($authorDescriptionNode);

            $newAuthor->save();

            return $newAuthor;
        };
    }
}
