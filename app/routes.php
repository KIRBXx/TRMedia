<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */

// MUST REMOVE THIS AFTER INSTALL FOR SECURITY REASONS
Route::resource('install', 'InstallController');
/**
 * Do not edit anything below this un-till unless you know what you are doing.
 * If you modify anything your site might go corrupted.
 * Thanks
 */
Route::get('/', 'HomeController@getIndex')->before('guest');
Route::get('gallery', 'GalleryController@getIndex');
Route::get('featured', 'FeaturedController@getIndex');
Route::get('image/{id}/{slug?}', 'ImageController@getIndex')->where(array('id' => '\d+'));
Route::get('user/{username}', 'UserController@getUser');
Route::get('user/{username}/favorites', 'UserController@getFavorites');
Route::get('user/{username}/followers', 'UserController@getFollowers');
Route::get('user/{username}/rss', 'UserController@getRss');
Route::get('users', 'UserController@getAll');
Route::get('category/{category}', 'CategoryController@getIndex');
Route::get('category/{category}/rss', 'CategoryController@getRss');
Route::get('tag/{tag}', 'TagController@getIndex');
Route::get('tag/{tag}/rss', 'TagController@getRss');
Route::get('notifications', 'NotificationController@getIndex');
Route::get('search', 'SearchController@getIndex');
Route::get('tos', 'PolicyController@getTos');
Route::get('privacy', 'PolicyController@getPrivacy');
Route::get('faq', 'PolicyController@getFaq');
Route::get('about', 'PolicyController@getAbout');
Route::get('most/commented','MostController@mostCommented');
Route::get('most/favorites','MostController@mostFavorited');
Route::get('most/downloads','MostController@mostDownloaded');

// Added 23 Dec RR
Route::get('contact_us', 'ContactUsController@getContactUs');
Route::post('contact_us', 'ContactUsController@postContactUs');

Route::get('lang/{lang?}', function ($lang) {
    if (in_array($lang, languageArray())) {
        Session::put('my.locale', $lang);
    } else {
        Session::put('my.locale', 'en');
    }
    return Redirect::to('/');
});
/**
 * Guest only visit this section
 */
Route::group(array('before' => 'guest'), function () {
    Route::get('login', 'LoginController@getLogin');
    Route::get('login/facebook', 'LoginController@getFacebook');
    Route::get('registration/facebook', 'RegistrationController@getFacebook');

    Route::get('password/remind', 'PasswordresetController@getIndex');
    Route::get('registration', 'RegistrationController@getIndex');
    Route::get('registration/activate/{username}/{code}', 'RegistrationController@validateUser');
    Route::get('password/reset/{token}', 'PasswordresetController@passwordReset');
});

/**
 * Guest Post form with csrf protection
 */
Route::group(array('before' => 'csrf|guest'), function () {
    Route::post('login', 'LoginController@postLogin');
    Route::post('registration/facebook', 'RegistrationController@postFacebook');
    Route::post('registration', 'RegistrationController@postIndex');
    Route::post('password/remind', 'PasswordresetController@postIndex');
    Route::post('password/reset/{token}', 'PasswordresetController@resetPassword');
});


/*
 * Ajax post
 */
Route::group(array('before' => 'ajax|ajaxban'), function () {
    Route::post('favorite', 'FavoriteController@postFavorite');
    Route::post('follow', 'FollowController@postFollow');
    Route::post('reply', 'ReplyController@postReply');
    Route::post('deletecomment', 'CommentController@postDeleteComment');
    Route::post('deletereply', 'ReplyController@postDeleteReply');
    Route::post('upload', 'UploadController@postUpload')->before('ban');
});

/*
 * Require login to access these sections
 */
Route::group(array('before' => 'auth'), function () {
    Route::get('upload', 'UploadController@getIndex')->before('ban');
    Route::get('logout', 'LoginController@getLogout');
    Route::get('feeds', 'FeedsController@getIndex');
    Route::get('user/{username}/following', 'UserController@getFollowing');
    Route::get('download/{id}/{any}', 'DownloadController@getDownload')->before('ban');
    Route::get('settings', 'SettingsController@getSettings');

    Route::get('delete/image/{id}', 'DeleteController@getDeleteImage');

    Route::get('report/image/{id}', 'ReportController@getReport')->before('ban');
    Route::get('report/user/{username}', 'ReportController@getReport')->before('ban');

});

/**
 * Post Sections CSRF + AUTH both
 */
Route::group(array('before' => 'csrf|auth'), function () {

    Route::post('image/{id}/{slug?}', 'CommentController@postComment')->where(array('id' => '\d+'))->before('ban');

    Route::post('settings/changepassword', 'SettingsController@postChangePassword');
    Route::post('settings/updateprofile', 'SettingsController@postUpdateProfile');
    Route::post('settings/updateavatar', 'SettingsController@postUpdateAvatar');

    Route::post('report/image/{id}', 'ReportController@postReportImage')->before('ban');
    Route::post('report/user/{username}', 'ReportController@postReportUser')->before('ban');
});


/**
 * Admin section users with admin privileges can access this area
 */
Route::group(array('before' => 'admin'), function () {
    Route::get('admin', 'AdminController@getIndex');
    Route::get('admin/users', 'AdminController@getUsersList');
    Route::get('admin/comments', 'AdminController@getAllComments');
    Route::get('admin/users/featured', 'AdminController@getFeaturedUserList');
    Route::get('admin/users/banned', 'AdminController@getBannedUserList');
    Route::get('admin/adduser', 'AdminController@getAddUser');


    Route::get('admin/images', 'AdminController@getImagesList');
    Route::get('admin/images/approval', 'AdminController@getImagesApproval');

    Route::get('admin/images/featured', 'AdminController@featuredImagesList');
    Route::get('admin/sitesettings', 'AdminController@getSiteSettings');
    Route::get('admin/sitecategory', 'AdminController@getSiteCategory');
    Route::get('admin/limitsettings', 'AdminController@getLimitSettings');
    Route::post('admin/limitsettings', 'AdminupdateController@postLimitSettings');

    Route::get('admin/edituser/{username}', 'AdminController@getEditUser');
    Route::get('admin/editimage/{id}', 'AdminController@getEditImage');

    Route::get('admin/reports', 'AdminController@getReports');
    Route::get('admin/report/{id}', 'AdminController@getReadReport');
    Route::get('admin/removecache', 'AdminController@getRemoveCache');
    Route::get('admin/updatesitemap', 'AdminController@updateSiteMap');
    Route::get('admin/bulkupload','AdminController@getBulkUpload');

    Route::post('admin/editimage/{id}', 'AdminupdateController@updateImage');
    Route::post('admin/images/approval', 'AdminupdateController@updateImagesApproval');
    Route::post('admin/edituser/{username}', 'AdminupdateController@updateUser');
    Route::post('admin/sitesettings', 'AdminupdateController@updateSettings');
    Route::post('admin/sitecategory', 'AdminupdateController@updateSiteCategory');
    Route::post('admin/adduser', 'AdminupdateController@addUser');
    Route::post('admin/bulkupload','AdminupdateController@postBulkUpload');
});