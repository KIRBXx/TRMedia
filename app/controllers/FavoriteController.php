<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class FavoriteController extends BaseController
{

    /*
     * Logged in user can make image it's favorites
     * also sends a notification to image owner
     * only if you yourself is not image owner
     */
    public function postFavorite()
    {
        if (Auth::check() == FALSE) {
            return 'Login First';
        }
        $v = array(
            'id' => array('required', 'integer')
        );
        $v = Validator::make(Input::all(), $v);
        // return error message if fails
        if ($v->fails()) {
            return 'Not Allowed';
        }

        $fav = Favorite::where('image_id', '=', Input::get('id'))->where('user_id', '=', Auth::user()->id);
        if ($fav->count() >= 1) {
            $fav->delete();
            return t('Un-Favorited');
        }

        $fav = new Favorite();
        $fav->user_id = Auth::user()->id;
        $fav->image_id = Input::get('id');
        $fav->save();

        // Send notification by finding image owner
        $user_id = Favorite::where('image_id', '=', Input::get('id'))->where('user_id', '=', Auth::user()->id)->first()->image->user_id;
        if (Auth::user()->id != $user_id) {
            $notice = new Notification();
            $notice->type = 'like';
            $notice->user_id = $user_id;
            $notice->from_id = Auth::user()->id;
            $notice->on_id = Input::get('id');
            $notice->save();
        }

        return t('Favorited');
    }


}