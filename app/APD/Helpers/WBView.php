<?php
/**
 * @author          Archie Disono (webmonsph@gmail.com)
 * @link            https://github.com/disono/Laravel-Template
 * @copyright       Webmons Development Studio. (https://webmons.com), 2016-2019
 * @license         Apache, 2.0 https://github.com/disono/Laravel-Template/blob/master/LICENSE
 */

use Illuminate\Http\Response;
use Jenssegers\Agent\Agent;

if (!function_exists('theme')) {
    /**
     * Theme for user
     *
     * @param null $file
     * @param array $data
     * @param int $response
     * @return Response
     */
    function theme($file = null, $data = [], $response = 200)
    {
        $currentTheme = currentTheme();
        return response()->view($currentTheme . trim($file, '/'), $data, $response);
    }
}

if (!function_exists('currentTheme')) {
    /**
     * Current theme selected from settings
     */
    function currentTheme()
    {
        $agent = new Agent();
        return 'themes.' . __settings('theme')->value . '.' .
            ((($agent->isMobile() === 1 || $agent->isTablet() === 1) && env('VIEW_MOBILE') === true) ? 'mobile.' : 'desktop.');
    }
}

if (!function_exists('adminTheme')) {
    /**
     * Admin theme
     *
     * @param $file
     * @param $data
     * @param int $response
     * @return Response
     */
    function adminTheme($file, $data, $response = 200)
    {
        return response()->view('admin.' . $file, $data, $response);
    }
}

if (!function_exists('isCurrentRoute')) {
    /**
     * Check if on the current route
     *
     * @param $to
     * @return bool
     */
    function isCurrentRoute($to)
    {
        if (!request()) {
            return false;
        }

        if (!request()->route()) {
            return false;
        }

        $to = explode('|', $to);
        if (in_array(request()->route()->getName(), $to)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('getRouteName')) {
    /**
     * Get the current route name
     *
     * @return bool|string|null
     */
    function getRouteName()
    {
        if (!request()) {
            return false;
        }

        if (!request()->route()) {
            return null;
        }

        return request()->route()->getName();
    }
}

if (!function_exists('isActiveMenu')) {
    /**
     * Is active menu
     *
     * @param $to
     * @return string
     */
    function isActiveMenu($to)
    {
        if (isCurrentRoute($to)) {
            return 'active';
        }

        return '';
    }
}

if (!function_exists('hasInputError')) {
    /**
     * Form error
     *
     * @param $errors
     * @param $inputName
     * @return string
     */
    function hasInputError($errors, $inputName)
    {
        return $errors->has($inputName) ? ' is-invalid invalid' : '';
    }
}

if (!function_exists('thDelete')) {
    /**
     * Checkbox th delete
     *
     * @return string
     */
    function thDelete()
    {
        $_id = '_check_' . time();
        return '<th>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" value="" id="' . $_id . '" v-on:change="toolbarSelectItem($event)">
                <label class="custom-control-label" for="' . $_id . '"></label>
            </div>
        </th>';
    }
}

if (!function_exists('tdDelete')) {
    /**
     * Checkbox td delete
     *
     * @param $id
     * @return string
     */
    function tdDelete($id)
    {
        return '<td>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" delete-data="toolbarSelectedItems" id="del_' . $id . '"
                                           v-model="toolbar.selectedItems" value="' . $id . '">
                <label class="custom-control-label" for="del_' . $id . '"></label>
            </div>
        </td>';
    }
}

if (!function_exists('html_meta_tag')) {
    /**
     * Meta tag
     *
     * @param $property
     * @param $content
     * @return string
     */
    function html_meta_tag($property, $content)
    {
        if (!$property || !$content) {
            return null;
        }

        return '<meta property="' . $property . '" content="' . $content . '" />';
    }
}