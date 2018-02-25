infinetix_timesheet
===================

A Symfony project created on May 16, 2016, 8:42 pm.


Project Update and Revision
===========================
Jun 19th, 2017 through Aug 25, 2017 <br>
Sep 27th, 2017 through Present

Brian Mize <br>
brianm@infinetix.com <br>
brian.r.mize@gmail.com


Dump, Checkout, and Update Script for Production:
=================================================

tss	- Needs to be located in /usr/local/bin/ so that it can run from the command line, else run with ./tss [arg] 

$tss [arg]

tss dump	- Removes the Timesheet directory from /var/www/ts/

tss co		- Checkouts out the Timesheet from the repository and runs all commands needs to prepare the folder for running in production, 
			  clear and warm up the cache, and sets the needed file permissions.

tss up		- Updates the Timesheet directory and runs all commands needs to prepare the folder for running in production, 
			  clear and warm up the cache, and sets the needed file permissions.


