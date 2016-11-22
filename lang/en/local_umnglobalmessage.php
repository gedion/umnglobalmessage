<?php
$string['actions'] = 'Actions';
$string['addmessage'] = 'Add new message';
$string['admin'] = 'All admin pages';
$string['allroles'] = 'All roles';
$string['banner'] = 'Banner';
$string['buttons'] = 'Buttons';
$string['category'] = 'Category';
$string['category_help'] = 'Select the category to target. This will only be utilized if the target is set to category';
$string['copyof'] = 'Copy of ';
$string['course'] = 'Course home';
$string['css'] = 'Message CSS';
$string['css_default'] = 'background-color: #fff;
padding: 7px;';
$string['css_help'] = 'Write the necessary CSS here in appropriate CSS format. Suggested CSS would be padding and background-color. THis CSS will be added as inline style to the enclosing div of the message.';
$string['currentmessage'] = 'Update message';
$string['dateend'] = 'Date end';
$string['dateend_help'] = 'This is the date and time that the message will cease to display, if enabled. If the "No end date" box is checked, the message will have no end date.';
$string['datestart'] = 'Date start';
$string['datestart_help'] = 'This is the date and time that the message will begin to display, if enabled.';
$string['dateserror'] = 'Date end must be after date start';
$string['description'] = 'Description';
$string['description_help'] = 'This is the description of the message that will be displayed to only those who can edit the message. This will be visible on hover of the message name.';
$string['disable'] = 'Disable';
$string['disabled'] = 'Disabled';
$string['dismiss'] = 'Dismiss';
$string['dismissing'] = 'Dismissing buttons';
$string['dismissing_help'] = 'Choose which option to provide for the dismissing options. If "Buttons" is chosen, then the user will be given a button or two buttons (depending on the type of display, session or once). If "X" is chosen, then the user will only always be given an "X" button to dismiss once.';
$string['dismissforever'] = 'Dismiss forever';
$string['duplicatemessage'] = 'Duplicate message';
$string['duplicatename'] = 'A message with this name already exists. Please use a unique name';
$string['editmessage'] = 'Edit message';
$string['enable'] = 'Enable';
$string['enableconflict'] = 'Enabling this message will conflict with active message(s): {$a}, because they will always target the same page, have the same display format, target the same role, and have overlapping dates. Check "Force enable" and press "Submit" if you wish to disable {$a} and enable this message instead.';
$string['enabled'] = 'Enabled';
$string['enablemessage'] = 'Enable this message';
$string['enablemessage_help'] = 'Checking this box will cause this message to be enabled, and will disable all other messages.';
$string['emptymessage'] = 'The message must exist';
$string['emptyname'] = 'The message must have a name';
$string['emptyothertarget'] = 'Since "other" was selected above, this value cannot be left empty.';
$string['everysession'] = 'Every session';
$string['forceenable'] = 'Force enable';
$string['forceenable_help'] = 'Checking this box will force this message to enable, and disable the above message(s). Leaving this box unchecked will check the latest settings against the other messages and return with an error if there is still a conflict.';
$string['forceupdate'] = 'Force update';
$string['forceupdate_help'] = 'Checking this box will cause the updated message to display for all users, including those who have already dismissed it. If this is not checked, then the users who have dismissed the message already will not see the updated version.';
$string['frequency'] = 'Frequency';
$string['general'] = 'General';
$string['gradebook'] = 'Gradebook';
$string['invalidaccess'] = 'You do not have permission to do that. If this error repeats, contact your developer.';
$string['invalidcharacters'] = 'The value supplied in this field contains invalid character(s). Please use alphanumeric only (a-z, 0-9).';
$string['longname'] = 'The provided name is too long. Please use one that is fewer than 255 characters.';
$string['longname_duplicate'] = 'Attempting to duplicate the message generated a name that was longer than 255 characters. Please shorten the name(s) of the message(s).';
$string['malformedaction'] = 'The action parameter provided was malformed. Please try again.';
$string['message'] = 'Message Content';
$string['messageprovider:messages'] = 'Global messages';
$string['message_help'] = 'This is the message that will be displayed. Feel free to use any rich text editing, but avoid using images.';
$string['myhome'] = 'My home';
$string['name'] = 'Message name';
$string['name_help'] = 'This is to be a unique short name for this message. This name will be displayed on the table for later reference. Alphanumeric only.';
$string['newdates'] = 'Set new dates';
$string['newmessage'] = 'New message';
$string['nodescription'] = 'No description was provided.';
$string['norecord'] = 'No record was found for this message. It is likely that the message was deleted before you clicked on it. If the message is still on this page, please try again. If this error persists, please contact your developer to view the logs.';
$string['hasend'] = 'Use end date';
$string['nomessages'] = 'There are currently no messages saved.';
$string['off'] = 'Off';
$string['once'] = 'Once';
$string['othertarget'] = 'Custom target';
$string['othertarget_help'] = 'What is entered here will be compared to the body ID and class of the page. Use "#" to indicate ID and "." to indicate class. Use a comma to separate the different targets, and a "|" to specify one target OR the other. The body ID and class MUST match all of them. The page IDs always start with "page-", therefore, if ID is being targeted, this value should start with "#page-". For example:<br><br>
&lt;div id="my-id" class="my-class my-other-class"&gt;&lt;/div&gt;<br><br>
Will be matched by:<br>
\#my-id,.my-class,.my-other-class<br>
OR:<br>
\#my-id,.my-class<br>
OR:<br>
\#my-id,.my-class,.not-my-other-class|.my-other-class<br><br>
But not:<br>
\#my-id,.my-class,.not-my-other-class';
$string['pluginname'] = 'UMN Global Message';
$string['popup'] = 'Type';
$string['popup_form'] = 'Display as popup';
$string['popup_form_help'] = 'If checked, the message will display as a popup with certain ingrained CSS, plus CSS added above. If not checked, the message will display above the banner at the top of the page.';
$string['popup_table'] = 'Popup';
$string['remove'] = 'Remove message';
$string['userrole'] = 'Role to target';
$string['userrole_help'] = 'Which role to target with this message. This will then cause the message to display deeper within a course, as there are very few site-wide roles.';
$string['session'] = 'Display every session';
$string['session_help'] = 'Checking this box will cause this message to display each time the user starts a new session, even if they have dismissed it before. If this box is not checked, the message will display for the user until dismissed, then never again. Of course, changing the message and forcing an update will reset the message and display it again.';
$string['sitewide'] = 'Site wide';
$string['status'] = 'Status';
$string['target'] = 'Page targeted';
$string['target_help'] = 'Select the area within Moodle to target. The available options are:
<ul>
<li>Site: Will display on every page.</li>
<li>My home: Will display ONLY on "My home" page.</li>
<li>Course home: Will display ONLY on course home pages. Will NOT display on modules within courses.</li>
<li>Gradebook: Will display on all pages within the gradebook.</li>
<li>All admin pages: Will display on all admin pages.</li>
<li>Other: Will allow for any target desired. See "Custom target" help icon.</li>
</ul>';
$string['viewcurrent'] = 'View all messages';
$string['x'] = 'Sprite (X)';
