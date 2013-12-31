<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class CommentController extends BaseController
{

    public function postDeleteComment()
    {

        $commentOwner = Comment::where('id', '=', Input::get('id'))->first();
        if (!$commentOwner) {
            exit;
        }
        $imageOwner = $commentOwner->image->user->id;

        if ($commentOwner->user_id == Auth::user()->id || Auth::user()->id == $imageOwner) {
            $commentOwner->delete();
            return 'success';
        }
    }

    public function postComment($id, $slug)
    {
        if (Auth::check() == false) {
            return Redirect::to('login')->with('flashNotice', t('Login'));
        }

        if (Request::is('image/*/*') == false) {
            return Redirect::to('gallery')->with('flashNotice', t('You are not allowed'));
        }

        $v = array(
            'comment' => array('required', 'min:2')
        );
        $v = Validator::make(Input::all(), $v);

        if ($v->fails()) return Redirect::to('image/' . $id . '/' . $slug)->withErrors($v);

        $image = Images::where('id', '=', $id);

        if ($image->count() != 1) {
            return Redirect::to('gallery')->with('flashError', t('You are not allowed'));
        }
        $comment = new Comment();
        $comment->user_id = Auth::user()->id;
        $comment->image_id = $id;
        $comment->comment = Input::get('comment');
        $comment->save();

        $imageUserId = $image->first()->user_id;
        if (Auth::user()->id != $imageUserId) {
            $notification = new Notification();
            $notification->user_id = $imageUserId;
            $notification->from_id = Auth::user()->id;
            $notification->type = 'comment';
            $notification->on_id = $id;
            $notification->save();
        }

        return Redirect::to('image/' . $id . '/' . $slug)->with('flashSuccess', t('Your comment is added'));
    }
}