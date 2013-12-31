<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class MostController extends BaseController
{
    public function mostCommented()
    {
        $images = Images::join('comments', 'comments.image_id', '=', 'images.id')
            ->select('images.*', DB::raw('count(comments.image_id) as comments'))
            ->groupBy('images.id')->with('user')->orderBy('comments', 'desc')->paginate(perPage());

        return View::make('gallery/index')
            ->with('images', $images)
            ->with('title', t('Most Commented'));
    }

    public function mostFavorited()
    {
        $images = Images::join('favorite', 'favorite.image_id', '=', 'images.id')
            ->select('images.*', DB::raw('count(favorite.image_id) as comments'))
            ->groupBy('images.id')->with('user')->orderBy('comments', 'desc')->paginate(perPage());

        return View::make('gallery/index')
            ->with('images', $images)
            ->with('title', t('Most Favorites'));
    }

    public function mostDownloaded()
    {
        $images = Images::where('approved', '=', 1)->orderBy('downloads', 'desc')->with('user', 'comments', 'favorite')->paginate(perPage());
        return View::make('gallery/index')
            ->with('images', $images)
            ->with('title', t('Most Downloads'));
    }
}