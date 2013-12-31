<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class FeedsController extends BaseController
{

    /*
     * Display feeds of users you follow
     */
    public function getIndex()
    {
        if (Auth::check() == FALSE) {
            return Redirect::to('gallery');
        }

        $images = DB::table('follow')
            ->select('images.slug','images.image_name','images.type','images.title','images.image_description','images.id as imgid','users.*')
            ->join('images', 'follow.follow_id', '=', 'images.user_id')
            ->join('users', 'follow.follow_id', '=', 'users.id')
            ->where('follow.user_id', '=', Auth::user()->id)
            ->where('images.deleted_at','=', NULL)
            ->where('images.approved','=',DB::raw(1))
            ->orderBy('images.created_at', 'desc')->paginate(perPage());

        return View::make('feeds/index')
            ->with('images', $images)
            ->with('title', 'Feeds');

    }
}