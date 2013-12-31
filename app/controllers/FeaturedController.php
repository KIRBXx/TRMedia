<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class FeaturedController extends BaseController
{
    /**
     * Main gallery of site
     */
    public function getIndex()
    {
        $images = Images::where('approved', '=', 1)->where('is_featured','=',1)->orderBy('created_at', 'desc')->with('user', 'comments', 'favorite')->paginate(perPage());
        return View::make('gallery/index')
            ->with('images', $images)
            ->with('title', t('Featured Images'));
    }
}