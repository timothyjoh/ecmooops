propel-wordpress
================

WordPress Plugin to work with Propel-LMS


## How to use the MyCourses Shortcodes

```
[if has_active_enrollments]

  To begin, click on your course title below.

[else]
  If you have not purchased a course please 
    <strong><a href="/course-catalog">Buy Now</a></strong> 
    or <strong><a href="/activate-key/">Activate a Key</a>.</strong>
[/else]

[/if]
```


## How to use the Certificate Shortcuts

```
[if is_enrolled_in_this_course] 
    You are enrolled in this course. Congrats!
  [else] 
    You are not enrolled, sorry.
  [/else]
[/if]

[if is_completed_in_this_course] 
  
  You have completed this course, have a certificate.

  [propel-certificate embed_code='A9PCH9' ]

[/if]
```