<?php

function is_eng() {
    $session = new Zend_Session_Namespace;
    if (isset($session->lang)) {
        $is_eng = ($session->lang != 'zh_CN');
    } else {
        $is_eng = true;
    }
    return $is_eng;
}

function locale_filtered_tags($recordOrTags) {
    if (is_array($recordOrTags)) {
        $tags = $recordOrTags;
    } elseif (is_string($recordOrTags)) {
        $tagstring = tag_string($recordOrTags);
        $tags = explode(', ', $tagstring);
    }

    define('IS_ENG', is_eng());

    $tags = array_filter($tags, function ($utf8_str) {
        $pattern = "/\p{Han}+/u";
        $match = preg_match($pattern, $utf8_str);
        if (IS_ENG) {
            return ! $match;
        } else {
            return $match;
        }
    });
    return $tags;
}

function locale_filtered_tag_string($recordOrTags) {
    $tagstring = implode(', ', locale_filtered_tags($recordOrTags));
    return $tagstring;
}
