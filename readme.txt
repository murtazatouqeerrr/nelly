1. Urgent: SSL certificate to enable student test account
2. Menu organization:
-improve overall organization
-there are two different Certificates buttons pointing to certificates page:
https://course.dummiestrafficschool.com/admin/certificates
-https://course.dummiestrafficschool.com/admin/florida-email-templates button only
says email templates, lives under EMAIL & NOTIFICATIONS which is not a
florida-specific menu bracket. Florida specific items should be grouped in a clean and
useful way
-broken pages: https://course.dummiestrafficschool.com/dicds/provider-menu ,
Symfony\Component\Routing\Exception\RouteNotFoundException,
https://course.dummiestrafficschool.com/admin/customers/segments
Illuminate\Database\QueryException
3. Student security questions
-should be able to be timed or untimed
-should be able to edit questions
-no place on admin dashboard to edit security questions
4. Course details https://course.dummiestrafficschool.com/course-details/florida_courses/1
-duration is blank only says “min” for all courses
-review says Anonymous
-”No reviews yet. Be the first to review this course!” no button to add a review, even
though the account has enrolled in course.
5. Manage Courses https://course.dummiestrafficschool.com/create-course
-should be able to sort/filter by state
6. Enrollments/Student Enrollments
https://course.dummiestrafficschool.com/admin/enrollments
-only shows enrollments under Super Admin (logged into super admin). But there are
many other users as shown on https://course.dummiestrafficschool.com/admin/users
Therefore the functionality of this whole page cant be accessed for all users unless we
log into their account.
7. Users/user access
https://course.dummiestrafficschool.com/admin/user-access shows locked accounts, but
there is no place to lock an account on this page or on user page
https://course.dummiestrafficschool.com/admin/users under edit.
8. Page duplicates under Florida:
Florida courses https://course.dummiestrafficschool.com/admin/florida-courses this page
shows courses from every state, not just florida. What was the functionality supposed to
be here? Still is not sorted by state.
Florida certificates https://course.dummiestrafficschool.com/admin/florida-certificates
same thing here. Shows all certificates, not just florida. What is the functionality
supposed to be? Seems to be a minimal duplicate of this page
https://course.dummiestrafficschool.com/admin/certificates
Same issue: florida-security
https://course.dummiestrafficschool.com/admin/florida-security is exact same as
security dashboard https://course.dummiestrafficschool.com/admin/security-dashboard
9. Account Security https://course.dummiestrafficschool.com/admin/account-security
-no place to enable 2 factor authentication
10. Page layout issue interferes with functionality. Menu sidebar overlaps page, unable to
toggle to view the page. Appears on Manage Schools, Manage Instructors, Order
Certificates https://course.dummiestrafficschool.com/dicds/certificates/order, and
Distribute Certificates, Reports
https://course.dummiestrafficschool.com/dicds/reports/menu, and Web Service Info .
Also why are these buttons under email and notifications? Need improved menu
organization.
11. User consent. We have many users, but no consents are recorded
https://course.dummiestrafficschool.com/admin/user-consents
People should not be able to create accounts and take the course if they dont agree to
our terms and conditions
12. Course timers not configured yet? Why is this not synced to course duration data? Is it a
placeholder page?
13. Support tickets. Created test support ticket via admin. No place to edit or respond to the
ticket.
14. FAQs home page. Buttons are not responsive. Florida shows first regardless of selected
state. Improve selector buttons to a dropdown to accommodate adding more states.
https://course.dummiestrafficschool.com/admin/faqs FAQs populated here do not
populate on the home page or the course enrollment page. For example
https://dummiestrafficschool.com/course-details/?region=MO&course_id=3 shows
california-specific answers. What is the point then? Do these faqs display somewhere
else?
15. Question Bank: nothing shows up, even when a specific course is selected. Where are
the final exams and quizzes populated?
https://course.dummiestrafficschool.com/admin/question-banks
16. Free response final exam and intra-course questions. There is currently no visible
functionality on the dashboard that allows the student to type their own response to a
question (not multiple choice). Add this functionality. Disable copy and paste for free
response questions on student end. Enable us to set a minimum word count for sufficient
answers. Create admin grading platform for free response questions where student will
receive score and feedback on their dashboard, and possibly allow revisions.
17. Random question selection from pool of questions. Currently there is no visible
functionality to make a quiz or final exam randomly selected from a pool of questions.
Enable us to designate number of questions randomly selected form pool for both
quizzes and final exams.
18. Course content
-Enable custom coding blocks. Need functionality for students to click to reveal, click
checkboxes, click to flip card.