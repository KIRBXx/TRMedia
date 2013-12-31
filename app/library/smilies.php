<?php

class Smilies
{
    /**
     * Replace the text smilies with images also add hash tags
     *
     * @access   public
     * @param    string $value
     * @return   string
     */
    public static function parse($value)
    {
        $config = Config::get('smilies');

        $smileys = $config['images'];

        foreach ($smileys as $key => $val) {
            $value = str_replace($key, '<img src="' . $config['path'] . $smileys[$key][0] . '" width="' . $smileys[$key][1] . '" height="' . $smileys[$key][2] . '" alt="' . $smileys[$key][3] . '" style="border:0;" />', $value);
        }

        return preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', ' <a href="' . url('tag') . '/\2">#\2</a>', $value);
    }
}