=== Study Plan Module ===

The Study Plan Module v 1.1.1 (2015020200) is a 
Moodle module plugin designed for use with 
Moodle v 2.5+ (2013050100).

=== Installation ===

* If you have the zip file go to 
  Site Admin > Plugins > Install Add-ons or use this link:
  http://YOURSERVER/admin/tool/installaddon/index.php
  see here for more details about installing plugins
  http://docs.moodle.org/25/en/Installing_plugins
  * Caution: If you are running Moodle in a VM or on a lightweight server
    plugin installation can be troublesome. Make sure you have a 
    good backup ready and make sure the VM has at least 8GB (or more) ram assigned.
* If you have source code access drop the directory into the mod subdirectory

=== Usage ===

* The study plan module provide a dynamic study plan for a student based on the 
  results of a quiz or the completion of other quizes and activities
* For students the study plan presents a layout calculated when the page is
  displayed so that it always reflects the student's current state of progress.
  * Students click on the Study Plan (or study plan's name set by the admin)
    link in the standard navigation
  * Students see a layout as administered by a teacher or course admin
* For teachers with review access to the study plan additional features become
  active
  * Teachers will see "Review" at the bottom of the Study Plan Administration
    block in the standard navigation
  * Teachers will see groups (if the course uses groups) and the students within
    those groups, and may change the group by selecting from the list
  * By selecting the student from the list the teach can see the student's
    study plan exactly as it will appear to the student
* For teachers with assign access to the study plan additional features become
  active
  * Teachers will see "Review and Assign" at the bottom of the Study Plan 
    Administration block in the standard navigation
  * The review page will behave the same as outlined above, but will add an
    "Assign" button so that the teacher can assign a task/activity to
    override the results of the evaluation
  * Teacher-assigned tasks can be removed by clicking the "Remove" button

=== Configuration ===

* The study plan must be configured before it can provide feedback for students
* To begin with, standard moodle module name and description fields are
  available to provide normal content elements for course pages
* The study plan is tied to the results of a particular quiz within the course
* Then rows of content can be configure in the "Content" section of the page
  * Each row can be 1 of 2 types: Headline, Evaluate
  * Type: Headline
    * Headlines contain only content to displayed as a headline to break up the
      results
    * Though you can store data in any of the other fields they will not be used
  * Type: Evaluate
    * Evaluation row are always displayed, but their formatting varies depending
      on the reuslts of the evaluations
    * Select what to count
      * Count types: Tags (for now -- maybe more later)
    * How to evaluate (> >= < <= %), either as a parituclar score or as a
      perncetage of total responses within that type (matches that same Tag)
    * What course activity to link to
    * What course activity to mark this as complete anyway (overrides evaluation)
    * If it evaluates to true the display of the activity will show it as assigned
    * It can be override by the instructor

=== Admin ===

* Because the studyplan was built as a standard Moodle module you should be able
  to use them across courses or even multiple times within a single course
* More Details Coming Soon

=== Tests ===

* Coming Soon

=== History ===
0.8.0 Initial functionality
0.8.1 Added groups support
0.8.2 Backup and restore support
0.8.3 Groups menu restricted, Comma tags list 
0.8.4 Minor text change
0.8.5 CSS Tweak, Form persistance on add in modform, 
      Quiz link when not finished fixed on view, review
0.8.6 CSS Tweak
0.8.7 Rebuilt index.xml to make upgrading work again
0.8.8 Major bug fix: removed unused API stub functions that were being called,
      replaced tag pulls to use question ids, changed index.xml to no use empty
      char default
1.0.0 Minor text string tweek, and version number bump to 1.0.0
1.0.1 Properly call completion get_data while passing the user id
1.0.2 Repaired path to intro for view & review
1.1.0 Added Progress tracking (% complete, stored in table, updated with cron too)