<?php

/**
 * get the replacement text for an autolink
 * @param $args['lid'] id of the link to fetch; or
 * @param $args['link'] link array if already fetched
 * @returns array
 * @return string: replace string; either a parsed template or a text array definition
 */
function autolinks_userapi_getreplace($args)
{
    // TODO: this will eventually select the template based on the
    // autolink type and feed DD property values into the template
    // as defined for the autolink type.

    extract($args);

    // Either a lid or a pre-fetched autolink detail has been passed in.
    if (!isset($lid) && !isset($link)) {
        $msg = xarML('Invalid Parameter Count',
                    'userapi', 'getreplace', 'autolinks');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    if (isset($lid)) {
        $link = xarModAPIFunc(
            'autolinks', 'user', 'get',
            array('lid' => $lid)
        );
    }

    if (!$link) {
        if (isset($lid)) {
            $result['error'] = xarML('Link ID ('.$lid.') invalid');
        } else {
            $result['error'] = xarML('Link details not supplied');
        }
        return $result;
    }

    // Check if we want special link styles
    $decoration = xarModGetVar('autolinks', 'decoration');
    if ($decoration) {
        $style = 'text-decoration: '.$decoration.';';
    } else {
        $style = '';
    }

    // Check if we want to open in a new window.
    if (xarModGetVar('autolinks', 'newwindow')) {
        $target = '_blank';
    } else {
        $target = '';
    }
    
    // Build an array of data for the template.

    // Standard values available to the template.
    // The comment is not normally used in the template, but it could
    // be useful to pass in.
    $template_data = array(
        'match' => '$1',
        'url' => $link['url'],
        'title' => $link['title'],
        'comment' => $link['comment'],
        'style' => $style,
        'target' => $target
    );

    // DD items to add to the template.
    if (xarModIsHooked('dynamicdata', 'autolinks', $link['itemtype'])) {
        // We are hooked into DD, so fetch the current fields and values.
        $dd_data = xarModAPIfunc(
            'dynamicdata', 'user', 'getitem',
            array('module'=>'autolinks', 'itemtype'=>$link['itemtype'], 'itemid'=>$link['lid'])
        );

        // Place each field value into the template array.
        if (is_array($dd_data)) {
            foreach ($dd_data as $name => $value) {
                // Prefixing a property name with an underscore will prevent it getting
                // into the template.
                // Splitting a property name into parts using underscores will allow
                // sub-arrays of any depth to be created. This is a bit of a work-around
                // that perhaps DD will support directly in the future.
                if ($name{0} != '_') {
                    // e.g. a DD property named 'a' will be evaluated as $template_data['a']
                    // and a DD property named 'a_b_c' will be evaluated as $template_data['a']['b']['c']
                    @eval('$template_data[\'' . str_replace('_', '\'][\'', $name) . '\'] = $value;');
                }
            }
        }
    }

    // Either execute the template now (if cachable) or return the expression used to
    // execute the template in an expression-based preg_replace.
    // Executing now will give us a simple replace string, creating the expression
    // will give us a function with array-based parameters.

    if ($link['dynamic_replace']) {
        // Dynamic templates are executed later on, when the link match is made.

        // Create the PHP expression, used to pass into the template at runtime,
        // but don't execute the template at this stage.
        $result = xarModAPIfunc('autolinks', 'user', 'varexport', $template_data);
    } else {
        // Non-dynamic templates can be executed and cached for later use.

        // Execute the template.
        //set_error_handler(null);
        $result = xarTplModule(
            'autolinks',
            xarModGetVar('autolinks', 'templatebase'),
            $template_name = $link['template_name'],
            $template_data
        );
        //restore_error_handler();

        // Catch any exceptions.
        if (xarExceptionMajor()) {
            $error_text = xarExceptionRender('text');
            // Hack until exceptions are sorted.
            if (isset($error_text['short'])) {$error_text = $error_text['short'];}

            // Handle the exception since we have rendered it.
            xarExceptionHandled();

            // Do we want the error displayed in-line?
            if (xarModGetVar('autolinks', 'showerrors') || xarVarGetCached('autolinks', 'showerrors')) {
                // Pass the error through the error template.
                // This mode of operation is used during setup.
                $result = xarTplModule('autolinks', 'error', 'match',
                    array(
                        'match' => '$1',
                        'template_base' => xarModGetVar('autolinks', 'templatebase'),
                        'template_name' => $link['template_name'],
                        'error_text' => $error_text
                    )
                );
                // Even the error template errored.
                if (xarExceptionMajor()) {
                    $result = '$1';
                    xarExceptionHandled();
                }
            } else {
                // Don't highlight the error - just return the matched text.
                // This is the normal (i.e. after debugging) mode of operation.
                $result = '$1';
            }
        } else {
            // Trim the template output, at least until we have more control
            // over whitespace in the rendered template output.
            $result = trim($result);
        }
    }

    return $result;
}

?>