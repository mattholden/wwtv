# wwtv
Who's Watching TV Programming Test

First, let me state that the sample data I received did not include anything having to do with coursework or grades/GPA. All that was present was a Names sheet and another sheet with addresses; therefore, I did not resolve any of the feature requests dealing with that data. If updated sample data that contains those schema can be provided, or if clarification can be provided that I was intended to develop that schema myself, I will happily update and resubmit the application right away. I am submitting this now because I was not counting on a timed assignment today; I have other interviews to do today and want to be sure I don't overshoot the deadline waiting on a reply.

A brief overview of the code in play here:

- I used an off the shelf free web template, keeping with your groovy space theme. I did little to nothing of the CSS here; this is purely to give the project a little pizzazz. I take no credit for the contents of the /css, /fonts, /images, /js directories or the styling of index.php.

- The contents of the /lib directory and the Model3 class are from a proprietary framework I developed for IndieGameAlliance.com, and were reused here in the interests of delivery speed.

- To speed up queries of the data and clean up the code, I created an SQL view "v_named_students" that joins the two tables on ID. You can find it modeled in /models/VNamedStudent.php.

- In a real application, the index.php form processing code should be in a controller class; I sacrificed this architectural purity in the name of delivery speed for this demo.

- It was not clear how fully-formed you wanted this application to be, so I didn't delve into throwing jQuery front end validation on the form or anything like that. I commented in a few places where I would do so in a production application, so that you're clear that I understand the need and the appropriate use cases.


