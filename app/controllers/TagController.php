<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class TagController extends BaseController
{
    public function getIndex($tag)
    {
        $images = Images::where('tags', 'LIKE', '%' . $tag . '%')->where('deleted_at', '=', NULL)->orderBy('created_at', 'desc')->with('user')->paginate(30);
        return View::make('gallery/index')
            ->with('images', $images)
            ->with('title', 'Tagged with ' . ucfirst($tag));
    }

    public function getRss($tag) {

        $images = Images::where('tags', 'LIKE', '%' . $tag . '%')->where('deleted_at', '=', NULL)->orderBy('created_at', 'desc')->with('user')->take(60)->get();
        $feed = Feed::make();
        $feed->title = siteSettings('siteName').'/tag/'.$tag;
        $feed->description = siteSettings('siteName').'/tag/'.$tag;
        $feed->link = URL::to('tag/'.$tag);
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