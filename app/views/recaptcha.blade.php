@if(!empty($options))
<script type="text/javascript">
    var RecaptchaOptions = {{ json_encode($options) }};
</script>
<style type="text/css">
    #recaptcha_logo,#recaptcha_privacy {
        display: none!important;
    }
    #recaptcha_response_field {
        border: 2px solid #DCE4EC!important;
        width: 302px!important;
        height: 30px;
    }
    #recaptcha_response_field:focus {
        border-color: #1abc9c !important;
    }
</style>
@endif
<script type="text/javascript" src="//www.google.com/recaptcha/api/challenge?k={{ $public_key }}"></script>
<noscript>
    <iframe src="//www.google.com/recaptcha/api/noscript?k={{ $public_key }}" height="300" width="500" frameborder="0"></iframe><br>
    <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
    <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
</noscript>