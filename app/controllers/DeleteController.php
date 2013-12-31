<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class DeleteController extends BaseController
{
    public function getDeleteImage($id)
    {
        $image = Images::where('id', '=', $id)->first();

        if ($image->user->id == Auth::user()->id) {
            File::delete('uploads/' . $image->image_name . '.' . $image->type);
            $image->delete();
            $image->comments()->delete();
            $image->favorite()->delete();
            Cache::forget('moreFromSite');
            return Redirect::to('gallery')->with('flashSuccess', t('Image is deleted permanently'));
        }

        return Redirect::to('gallery')->with('flashError', t('You are not allowed'));
    }


}