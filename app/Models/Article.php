<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Article
 *
 * @property int $guid
 * @property int $author_id
 * @property string $url
 * @property \Illuminate\Support\Carbon $posted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ArticleTitleActivity[] $articleTitleActivities
 * @property-read int|null $article_title_activities_count
 * @property-read \App\Models\Author $author
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article wherePostedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereUrl($value)
 * @mixin \Eloquent
 */
class Article extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'guid';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function articleTitleActivities()
    {
        return $this->hasMany(ArticleTitleActivity::class)->orderBy('created_at', 'desc');
    }
}
