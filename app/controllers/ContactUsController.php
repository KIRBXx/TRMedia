<?php
/**
 * @author RickR <rick@rudelinux.org>
 */
class ContactUsController extends BaseController
{
    public function getContactUs()
    {
        return View::make('contact_us/index')
            ->with('title', 'Contact Us');
    }

    public function postContactUs()
    {

        $input = Input::all();

        $v = array(
            'email'                    => array('required', 'email'),
            'name'                     => array('required'),
            'subject'                  => array('required'),
            'mail_message'             => array('required'),
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
            'mail_message' => Input::get('mail_message'),
            'subject' => Input::get('subject')
        );

        Mail::send('emails.contact.index', $data, function ($message) use ($data) {
            $message->to('jameskirby1993@gmail.com', 'James Kirby')->subject($data['subject']);
        });

        $return_msg = [];
        $return_msg['success'] = "Your message has been sent!";
        return($return_msg);
    }
}
