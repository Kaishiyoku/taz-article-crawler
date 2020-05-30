<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ArticleTitleActivity
 *
 * @property int $id
 * @property int $article_guid
 * @property string $title
 * @property string $sub_title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Article $article
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity whereArticleGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ArticleTitleActivity whereTitle($value)
 * @mixin \Eloquent
 */
class ArticleTitleActivity extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['*'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'posted_at',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
