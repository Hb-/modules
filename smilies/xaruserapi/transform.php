<?php

/**
 * transform text
 * @param $args['extrainfo'] string or array of text items
 * @returns string
 * @return string or array of transformed text items
 */
function smilies_userapi_transform($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($extrainfo)) {
        $msg = xarML('Invalid Parameter Count',
                    join(', ',$invalid), 'userapi', 'transform', 'smilies');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    if (is_array($extrainfo)) {
        if (isset($extrainfo['transform']) && is_array($extrainfo['transform'])) {
            foreach ($extrainfo['transform'] as $key) {
                if (isset($extrainfo[$key])) {
                    $extrainfo[$key] = smilies_userapitransform($extrainfo[$key]);
                }
            }
            return $extrainfo;
        }
        $transformed = array();
        foreach($extrainfo as $text) {
            $transformed[] = smilies_userapitransform($text);
        }
    } else {
        $transformed = smilies_userapitransform($extrainfo);
    }

    return $transformed;
}

function smilies_userapitransform($text)
{
    static $alsearch = array();
    static $alreplace = array();
    static $gotsmilies = 0;

    if (empty($gotsmilies)) {
        $gotsmilies = 1;
        $tmpsmilies = xarModAPIFunc('smilies', 'user', 'getall');

        // Create search/replace array from autolinks information
        foreach ($tmpsmilies as $tmpsmiley) {
            // Munge word boundaries to stop autolinks from linking to
            // themselves or other autolinks in step 2
            // $tmpsmiley['icon'] = preg_replace('/(\b)/', '\\1ALSPACEHOLDER', $tmpsmiley['icon']);

            // Allow matches for smiles with < and > entities.
            $tmpsmiley['code'] = preg_quote($tmpsmiley['code'], '/');
            $tmpsmiley['code'] = str_replace(array('\>', '\<'), array('(?:&gt;|>)', '(?:&lt;|<)'), $tmpsmiley['code']);

            // Note use of assertions here to only match specific words,
            // for instance ones that are not part of a hyphenated phrase
            // or (most) bits of an email address
            $alsearch[] = '/(?<![\w@\.:-])(' . $tmpsmiley['code'] . ')(?![\w@:-])(?!\.\w)/i';
            $alreplace[] = '<img src="' . htmlspecialchars(xarTplGetImage($tmpsmiley['icon'], 'smilies')) .
                           '" alt="' . htmlspecialchars(xarML($tmpsmiley['emotion'])) .
                           '" title="' . htmlspecialchars(xarML($tmpsmiley['emotion'])) .
                           '" />';
        }
    }

    // Step 1 - move all tags out of the text and replace them with placeholders
    preg_match_all('/(<\w[^>]+>)/i', $text, $matches);
    $matchnum = count($matches[1]);
    for ($i = 0; $i <$matchnum; $i++) {
        $text = preg_replace('/' . preg_quote($matches[1][$i], '/') . '/', "ALPLACEHOLDER{$i}PH", $text, 1);
    }

    // Step 2 - put the smilies in.
    $text = preg_replace($alsearch, $alreplace, $text);

    // Step 3 - replace the spaces we munged in step 2
    $text = preg_replace('/ALSPACEHOLDER/', '', $text);

    // Step 4 - replace the HTML tags that we removed in step 1
    for ($i = 0; $i <$matchnum; $i++) {
        $text = preg_replace("/ALPLACEHOLDER{$i}PH/", $matches[1][$i], $text, 1);
    }

    return $text;
}
?>