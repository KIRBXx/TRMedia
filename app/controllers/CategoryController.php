<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */

class CategoryController extends BaseController
{
    /**
     * Display images posted in particular category,
     * check if category exits or not if not then redirect to to gallery
     * @param $category
     * @return mixed
     */
    public function getIndex($category)
    {
        if (DB::table('sitecategories')->where('slug', '=', Str::slug($category))->count() != 1) {
            return Redirect::to('gallery');
        }

        $images = Images::where('category', '=', $category)->where('deleted_at', '=', NULL)->orderBy('created_at', 'desc')->with('user')->paginate(perPage());
        return View::make('gallery/index')
            ->with('images', $images)
            ->with('title', 'Browsing ' . ucfirst($category) . ' Category');
    }

    /**
     * Generate RSS feed of each category
     * @param $category
     * @return mixed
     */
    public function getRss($category)
    {

        $images = Images::where('category', '=', $category)->where('deleted_at', '=', NULL)->orderBy('created_at', 'desc')->with('user')->take(60)->get();
        $feed = Feed::make();
        $feed->title = siteSettings('siteName') . '/category/' . $category;
        $feed->description = siteSettings('siteName') . '/category/' . $category;
        $feed->link = URL::to('category/' . $category);
        $feed->lang = 'en';
        foreach ($images as $post) {
            // set item's title, author, url, pubdate and description
            $desc = '<a href="' . url('image/' . $post->id . '/' . $post->slug) . '"><img src="' . asset(cropResize('uploads/' . $post->image_name . '.' . $post->type)) . '" /></a><br/><br/>
                <h2><a href="' . url('image/' . $post->id . '/' . $post->slug) . '">' . $post->title . '</a>
                by
                <a href="' . url('user/' . $post->user->username) . '">' . ucfirst($post->user->fullname) . '</a>
                ( <a href="' . url('user/' . $post->user->username) . '">' . $post->user->username . '</a> )
                </h2>' . $post->image_description;
            $feed->add(ucfirst($post->title), $post->user->fullname, URL::to('image/' . $post->id . '/' . $post->slug), $post->created_at, $desc);
        }

        return $feed->render('rss');

    }
}