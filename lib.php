<?php

require_once($CFG->dirroot . '/mod/lti/source/zoomalias/classes/zoomalias_toolkit.php');

/**
 * before_launch hook is called by LTI module just before signing and POST'ing LTI launch form.
 *
 * Add new LTI parameters based on config or modify existing ones just before the launch.
 * The launch cannot be cancelled gracefully so throw an exception on critical error.
 *
 *
 * @param int $instance LTI instance.
 * @param int $endpoint LTI endpoint. Cannot be modified.
 * @param int $requestparams Initial LTI parameters.
 * @return array Additional or modified LTI parameters, which is merged into the original ones.
 * @throws moodle_exception On critical error.
 */
function ltisource_zoomalias_before_launch($instance, $endpoint, $requestparams)
{
    global $CFG;

    // Filtering the applicable LTI launches. Only allowed are running the zoom alias stuff.
    if (zoomalias_toolkit::is_applicable_for_zoomalias($endpoint)) {

        if ($instance->name == "Zoom LTI Activity STD (Customizable)") {
            zoomalias_toolkit::replace_custom_params($requestparams);
        }

        if ($instance->name == "Zoom LTI Activity STD (Hardcoded)") {
            zoomalias_toolkit::add_zoomalias_custom_param($requestparams);
        }
        
        if (!empty($CFG->debugdisplay)) {
            echo var_dump($requestparams);
            die();
        }
    }
    
    return $requestparams;
}
