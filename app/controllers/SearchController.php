<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class SearchController extends BaseController
{
    public function getIndex()
    {
        $search = Request::get('q');
        if (empty($search)) {
            return Redirect::to('gallery');
        }
        $extends = explode(' ', $search);


        $images = Images::where('title', 'LIKE', '%' . $search . '%')
            ->orWhere('tags', 'LIKE', '%' . $search . '%')->orWhere('category', '=', $search)
            ->where('deleted_at', '=', NULL)->where('approved','=',DB::raw(1))->orderBy('created_at', 'desc');

        foreach ($extends as $extend) {
            $images->orWhere('tags', 'LIKE', '%' . $extend . '%')
                ->orWhere('title', 'LIKE', '%' . $search . '%')
                ->orWhere('image_description', 'LIKE', '%' . $search . '%');
        }
        $images = $images->paginate(30);

        return View::make('gallery/index')
            ->with('images', $images)
            ->with('title', t('Searching for').' "' . ucfirst($search) . '"');

    }
}