<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class NotificationController extends BaseController
{
    /**
     * Display all notifications to a user,
     * notifications type check is done in view file
     * @return mixed
     */
    public function getIndex()
    {
        $notifications = Notification::where('user_id', '=', Auth::user()->id)->with('user', 'image')
            ->orderBy('created_at', 'desc')->paginate(30);

        foreach ($notifications as $notice) {
            if ($notice->is_read == '0') {
                $notice->is_read = 1;
                $notice->save();
            }
        }
        return View::make('notifications/index')
            ->with('notifications', $notifications)
            ->with('title', 'Notifications');
    }

}