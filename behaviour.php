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
 * Interactive  explanation question behaviour.
 *
 * This is like the interactive with multiple tries  behaviour, but with an extra
 * text input box where the student can explain their reasoning. That part is
 * un-graded, but the teacher could read it later and manually adjust the marks
 * based on it. The student can also review it later, to be reminded what they
 * were thinking at the time they answered the question. This is heavily based on
 * Tim hunts qbehaviour_deferredfeedbackexplain plugin.
 *
 * @package   qbehaviour_interactiveexplain
 * @copyright 2019 Marcus Green
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../interactive/behaviour.php');


/**
 * Question behaviour for Interactive with explanation
 *
 * This is like the standard Interactive with multiple attempts behaviour, but with an extra
 * text input box where the student can explain their reasoning. That part is
 * un-graded, but the teacher could read it later and manually adjust the marks
 * based on it. The student can also review it later, to be reminded what they
 * were thinking at the time they answered the question.
 *
 */
/**
 * The actual behaviour class
 * @copyright 2020 Marcus Green
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_interactiveexplain extends qbehaviour_interactive {
    /**
     * What fields are expected
     *
     * @return array
     */
    public function get_expected_data() :array {
        $explain = [];
        if ($this->qa->get_state()->is_active()) {
            $explain = [
                'explanation'       => PARAM_RAW,
                'explanationformat' => PARAM_ALPHANUMEXT
            ];
        }
        $expected = parent::get_expected_data();
        $expected = $expected + $explain;

        return $expected;
    }

    /**
     * When restarting a quiz
     *
     * @return array
     */
    protected function get_our_resume_data() :array {
        $lastexplanation = $this->qa->get_last_behaviour_var('explanation');
        if ($lastexplanation) {
            return array(
                '-explanation'       => $lastexplanation,
                '-explanationformat' => $this->qa->get_last_behaviour_var('explanationformat'),
            );
        } else {
            return [];
        }
    }

    /**
     * Work out whether the response in $pendingstep are significantly different
     * from the last set of responses we have stored.
     * @param question_attempt_step $pendingstep contains the new responses.
     * @return bool whether the new response is the same as we already have.
     */
    protected function is_same_response(question_attempt_step $pendingstep) {
        return parent::is_same_response($pendingstep) &&
                $this->qa->get_last_behaviour_var('explanation') == $pendingstep->get_behaviour_var('explanation') &&
                $this->qa->get_last_behaviour_var('explanationformat') == $pendingstep->get_behaviour_var('explanationformat');
    }
    /**
     * Not sure what this does
     * @todo Find out what this does
     *
     * @param question_attempt_step $step
     * @return void
     */
    public function summarise_action(question_attempt_step $step) {
        return $this->add_explanation(parent::summarise_action($step), $step);
    }

    /**
     *  The main entry point for processing an action.
     *
     * @param question_attempt_pending_step $pendingstep
     * @return boolean
     */
    public function process_action(question_attempt_pending_step $pendingstep) : bool {
        $result = parent::process_action($pendingstep);

        if ($result == question_attempt::KEEP && $pendingstep->response_summary_changed()) {
            $explanationstep = $this->qa->get_last_step_with_behaviour_var('explanation');
            $pendingstep->set_new_response_summary($this->add_explanation(
                    $pendingstep->get_new_response_summary(), $explanationstep));
        }
        return $result;
    }
    /**
     * Add the text from the explanation/reason textarea
     *
     * @param string $text
     * @param question_attempt_step $step
     * @return string
     */
    protected function add_explanation($text, question_attempt_step $step) : string {
        $explanation = $step->get_behaviour_var('explanation');
        if (!$explanation) {
            return $text;
        }

        $a = new stdClass();
        $a->response = $text;
        $a->explanation = question_utils::to_plain_text($explanation,
                $step->get_behaviour_var('explanationformat'), array('para' => false));
        return get_string('responsewithreason', 'qbehaviour_interactiveexplain', $a);
    }
}
