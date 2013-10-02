<?php

function getUrlSIx39($sample) {
	global $displayname;
	global $idName;

	if ( isset($_GET['url']) ) return $_GET['url'];


	if ( $displayname ) {
		echo("<p>&nbsp;</p><p><b>Hello $displayname</b> - welcome to the autograder.</p>\n");
	}
	echo('<form>
		Please enter the URL of your web site to grade:<br/>
		<input type="text" name="url" value="'.$sample.'" size="100"><br/>
		
		<input type="checkbox" name="grade">Send Grade (leave unchecked for a dry run)<br/>
		<input type="submit" value="Evaluate">
		</form>');
	if ( $displayname ) {
		echo("By entering a URL in this field and submitting it for 
		grading, you are representing that this is your own work.  Do not submit someone else's
		web site for grading.
		");
	}

	echo("<p>You can run this autograder as many times as you like and the last submitted
	grade will be recorded.  Make sure to double-check the course Gradebook to verify
	that your grade has been sent.</p>\n");
	exit();
}
