# Interactive with explanation question behaviour
by Marcus Green

## Introduction

This moodle question behaviour is based heavily on Tim Hunts
deferred feedback with explanation question behaviour.
https://github.com/timhunt/moodle-qbehaviour_deferredfeedbackexplain

This question behaviour is  like the interactive with multiple attempts behaviour , but with an additional text box where students can give a reason why they gave the answer they did.

No attempt is made to automatically grade the explanation, nor is it required.
However, it may be used in various ways, for example

1. The teacher may want to manually edit the grades where the student gave a wrong answer, to give partial credit if the student used the right method or approach.
2. The student might want to explain their thinking, so that later, when the results and feedback are revealed, they are reminded of what they were thinking at the time, and so can reflect more deeply.

### How to install

From the command prompt change to
yourmoodle/question/behaviour
type:
```
git clone https://github.com/marcusgreen/moodle-qbehaviour_interactiveexplain interactiveexplain

```