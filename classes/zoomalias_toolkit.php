<?php

class zoomalias_toolkit
{
    // List of object names allowed to intetacting to through zoomalias.
    const OBJECT_SCOPE = [
        "USER"
    ];

    /**
     * Determine wheter a given endpoint (URL) is allowed/apply for zoomalias. The list
     * of allowed endpoints are set up in zoomalias LTI Source plugin settings.
     * 
     * @param $ltiendpoint String containing the endpoint's URL to validate.
     * @return boolean
     */
    public static function is_applicable_for_zoomalias($ltiendpoint)
    {
        $allowedltis = preg_split('/[(\r\n)|\n]/', trim(get_config('ltisource_zoomalias', 'targetlti')));
        return in_array($ltiendpoint, $allowedltis);
    }

    /**
     * Search for specific indexes in an array and then use the values in that position
     * to replace a certain string format to return a computed value.
     * 
     * @param $requestparams (pointer) Asociative array containing a list of LTI params
     */
    public static function replace_custom_params(&$requestparams)
    {
        foreach($requestparams as $key => $value) {
            // Look for indexes starting with "custom_" (standard for custom parameters in a LTI call)
            if (preg_match('/custom_.*/', $key) && !is_null($value)) {
                $requestparams[$key] = self::eval_custom_params($value);
            }
        }
    }

    /**
     * Transform the given string in PHP format to its corresponding value.
     * 
     * @param $evalstring String in PHP format to convert.
     */
    private static function eval_custom_params($evalstring)
    {
        // Wrong/fail scenarios at first to return the default values as fast
        // as possible. In case of wrong scenarios return the original string.
        if (!$obj = self::is_valid_format($evalstring)) {
            return $evalstring;
        }

        // Loading globals dinamically to validate easier.
        foreach(self::get_object_scope() as $scope) {
            global $$scope;
        }

        // Check if a object->prop exists in the current context
        if (isset(${$obj[0]}->{$obj[1]})) {
            return ${$obj[0]}->{$obj[1]};
        }

        // If the object not match in global context and it is a user object then we look 
        // into custom profile field context.
        if ($obj[0] === "USER") {
            $customfield = self::get_custom_profile_field_data($obj[1], $USER->id);

            if (!is_null($customfield)) {
                return $customfield;
            }
        }

        return $evalstring;
    }

    /**
     * Function to determine if a given string has the expected format like PHP object.
     * @param $evalstring String to validate format
     * @return mixed array|false.
     */
    private static function is_valid_format($evalstring)
    {
        // Remove the dollar sign ("$") from the string for following operations
        // and split it by "." to separate object and prop, object should be 
        // object-prop replation. 
        $obj = explode(".", trim($evalstring, "$"));
        
        // Wrong/fail scenarios
        if (trim($evalstring) == ""
            || strpos($evalstring, "$") !== 0
            || strpos($evalstring, ".") === false
            || count($obj) != 2
            || !self::in_object_scope($obj[0])
        ) {
            return false;
        }

        return $obj;
    }

    /**
     * Return a list of strings containing the names of the objects
     * we determine as "general scope".
     */
    private function get_object_scope()
    {
        return self::OBJECT_SCOPE;
    }

    /**
     * Check wheter an object is in the scope or not.
     * 
     * @param $needle Object name to evaluate.
     * @return boolean
     */
    private static function in_object_scope($needle)
    {
        return in_array($needle, self::get_object_scope());
    }

    /**
     * Return the data from a given custom profile field name of an specific user.
     * 
     * @param $customfieldname
     * @param $userid
     * @return mixed string if find data other case null is returned.
     */
    private static function get_custom_profile_field_data($customfieldname, $userid)
    {
        global $DB;

        $sql = "
        SELECT CFD.data $customfieldname
        FROM {user_info_data} CFD
            INNER JOIN {user_info_field} CF ON (CF.id = CFD.fieldid)
        WHERE CF.shortname = '{$customfieldname}' AND CFD.userid = {$userid}
        ";

        $customfield = $DB->get_record_sql($sql);

        if (!empty($customfield)) {
            return $customfield->$customfieldname;
        }

        return null;
    }

    /**
     * Add the zoom_user custom field of a user into custom_lti_username index 
     * of the given LTI request parameter list, only if this custom field exists
     * for the user.
     * 
     * @param $requestparams (pointer) LTI request parameters.
     */
    public static function add_zoomalias_custom_param(&$requestparams)
    {
        global $USER;

        $zoom_user = self::get_custom_profile_field_data('zoom_user', $USER->id);

        if (!is_null($zoom_user)) {
            $requestparams['custom_lti_username'] = $zoom_user;
        }
    }
}
