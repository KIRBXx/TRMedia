<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class ReplyController extends BaseController
{

    public function postDeleteReply()
    {
        if (Auth::check() == false) {
            return 'Login first';
        }
        $reply = Reply::where('id', '=', Input::get('id'))->first();
        $commentOwner = $reply->comment->user->id;
        $imageOwner = $reply->image->user->id;

        if ($reply->user_id == Auth::user()->id || $commentOwner == Auth::user()->id || $imageOwner == Auth::user()->id) {
            $reply->delete();
            return 'success';
        }
    }

    public function postReply()
    {
        if (Auth::check() == false) {
            return 'Login first';
        }
        $input = Input::all();
        $rules = array(
            'reply_msgid' => array('required', 'integer'),
            'textcontent' => array('required'),
        );
        $validate = Validator::make(Input::all(), $rules);
        if ($validate->fails()) {
            return 'Text is required';
        }
        // check if image exits;
        $image = Comment::where('id', '=', Input::get('reply_msgid'))->first();
        if (!$image) {
            return 'Not allowed';
        }
        $image = Images::where('id', '=', $image->image_id);
        if ($image->count() != 1) {
            return 'Not allowed';
        }
        $reply = new Reply();
        $reply->user_id = Auth::user()->id;
        $reply->image_id = $image->first()->id;
        $reply->comment_id = Input::get('reply_msgid');
        $reply->reply = Input::get('textcontent');
        $reply->save();

        // send notice to comment owner
        $commentOwnerId = $reply->comment->user->id;
        if (Auth::user()->id != $commentOwnerId) {
            $notification = new Notification();
            $notification->user_id = $commentOwnerId;
            $notification->from_id = Auth::user()->id;
            $notification->type = 'reply';
            $notification->on_id = $reply->comment->id;
            $notification->save();
        }

        // GET ALL REPLIER
        $all = Reply::where('comment_id', '=', Input::get('reply_msgid'))->get();
        $NoticeSendUsers = array();
        foreach ($all as $replyUser) {
            // Send notice only if not Comment Owner or Current logged in user and not already notice send
            if ($replyUser->user_id != Auth::user()->id AND $replyUser->user_id != $commentOwnerId && !in_array($replyUser->user_id, $NoticeSendUsers)) {

                $notification = new Notification();
                $notification->user_id = $replyUser->user_id;
                $notification->from_id = Auth::user()->id;
                $notification->type = 'reply';
                $notification->on_id = $reply->comment->id;
                $notification->save();

                $NoticeSendUsers[] = $replyUser->user_id;
            }
        }
        return '<div class="media"> <hr> <a class="pull-left bla" href="' . url('user/' . Auth::user()->username) . '"> <img class="media-object" src="' . avatar(Auth::user()->avatar, 75, 75) . '"> </a> <div class="media-body"> <h4 class="media-heading comment"><a href="' . url('user/' . Auth::user()->username) . '">' . ucfirst(Auth::user()->fullname) . '</a>    </h4> ' . Input::get('textcontent') . ' </div> </div>';
    }
}