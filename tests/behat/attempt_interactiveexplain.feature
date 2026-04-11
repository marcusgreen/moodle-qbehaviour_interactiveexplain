@qbehaviour @qbehaviour_interactiveexplain
Feature: Attempt a quiz using the interactive with explanation behaviour
              In order to provide feedback while answering questions
              As a student
              I should see an explanation textarea when attempting a quiz with the interactiveexplain behaviour

        Background:
            Given the following "users" exist:
                  | username | firstname | lastname | email                |
                  | student1 | Student   | One      | student1@example.com |
                  | teacher1 | Teacher   | One      | teacher1@example.com |
              And the following "courses" exist:
                  | fullname | shortname | category |
                  | Course 1 | C1        | 0        |
              And the following "course enrolments" exist:
                  | user     | course | role           |
                  | student1 | C1     | student        |
                  | teacher1 | C1     | editingteacher |
              And the following "activities" exist:
                  | activity | name   | course | preferredbehaviour | idnumber |
                  | quiz     | Quiz 1 | C1     | interactiveexplain | quiz1    |
              And the following "question categories" exist:
                  | contextlevel    | reference | name           |
                  | Activity module | quiz1     | Test questions |
              And the following "questions" exist:
                  | questioncategory | qtype       | name | questiontext   | template    | hint1  | hint2  | shuffleanswers |
                  | Test questions   | multichoice | MC1  | First question | one_of_four | Hint 1 | Hint 2 | 0              |
              And quiz "Quiz 1" contains the following questions:
                  | question | page |
                  | MC1      | 1    |

        @javascript
        Scenario: The explanation textarea is visible when attempting the quiz
            Given I am on the "Quiz 1" "quiz activity" page logged in as student1
             When I press "Attempt quiz"
             Then I should see "First question"
              And I should see "Is there a problem with this question?"
              And "textarea" "css_element" should exist

        @javascript
        Scenario: A student can submit a correct answer using the interactiveexplain behaviour
            Given I am on the "Quiz 1" "quiz activity" page logged in as student1
              And I press "Attempt quiz"
             When I click on "One" "qtype_multichoice > Answer" in the "Question 1" "question"
              And I press "Check"
             Then I should see "Well done!"

        @javascript
        Scenario: A student who answers incorrectly can try again
            Given I am on the "Quiz 1" "quiz activity" page logged in as student1
              And I press "Attempt quiz"
             When I click on "Two" "qtype_multichoice > Answer" in the "Question 1" "question"
              And I press "Check"
             Then I should see "That is not right at all."
              And I should see "Hint 1."
              And I press "Try again"
              And I should see "First question"
