<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */

function siteSettings($request)
{
    $request = DB::table('sitesettings')->where('option', '=', $request)->remember(999, $request)->first();
    return $request->value;
}

function siteCategories()
{
    return DB::table('sitecategories')->remember(999, 'siteCategories')->orderBy(DB::raw('category'))->get();

}

/**
 * Pagination limit per page in gallery
 * @param int $int
 * @return int
 */
function perPage($int = 20)
{
    if (siteSettings('numberOfImagesInGallery') == '') {
        return $int;
    }
    return siteSettings('numberOfImagesInGallery');
}

/**
 * Number of tags that an image can hold
 * @param int $int
 * @return int
 */
function tagLimit($int = 5)
{
    return $int;
}

function limitPerDay($int = 100)
{
    if (siteSettings('limitPerDay') == '') {
        return $int;
    }
    return siteSettings('limitPerDay');
}