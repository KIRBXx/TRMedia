<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class ImageController extends BaseController
{

    /**
     * Display all details of image with it's comments
     * and replies send it view file.
     * @param $id
     * @param null $slug
     * @return mixed
     */
    public function getIndex($id, $slug = NULL)
    {
        $image_get = Images::find($id);
        if (!$image_get) {
            return Redirect::to('/');
        }
        if (empty($slug)) {
            return Redirect::to('image/' . $image_get->id . '/' . $image_get->slug);
        }
        if ($slug != $image_get->slug) {
            return Redirect::to('image/' . $image_get->id . '/' . $image_get->slug);
        }
        if ($image_get->approved == 0) {
            return Redirect::to('/');
        }
        $comments = $image_get->comments()->with('user')->orderBy('created_at', 'desc');
        $commentsCount = $comments->count();
        $comments = $comments->paginate(10);
        $imageNumbeOffavroites = Favorite::where('image_id', '=', $id)->count();
        $exif = NULL;
        if (extension_loaded('exif')) {
            $exif = @exif_read_data(public_path() . '/uploads/' . $image_get->image_name . '.' . $image_get->type);
        }
        return View::make('image/index')
            ->with('image', $image_get)
            ->with('exif', $exif)
            ->with('comments', $comments)
            ->with('numberOfComments', $commentsCount)
            ->with('numberOfFavorites', $imageNumbeOffavroites)
            ->with('title', ucfirst($image_get->title));
    }

}
