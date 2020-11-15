@qbehaviour @qbehaviour_interactiveexplain @javascript

Feature: Add a quiz with interactiveexplain behaviour
  In order to evaluate students
  As a teacher
  I need to create a quiz with interactiveexplain behaviour

  Background:

    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name        | Interactive behaviour quiz        |
      | Description | Test Gapfill with more than one question per page |
    And I follow "Interactive behaviour quiz"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "How questions behave" to "Interactive with explanation"
    And I press "Save and return to course"

#############################################################################
#All questions on a single page. This will check that javascript only works
#on the current question and is not applied to every question as happened
#with an early bug
##############################################################################
      And I add a "Multiple choice" to the "Interactive behaviour quiz" quiz with:
      | Question name            | Multi-choice-001                   |
      | Question text            | Find the capital cities in Europe. |
      | General feedback         | Paris and London                   |
      | One or multiple answers? | Multiple answers allowed           |
      | Choice 1                 | Tokyo                              |
      | Choice 2                 | Spain                              |
      | Choice 3                 | London                             |
      | Choice 4                 | Barcelona                          |
      | Choice 5                 | Paris                              |
      | id_fraction_0            | None                               |
      | id_fraction_1            | None                               |
      | id_fraction_2            | 50%                                |
      | id_fraction_3            | None                               |
      | id_fraction_4            | 50%                                |
      | Hint 1                   | First hint                         |
      | Hint 2                   | Second hint                        |

    And I add a "Short answer" to the "Interactive behaviour quiz" quiz with:
      | Question name        | shortanswer-001                           |
      | Question text        | What is the national langauge in France?  |
      | General feedback     | The national langauge in France is French |
      | Default mark         | 1                                         |
      | Case sensitivity     | No, case is unimportant                   |
      | id_answer_0          | French                                    |
      | id_fraction_0        | 100%                                      |
      | id_feedback_0        | Well done. French is correct.             |
      | id_answer_1          | *                                         |
      | id_fraction_1        | None                                      |
      | id_feedback_1        | Your answer is incorrect.                 |

    And I pause
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Gapfill single page quiz"
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I type "cat" into gap "1" in the gapfill question
    And I type "mat" into gap "2" in the gapfill question
    And I press "Check"

    Then I should see "Question1 feedback"
    And I should not see "Question2 feedback"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

##########################################################################################
# One question per page, which can be used to check the status of the question
# if you page forward and backwards between pages (though I don't think it does at the moment)
##########################################################################################
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name        | Test quiz name        |
      | Description | Test quiz description |

    And I add a "Gapfill" question to the "Test quiz name" quiz with:
      | Question name                      | First question                         |
      | Question text                      | The [cat] sat on the mat               |
      | General feedback                   | General feedback cat mat|

    And I add a "Gapfill" question to the "Test quiz name" quiz with:
      | Question name                      | Second question                         |
      | Question text                      | The [cow] jumped over the [moon]        |
      | General feedback                   | General feedback cow moon|

    And I press "Repaginate"
    And I press "Go"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test quiz name"
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I type "cat" into gap "1" in the gapfill question
    #And I should see "Answer saved"
    And I press "Next page"
    Then I should see "Question 2"
    And I type "cow" into gap "1" in the gapfill question
    And I type "moon" into gap "2" in the gapfill question
    And I press "Finish attempt ..."
    And I press "Submit all and finish"

 # @javascript
  Scenario: Add and configure small quiz and perform an attempt as a student with Javascript enabled
    Then I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I follow "Finish review"
    And I log out