<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class FollowController extends BaseController
{

    /**
     * User can follow other user
     * If already following then un-follow
     * Also sends notification to user
     * Via ajax
     */
    public function postFollow()
    {
        if (Auth::check() == FALSE) {
            return t('Login');
        }
        if (Auth::user()->id == Input::get('id')) {
            return t("Can't follow");
        }
        if (User::where('id', '=', Input::get('id'))->count() != 1) {
            return t("Can't follow");
        }
        if (Request::ajax()) {
            // Check if following
            // IF true then un-follow
            $isFollowing = Follow::where('user_id', '=', Auth::user()->id)
                ->where('follow_id', '=', Input::get('id'));
            if ($isFollowing->count() >= 1) {
                $isFollowing->delete();
                return t('Un-Followed');
            }
            $follow = new Follow();
            $follow->user_id = Auth::user()->id;
            $follow->follow_id = Input::get('id');
            $follow->save();
            // Send notice to user who is getting followed
            $notice = new Notification();
            $notice->user_id = Input::get('id');
            $notice->from_id = Auth::user()->id;
            $notice->type = 'follow';
            $notice->save();
            return t('Following');
        }
    }
}