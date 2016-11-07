# Web-Development
random snippets of web stuff
javascript file calls the php that grabs the timestamp from the users session file. it then continues to check for other tabs updating the timeout using local storage.
timeout is set to 24 mins on the server. at 20 mins since any activity, js brings up a php modal that alerts the user theyre nearing timeout, displaying their time left, and a button to "refresh" their session.
the php grabs the users timestamp on their session file from the server. the first half is for the javascript timeout, the second half is for another page that shows active users.
