<?php
/**
 * @author RickR <rick@rudelinux.org>
 */
class ContactUsController extends BaseController
{
    //public function getContactUs()
    //{
    //    return View::make('contact_us/index')
    //        ->with('title', 'Contact Us');
    //}

    public function postContactUs()
    {

        $input = Input::all();

        $v = array(
            'email'                    => array('required', 'email'),
            'name'                     => array('required'),
            'subject'                  => array('required'),
            'message'                  => array('required'),
            //'recaptcha_response_field' => array('required', 'recaptcha')
        );

        $v = Validator::make($input, $v);
        if ($v->fails()) {
            $errors = $v->messages();
            return($errors);
        }


        $data = array(
            'name'    => Input::get('name'),
            'email'   => Input::get('email'),
            'message' => Input::get('message'),
            'subject' => Input::get('subject')
        );

        Mail::send('emails.contact.index', $data, function ($message) use ($data) {
            $message->to('jameskirby1993@gmail.com', 'James Kirby')->subject($data['subject']);
        });

        return(t("Your message has been sent!"));
    }
}
