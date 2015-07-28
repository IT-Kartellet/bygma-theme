<?php

require_once("$CFG->dirroot/lib/coursecatlib.php");

/**
 * Moodle's Clean theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_clean
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_bygma_process_css($css, $theme) {

    // Set the background image for the logo.
    $logo = $theme->setting_file_url('logo', 'logo');
    $css = theme_bygma_set_logo($css, $logo);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_bygma_set_customcss($css, $customcss);

    return $css;
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css The CSS.
 * @param string $logo The URL of the logo.
 * @return string The parsed CSS
 */
function theme_bygma_set_logo($css, $logo) {
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_bygma_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM ) {
        $theme = theme_config::load('learngo');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        if($filearea === 'logo'){
            return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
        } else if($filearea == 'frontpageslider'){
            return $theme->setting_file_serve('frontpageslider', $args, $forcedownload, $options);
        }
    }else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_bygma_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * Do not add Clean specific logic in here, child themes should be able to
 * rely on that function just by declaring settings with similar names.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_bygma_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    if (!empty($page->theme->settings->logo)) {
        $return->heading = html_writer::tag('div', '', array('class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.format_text($page->theme->settings->footnote).'</div>';
    }

    return $return;
}

/**
 * All theme functions should start with theme_bygma_
 * @deprecated since 2.5.1
 */
function bygma_process_css() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_bygma_
 * @deprecated since 2.5.1
 */
function bygma_set_logo() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_bygma_
 * @deprecated since 2.5.1
 */
function bygma_set_customcss() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

function render_frontpage_slider(){

    $output = html_writer::start_div('frontpage-slider-wrapper', array('id' => 'frontpage-slider'));
    foreach(get_resources_url('frontpageslider', 'theme_bygma') as $frontpageslider){
        $output .= html_writer::img($frontpageslider, null);
    }
    $output .= html_writer::end_div();
    return $output;
}

function get_resources_url($filearea, $component){
    global $DB;

    $file = $DB->get_record_sql("SELECT * FROM {files}
        WHERE filename = :filename AND component = :component AND filearea = :filearea ORDER BY timemodified DESC LIMIT 1",
        array('component' => $component, 'filearea' => $filearea, 'filename' => '.'));

    if (empty($file)){
        return array();
    }

    $fs = get_file_storage();

    $files = $fs->get_area_files($file->contextid, $file->component, $file->filearea, $file->itemid);

    $resource_urls = array();

    foreach($files as $file){
        if ($file->get_filename() != '.'){
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            array_push($resource_urls, $url);
        }
    }
    return $resource_urls;
}

/*
    Renders available courses
    returns rendered course or null if user not logged in
*/
function render_frontpage_courses(){
    global $CFG;

    if(isloggedin()){
        if (!empty($CFG->navsortmycoursessort)) {
            // sort courses the same as in navigation menu
            $sortorder = 'visible DESC,'. $CFG->navsortmycoursessort.' ASC';
        } else {
            $sortorder = 'visible DESC,sortorder ASC';
        }

        $courses = enrol_get_my_courses(null, $sortorder);

        if($courses){
            $output = html_writer::start_div('frontpage-mycourses-wrapper');
            $output .= html_writer::start_tag('table');

            $iterator = new ArrayIterator($courses);
            $counter = 1;

            while($iterator->valid()){
                $course = $iterator->current();

                if($counter == 1){
                    $output .= html_writer::start_tag('tr');
                }
                $output .= html_writer::start_tag('td', array('class' => "cell-{$counter}"));
                $output .= html_writer::start_div('frontpage-course-wrapper');
                $output .= html_writer::tag('a', render_course_image($course), array('href' => "{$CFG->wwwroot}/course/view.php?id={$course->id}"));
                $output .= html_writer::start_div('frontpage-course-text');
                $output .= html_writer::start_tag('a', array('href' => "{$CFG->wwwroot}/course/view.php?id={$course->id}"));
                $output .= html_writer::tag('h3', $course->fullname, array('class' => 'frontpage-course-fullname'));
                $output .= html_writer::tag('span', $course->shortname, array('class' => 'frontpage-course-shortname'));
                $output .= html_writer::end_tag('a');
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
                $output .= html_writer::end_tag('td');
                $iterator->next();
                if($counter == 3 || !$iterator->valid()){
                    $counter = 1;
                    $output .= html_writer::end_tag('tr');
                    continue;
                }
                $counter++;
            }
            $output .= html_writer::end_tag('table');
            $output .= html_writer::end_div();
            return $output;
        }
    }
    return null;
}

function render_course_image($course = null){
    global $PAGE;

    if($course == null){
        $course = $PAGE->course;
    }

    $course_in_list = new course_in_list($course);
    $course_files = $course_in_list->get_course_overviewfiles();

    $output = html_writer::start_div('course-image-wrapper');

    if(count($course_files) > 0){
        $file = reset($course_files);
        $file_url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
            null, $file->get_filepath(), $file->get_filename());
        $output .= html_writer::img($file_url, null);

    }
    $output .= html_writer::end_div();

    return $output;
}
