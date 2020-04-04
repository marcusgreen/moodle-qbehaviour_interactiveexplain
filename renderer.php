<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Defines the renderer for the interactive with explanation behaviour.
 *
 * @package   qbehaviour_interactiveexplain
 * @copyright 2019 Marcus Green
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__) . '/../interactive/renderer.php');

/**
 * Output parts of a question belonging to the interactive with explanation
 *
 * @copyright 2019 Marcus Green
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_interactiveexplain_renderer extends qbehaviour_interactive_renderer {


    /**
     * Add controls associated with the explanation
     * headings textarea etc
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string
     */
    public function controls(question_attempt $qa, question_display_options $options) : string {
        $controls = parent::controls($qa, $options);
        $explanation = html_writer::div(html_writer::div($this->explanation($qa, $options), 'answer'), 'ablock');
        return $controls . $explanation;
    }

    /**
     * Render the explanation as either a HTML editor, or read-only, as applicable.
     * @param question_attempt $qa a question attempt.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    protected function explanation(question_attempt $qa, question_display_options $options) {
        $step = $qa->get_last_step_with_behaviour_var('explanation');

        if (empty($options->readonly)) {
            $answer = $this->explanation_input($qa, $step, $options->context);
        } else {
            $answer = $this->explanation_read_only($step);
        }

        return $answer;
    }


    /**
     * Render the explanation in read-only form.
     *
     * @param question_attempt_step $step from which to get the current explanation.
     * @return string
     */
    public function explanation_read_only(question_attempt_step $step) : string {
        $output = '';
        if ($step->has_behaviour_var('explanation')) {
            $formatoptions = new stdClass();
            $formatoptions->para = false;
            $explanation = $step->get_behaviour_data('explanation');
            $step->get_behaviour_var('explanationformat');
            if ($explanation['explanation'] > '') {
                $output .= html_writer::tag('span', get_string('explanation', 'local_qbehaviour_interactiveexplain'),
                 ['class' => 'explanation_header']);
                $output .= html_writer::div(format_text($step->get_behaviour_var('explanation'),
                    $step->get_behaviour_var('explanationformat'), $formatoptions), 'explanation_readonly');
            }

        }
        return $output;
    }

    /**
     * Render the explanation in a HTML editor.
     * @param question_attempt $qa a question attempt.
     * @param question_attempt_step $step from which to get the current explanation.
     * @param context $context
     * @return string HTML fragment.
     */
    public function explanation_input(question_attempt $qa, question_attempt_step $step, context $context) :string {
        global $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');
        $config = get_config('local_qbehaviour_interactiveexplain');

        $output = '';
        if ($config->starthidden) {
            $output .= '<details>';
        }

        $output .= '<summary>' . get_string('problem_with_question_header', 'local_qbehaviour_interactiveexplain') . '</summary>';

        $inputname = $qa->get_behaviour_field_name('explanation');
        $explanation = $step->get_behaviour_var('explanation');
        $explanationformat = $step->get_behaviour_var('explanationformat');
        $id = $inputname . '_id';

        $editor = editors_get_preferred_editor($explanationformat);
        $strformats = format_text_menu();
        $formats = $editor->get_supported_formats();
        foreach ($formats as $fid) {
            $formats[$fid] = $strformats[$fid];
        }

        if ($config->useeditor) {
            $editor->use_editor($id, ['context' => $context, 'autosave' => false],
                ['return_types' => FILE_EXTERNAL]);
        }

        $output .= html_writer::tag('p', get_string('giveyourexplanation', 'local_qbehaviour_interactiveexplain'));

        $output .= html_writer::div(html_writer::tag('textarea', s($explanation),
            ['id' => $id, 'name' => $inputname, 'rows' => 4, 'cols' => 80]));

        $output .= html_writer::start_div();
        if (count($formats) == 1) {
            reset($formats);
            $output .= html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $inputname . 'format', 'value' => key($formats)));

        } else {
            $output .= html_writer::label(get_string('format'), 'menu' . $inputname . 'format', false);
            $output .= ' ';
            $output .= html_writer::select($formats, $inputname . 'format', $explanationformat, '');
        }
        $output .= html_writer::end_div();
        if ($config->starthidden) {
            $output .= '</details>';
        }
        return $output;
    }
}