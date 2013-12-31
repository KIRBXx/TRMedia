<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class UserController extends BaseController
{

    public function getUser($user)
    {

        $user = User::where('username', '=', $user)->with('followers.user')->first();
        if (!$user) {
            return Redirect::to('gallery');
        }
        $images = Images::where('approved', '=', 1)->where('user_id', '=', $user->id)->with('comments','favorite')->orderBy('created_at','desc')->paginate(perPage());
        $user->numberOfComments = $user->comments()->count();
        $user->numberOfImages = $user->images()->where('approved', '=', 1)->count();
        $tags = implode(',', $user->images()->lists('tags'));
        $words = str_word_count($tags, 1);
        $freq = array();
        $i = 0;
        foreach ($words as $w) {
            if (preg_match_all('/' . preg_quote($w, '/') . '/', $tags, $m)) {
                $freq[$w] = count($m[0]);
            }
            if ($i == 10) {
                break;
            }
            $i++;
        }
        arsort($freq);

        return View::make('user/index')
            ->with('title', $user->username)
            ->with('images', $images)
            ->with('mostUsedTags', $freq)
            ->with('user', $user);

    }


    public function getFavorites($user)
    {
        $user = User::where('username', '=', $user)->with('favorites.image.user')->first();
        $user->numberOfComments = $user->comments()->count();
        $user->numberOfImages = $user->images()->count();
        $tags = implode(',', $user->images()->lists('tags'));
        $words = str_word_count($tags, 1);
        $freq = array();
        $i = 0;
        foreach ($words as $w) {
            if (preg_match_all('/' . preg_quote($w, '/') . '/', $tags, $m)) {
                $freq[$w] = count($m[0]);
            }
            if ($i == 10) {
                break;
            }
            $i++;
        }
        arsort($freq);

        return View::make('user/favorites')
            ->with('title', $user->fullname)
            ->with('mostUsedTags', $freq)
            ->with('user', $user);

    }

    public function getFollowers($username)
    {
        $user = User::where('username', '=', $username)->with('followers')->first();
        if (!$user) {
            return Redirect::to('/');
        }
        $user->numberOfComments = $user->comments()->count();
        $user->numberOfImages = $user->images()->count();
        $tags = implode(',', $user->images()->lists('tags'));
        $words = str_word_count($tags, 1);
        $freq = array();
        $i = 0;
        foreach ($words as $w) {
            if (preg_match_all('/' . preg_quote($w, '/') . '/', $tags, $m)) {
                $freq[$w] = count($m[0]);
            }
            if ($i == 10) {
                break;
            }
            $i++;
        }
        arsort($freq);

        return View::make('user/followers')
            ->with('title', $user->fullname)
            ->with('mostUsedTags', $freq)
            ->with('user', $user);
    }

    public function getFollowing($username)
    {
        $user = User::where('username', '=', $username)->with('following.followingUser')->first();
        if (!$user) {
            return Redirect::to('/');
        }
        if ($user->id != Auth::user()->id) {
            return Redirect::to('/');
        }
        $user->numberOfComments = $user->comments()->count();
        $user->numberOfImages = $user->images()->count();
        $tags = implode(',', $user->images()->lists('tags'));
        $words = str_word_count($tags, 1);
        $freq = array();
        $i = 0;
        foreach ($words as $w) {
            if (preg_match_all('/' . preg_quote($w, '/') . '/', $tags, $m)) {
                $freq[$w] = count($m[0]);
            }
            if ($i == 10) {
                break;
            }
            $i++;
        }
        arsort($freq);

        return View::make('user/following')
            ->with('title', $user->fullname)
            ->with('mostUsedTags', $freq)
            ->with('user', $user);
    }

    public function getRss($user)
    {
        $user = User::where('username', '=', $user)->with('followers.user')->first();
        $images = Images::where('approved', '=', 1)->where('user_id', '=', $user->id)->paginate(perPage());

        $feed = Feed::make();

        $feed->title = siteSettings('siteName') . '/user/' . $user->username;
        $feed->description = siteSettings('siteName') . '/user/' . $user->username;
        $feed->link = URL::to('user/' . $user->username);
        $feed->lang = 'en';

        foreach ($images as $post) {
            // set item's title, author, url, pubdate and description
            $desc = '<a href="' . url('image/' . $post->id . '/' . $post->slug) . '"><img src="' . asset(cropResize('uploads/' . $post->image_name . '.' . $post->type)) . '" /></a><br/><br/>
                <h2><a href="' . url('image/' . $post->id . '/' . $post->slug) . '">' . $post->title . '</a>
                by
                <a href="' . url('user/' . $user->username) . '">' . ucfirst($user->fullname) . '</a>
                ( <a href="' . url('user/' . $user->username) . '">' . $user->username . '</a> )
                </h2>' . $post->image_description;
            $feed->add(ucfirst($post->title), $user->fullname, URL::to('image/' . $post->id . '/' . $post->slug), $post->created_at, $desc);
        }

        return $feed->render('rss');
    }

    public function getAll()
    {
        $users = User::where('confirmed', '=', 1)->with('latestImages', 'comments')->paginate(perPage());
        return View::make('user/users')
            ->with('users', $users)
            ->with('title', 'Members');
    }
}