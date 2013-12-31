<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class DownloadController extends BaseController
{
    public function getDownload($id, $slug = '')
    {
        if (Auth::check() == false) {
            return Redirect::to('login')->with('flashError', t('You need to login to download images'));
        }

        $id = Crypt::decrypt($id);
        $slug = Crypt::decrypt($slug);
        $image = Images::where('id', '=', $id)->where('slug', '=', $slug)->where('deleted_at', '=', NULL);
        if ($image->count() != 1) {
            return Redirect::to('gallery')->with('flashError', t('You are not allowed to download this image'));
        }
        $image = $image->first();
        if(siteSettings('allowDownloadOriginal') == 'leaveToUser' AND $image->allow_download != '1') {
            return Redirect::to('gallery')->with('flashError', t('You are not allowed to download this image'));
        }
        if (Auth::user()->id != $image->user_id) {
            $image->downloads = $image->downloads + 1;
            $image->save();
        }
        return Response::download('uploads/' . $image->image_name . '.' . $image->type, $image->slug . '.' . $image->type, array('content-type' => 'image/jpg'));
    }


}