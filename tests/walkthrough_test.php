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

namespace qbehaviour_interactiveexplain;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../../engine/lib.php');
require_once(dirname(__FILE__) . '/../../../engine/tests/helpers.php');
use question_hint_with_parts;

/**
 * Walk-through tests for the deferred feedback with explanation behaviour.
 *
 * @package   qbehaviour_interactiveexplain
 * @copyright 2023
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class walkthrough_test extends \qbehaviour_walkthrough_test_base {
    /**
     * Test a multiple choice question
     * @covers ::process_submission()
     *
     * @return void
     */
    public function test_interactiveexplain_feedback_multichoice_right() {
        global $PAGE;
        // Required to init a text editor.
        $this->setAdminUser();
        $PAGE->set_url('/');
                // Create a multichoice single question.
                $mc = \test_question_maker::make_a_multichoice_single_question();
                $mc->hints = array(
                        new question_hint_with_parts(0, 'This is the first hint.', FORMAT_HTML, false, false),
                        new question_hint_with_parts(0, 'This is the second hint.', FORMAT_HTML, true, true),
                );
                $this->start_attempt_at_question($mc, 'interactiveexplain', 1);

                $rightindex = $this->get_mc_right_answer_index($mc);
                $wrongindex = ($rightindex + 1) % 3;

                // Check the initial state.
                $this->check_current_state(\question_state::$todo);
                $this->check_current_mark(null);
                $this->check_current_output(
                        $this->get_contains_marked_out_of_summary(),
                        $this->get_contains_question_text_expectation($mc),
                        $this->get_contains_mc_radio_expectation(0, true, false),
                        $this->get_contains_mc_radio_expectation(1, true, false),
                        $this->get_contains_mc_radio_expectation(2, true, false),
                        $this->get_contains_submit_button_expectation(true),
                        $this->get_does_not_contain_feedback_expectation(),
                        $this->get_tries_remaining_expectation(3),
                       $this->get_no_hint_visible_expectation());

                       $config = get_config('local_qbehaviour_interactiveexplain');
                       $this->check_output_contains($config->problemheader);

                       $this->check_output_contains($config->problemheaderdetails);

                // Save the wrong answer.
                $this->process_submission(array('answer' => $wrongindex));
                // Verify.
                $this->check_current_state(\question_state::$todo);
                $this->check_current_mark(null);
                $this->check_current_output(
                        $this->get_contains_marked_out_of_summary(),
                        $this->get_contains_mc_radio_expectation($wrongindex, true, true),
                        $this->get_contains_mc_radio_expectation(($wrongindex + 1) % 3, true, false),
                        $this->get_contains_mc_radio_expectation(($wrongindex + 1) % 3, true, false),
                        $this->get_contains_submit_button_expectation(true),
                        $this->get_does_not_contain_correctness_expectation(),
                        $this->get_does_not_contain_feedback_expectation(),
                        $this->get_tries_remaining_expectation(3),
                        $this->get_no_hint_visible_expectation());

                // Submit the wrong answer.
                $this->process_submission(array('answer' => $wrongindex, '-submit' => 1));

                // Verify.
                $this->check_current_state(\question_state::$todo);
                $this->check_current_mark(null);
                $this->check_current_output(
                        $this->get_contains_marked_out_of_summary(),
                        $this->get_contains_mc_radio_expectation($wrongindex, false, true),
                        $this->get_contains_mc_radio_expectation(($wrongindex + 1) % 3, false, false),
                        $this->get_contains_mc_radio_expectation(($wrongindex + 1) % 3, false, false),
                        $this->get_does_not_contain_submit_button_expectation(),
                        $this->get_contains_try_again_button_expectation(true),
                        $this->get_does_not_contain_correctness_expectation(),
                        new \question_pattern_expectation('/Tries remaining: 2/'),
                        $this->get_contains_hint_expectation('This is the first hint'));

                // Check that, if we review in this state, the try again button is disabled.
                $displayoptions = new \question_display_options();
                $displayoptions->readonly = true;
                $html = $this->quba->render_question($this->slot, $displayoptions);
                $this->assert($this->get_contains_try_again_button_expectation(false), $html);

                // Do try again.
                $this->process_submission(array('-tryagain' => 1));

                // Verify.
                $this->check_current_state(\question_state::$todo);
                $this->check_current_mark(null);
                $this->check_current_output(
                        $this->get_contains_marked_out_of_summary(),
                        $this->get_contains_mc_radio_expectation($wrongindex, true, true),
                        $this->get_contains_mc_radio_expectation(($wrongindex + 1) % 3, true, false),
                        $this->get_contains_mc_radio_expectation(($wrongindex + 1) % 3, true, false),
                        $this->get_contains_submit_button_expectation(true),
                        $this->get_does_not_contain_correctness_expectation(),
                        $this->get_does_not_contain_feedback_expectation(),
                        $this->get_tries_remaining_expectation(2),
                        $this->get_no_hint_visible_expectation());

                // Submit the right answer.
                $this->process_submission(array('answer' => $rightindex, '-submit' => 1));

                // Verify.
                $this->check_current_state(\question_state::$gradedright);
                $this->check_current_mark(0.6666667);
                $this->check_current_output(
                        $this->get_contains_mark_summary(0.6666667),
                        $this->get_contains_mc_radio_expectation($rightindex, false, true),
                        $this->get_contains_mc_radio_expectation(($rightindex + 1) % 3, false, false),
                        $this->get_contains_mc_radio_expectation(($rightindex + 1) % 3, false, false),
                        $this->get_does_not_contain_submit_button_expectation(),
                        $this->get_contains_correct_expectation(),
                        $this->get_no_hint_visible_expectation());

                // Finish the attempt - should not need to add a new state.
                $numsteps = $this->get_step_count();
                $this->quba->finish_all_questions();

                // Verify.
                $this->assertEquals($numsteps, $this->get_step_count());
                $this->check_current_state(\question_state::$gradedright);
                $this->check_current_mark(0.6666667);
                $this->check_current_output(
                        $this->get_contains_mark_summary(0.6666667),
                        $this->get_contains_mc_radio_expectation($rightindex, false, true),
                        $this->get_contains_mc_radio_expectation(($rightindex + 1) % 3, false, false),
                        $this->get_contains_mc_radio_expectation(($rightindex + 1) % 3, false, false),
                        $this->get_contains_correct_expectation(),
                        $this->get_no_hint_visible_expectation());

                // Process a manual comment.
                $this->manual_grade('Not good enough!', 0.5, FORMAT_HTML);

                // Verify.
                $this->check_current_state(\question_state::$mangrpartial);
                $this->check_current_mark(0.5);
                $this->check_current_output(
                        $this->get_contains_mark_summary(0.5),
                        $this->get_contains_partcorrect_expectation(),
                        new \question_pattern_expectation('/' . preg_quote('Not good enough!', '/') . '/'));

                // Check regrading does not mess anything up.
                $this->quba->regrade_all_questions();

                // Verify.
                $this->check_current_state(\question_state::$mangrpartial);
                $this->check_current_mark(0.5);
                $this->check_current_output(
                        $this->get_contains_mark_summary(0.5),
                        $this->get_contains_partcorrect_expectation());

                $autogradedstep = $this->get_step($this->get_step_count() - 2);
                $this->assertEqualsWithDelta($autogradedstep->get_fraction(), 0.6666667, 0.0000001);
    }

}
