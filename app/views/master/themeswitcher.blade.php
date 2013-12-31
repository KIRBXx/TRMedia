
<style type="text/css">
        /* =================================================================== */
        /* Styles Switcher
        ====================================================================== */

    #style-switcher h3 {
        color:#fff;
        font-size:12px;
        margin: 5px 0 -3px 3px;
    }

    #style-switcher {
        background: #222;
        width:150px;
        position:fixed;
        top:50px;
        z-index:9999;
        left:0px;
        border-radius: 0 0 3px 0;
    }


    #style-switcher h2 {
        background: #333;
        color: #FFFFFF;
        font-weight: bold;
        padding: 0;
        font-size: 14px;
        padding: 6px 0 5px 10px;
        margin-top: -1px;
    }

    #style-switcher h2 a {
        background: url("http://centum.envato.tabvn.com/sites/all/themes/centum/images/switcher.png") no-repeat scroll left center transparent;
        display: block;
        height: 281px;
        position: absolute;
        right: -39px;
        text-indent: -9999px;
        top: 0;
        width: 39px;
        border-radius: 0 3px 3px 0;
    }

    .colors { list-style:none; margin-left:-23px; overflow: hidden}
    .colors li { float:left; margin:2px; }
    .tms-img {height: 100px;width: 100px;}

    .layout-style select {
        width: 100%;
        padding: 5px;
        border: none;
        margin:0 0 0 -5px;
        color: #666;
        cursor: pointer;
    }

    #reset {margin: 0 0 0px 30px;}
    #reset a {color: #fff; font-size: 14px;}




    @media only screen and (max-width: 1029px) {#style-switcher {display: none;}}
</style>
<script type="text/javascript">
    /*-----------------------------------------------------------------------------------
     /* Styles Switcher
     -----------------------------------------------------------------------------------*/

    window.console = window.console || (function(){
        var c = {}; c.log = c.warn = c.debug = c.info = c.error = c.time = c.dir = c.profile = c.clear = c.exception = c.trace = c.assert = function(){};
        return c;
    })();

    (function ($) {


        jQuery(document).ready(function($) {

            // Color Changer
            $(".default" ).click(function(){
                $("#colors" ).attr("href", "/static/css/bootstrap.min.css" );
                return false;
            });

            $(".one" ).click(function(){
                $("#colors" ).attr("href", "/static/css/1.min.css" );
                return false;
            });

            $(".two" ).click(function(){
                $("#colors" ).attr("href", "/static/css/2.min.css" );
                return false;
            });

            $(".three" ).click(function(){
                $("#colors" ).attr("href", "/static/css/3.min.css" );
                return false;
            });

            $(".four" ).click(function(){
                $("#colors" ).attr("href", "/static/css/4.min.css" );
                return false;
            });

            $(".five" ).click(function(){
                $("#colors" ).attr("href", "/static/css/5.min.css" );
                return false;
            });

            $("#layout-switcher").on('change', function() {
                $('#layout').attr('href', $(this).val() + '.css');
            });;


            // Style Switcher
            $('#style-switcher').animate({
                left: '-154px'
            });

            $('#style-switcher h2 a').click(function(e){
                e.preventDefault();
                var div = $('#style-switcher');
                console.log(div.css('left'));
                if (div.css('left') === '-154px') {
                    $('#style-switcher').animate({
                        left: '0px'
                    });
                } else {
                    $('#style-switcher').animate({
                        left: '-154px'
                    });
                }
            })

            $('.colors li a').click(function(e){
                e.preventDefault();
                $(this).parent().parent().find('a').removeClass('active');
                $(this).addClass('active');
            })

            $('.bg li a').click(function(e){
                e.preventDefault();
                $(this).parent().parent().find('a').removeClass('active');
                $(this).addClass('active');
                var bg = $(this).css('backgroundImage');
                $('body').css('backgroundImage',bg)
            })

            $('.bgsolid li a').click(function(e){
                e.preventDefault();
                $(this).parent().parent().find('a').removeClass('active');
                $(this).addClass('active');
                var bg = $(this).css('backgroundColor');
                $('body').css('backgroundColor',bg).css('backgroundImage','none')
            })

            $('#reset a').click(function(e){
                var bg = $(this).css('backgroundImage');
                $('body').css('backgroundImage','url(/sites/all/themes/centum/images/bg/noise.png)')
            })


        });
    })(jQuery);
</script>


<div id="style-switcher">
    <h2>Style Switcher <a href="#"></a></h2>
    <div>
        <ul class="colors" id="color1">
            <li><a href="#" class="one" title="Green"><img src="http://bootswatch.com/united/thumbnail.png" class="tms-img"/></a></li>
            <li><a href="#" class="two" title="Green"><img src="http://bootswatch.com/united/thumbnail.png" class="tms-img"/></a></li>
            <li><a href="#" class="three" title="Green"><img src="http://bootswatch.com/united/thumbnail.png" class="tms-img"/></a></li>
            <li><a href="#" class="four" title="Green"><img src="http://bootswatch.com/united/thumbnail.png" class="tms-img"/></a></li>
            <li><a href="#" class="five" title="Green"><img src="http://bootswatch.com/united/thumbnail.png" class="tms-img"/></a></li>
        </ul>


    <div id="reset"><a href="#" class="btn default">Reset</a></div>

</div>
