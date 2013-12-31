<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 */
class PolicyController extends BaseController
{
    /**
     * Terms and services
     * @return mixed
     */
    public function getTos()
    {
        return View::make('policy/tos')
            ->with('title', 'Friends');
    }

    /**
     * Privacy Policies
     * @return mixed
     */
    public function getPrivacy()
    {
        return View::make('policy/privacy')
            ->with('title', 'Privacy Policy');
    }

    /**
     * Faq of the site
     * @return mixed
     */
    public function getFaq()
    {
        return View::make('policy/faq')
            ->with('title', 'Privacy Policy');
    }

    /**
     * About us
     * @return mixed
     */
    public function getAbout()
    {
        return View::make('policy/about')
            ->with('title', 'About Us');
    }
}