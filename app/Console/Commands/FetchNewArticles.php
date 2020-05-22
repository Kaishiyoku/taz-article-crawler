<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Kaishiyoku\HeraRssCrawler\HeraRssCrawler;
use Kaishiyoku\HeraRssCrawler\Models\Rss\FeedItem;
use Symfony\Component\CssSelector\CssSelectorConverter;
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rssCrawler = new HeraRssCrawler();

        $feed = $rssCrawler->parseFeed(env('RSS_FEED_URL'));

        $feed->getFeedItems()->each(function (FeedItem $feedItem) {
            $this->line($feedItem->getPermalink());

            $guid = (int) filter_var($feedItem->getId(), FILTER_SANITIZE_NUMBER_INT);
            $url = env('BASE_URL') . '!' . $guid;

            $crawler = new Crawler(Http::get($url)->body());

            $selectorConverter = new CssSelectorConverter();

            $authorNameNode = $crawler->filterXPath($selectorConverter->toXPath(self::AUTHOR_NAME_CSS_SELECTOR));

            $author = $authorNameNode->count() === 0 ? Author::getDefaultAuthor() : Author::whereName($authorNameNode->text())->firstOr(function () use ($crawler, $selectorConverter, $authorNameNode, $feedItem, $url) {
                $jobTitleNode = $crawler->filterXPath($selectorConverter->toXPath(self::AUTHOR_JOB_TITLE_CSS_SELECTOR));

                $newAuthor = new Author();
                $newAuthor->name = $authorNameNode->text();
                $newAuthor->job_title = $jobTitleNode->count() === 0 ? null : defaultToNull($jobTitleNode->text());

                $authorUrl = env('BASE_URL') . ltrim($crawler->filterXPath($selectorConverter->toXPath(self::AUTHOR_LINK_CSS_SELECTOR))->attr('href'), '/');

                $authorCrawler = new Crawler(Http::get($authorUrl)->body());
                $authorDescriptionNode = $authorCrawler->filterXPath($selectorConverter->toXPath(self::AUTHOR_DESCRIPTION_CSS_SELECTOR));
                $newAuthor->description = $authorDescriptionNode->count() === 0 ? null : defaultToNull($authorDescriptionNode->text());

                $newAuthor->save();

                return $newAuthor;
            });

            if (Article::find($guid) === null) {
                $article = new Article();
                $article->guid = $guid;
                $article->author_id = $author->id;
                $article->url = $url;

                $article->save();
            }
        });
    }
}
