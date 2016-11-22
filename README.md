UMN Global Message Plugin

Utilizing community input, this plugin allows for two different types of messages:
Popup messages
Inline messages



And allow for the following features per message:
Name
The name of the message, for identification.

Message
The actual content of the message.

Type of message
Popup or inline.

Custom CSS
Style to be added to the div containing the message.

Date start
The date (and time) that the message will begin displaying.

Date end (or no date)
The date (and time) that the message will cease displaying.

Display each session or display once
If each session is chosen, the message will display each time the user logs in, until they dismiss it, then will display the next time they log in. In this case, the user is given the option to permanently disable the message.
If once is chosen, the message will display until the user dismisses it, then never again.

Page target
The messages can target a specific page or subset of pages to display on, based on the body ID. Other can be chosen and a custom target can be placed in the input.



A non-admin can view all of the active messages by clicking on the link in the message.



The interface to add messages and the interface to hide the messages are both done exclusively via AJAX and are therefore less "page load" intensive!



To install:  
Add this directory under /local/ (/local/umnglobalmessage/).  
Add the following code in your banner.php file (or relevant layout file) where you want the inline message displayed:  
if (file_exists($CFG->dirroot . '/local/umnglobalmessage/display.php')) {  
    include($CFG->dirroot . '/local/umnglobalmessage/display.php');  
}  
Upgrade your database.  
That's it!  
