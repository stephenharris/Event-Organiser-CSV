# Event Organiser CSV #
**Contributors:**      stephenharris  
**Donate link:**       http://wp-event-organiser.com/  
**Tags:** CSV, Event, import  
**Requires at least:** 3.5.1  
**Tested up to:**      3.8  
**Stable tag:**        0.1.2  
**License:**           GPLv2 or later  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

Import & export events from/to CSV format

## Description ##

This plug-in allows to import events from a CSV file into Event Organiser. You can also export events from
Event Organiser into a CSV file.

Please note that this plug-in still in **beta**. I welcome feedback, issues and pull-requests.


### Aim ###
To allow users to export / import events in CSV format between various calendar applications, and to do this flexiably 
so as to limit the number of requirements on the CSV file before it can be read correctly. To allow users to move events 
between installations of Event Organiser while preserving data that is not suported by iCal.

**In the vein of flexibility columns do not have to be in any prescribed order:** you tell the plug-in which columns pertain to what (start date, end date etc)   
after importing the file.


### How to use this plug-in ###

Once installed, go to *Tools > Import Events*. Here you can export a CSV file or select a file to import one. To import an file:
 
* Select browse and select the file, click "Upload file and import"
* All being well you should now see a preview of the CSV file, along with a drop-down option at the base of each column. If the preview looks wrong, try 
selecting a different delimiter type (comma, tab, space) at the top.
* If the first row of the CSV file is a header, select the option indicating this. The first row will then be ignored.
* At the bottom of each column select what the column represents. The options are (not all a required):
  - Title
  - Start (formatted in Y-m-d format, and also indicating time **only** if the event is not all-day)  
  - End (formatted as above)
  - Recur until (if the event recurs, the date of its last occurrence)
**  - Recurrence Schedule (if the event recurs, how it repeats:** once|daily|weekly|monthly|yearly|custom).  
  - Recurrence Frequency (if the event recurs, an integer indicating with what frequency)
  - Schedule Meta (See documentation for [eo_insert_post()](http://codex.wp-event-organiser.com/function-eo_insert_event.html), e.g. "MO,TU,THR" (weekly), "BYDAY=2MO" or "BYMONTHDAY=16" (monthly)
  - Content (HTML post content)
  - Venue (Venue slug)
  - Categories (comma seperated list of category slugs) 
  - Tags (comma seperated list of tag slugs)
  - Include dates (comma seperated list of Y-m-d dates to include from the event's schedule)
  - Exclude dates (as above, but added to the event's schedule)
  - Post Meta (an option will appear to provide the meta-key)
 * Click import.
 

### Limitations ###
Current limitations apply. See the examples folder for an archetypal CSV file 

* All dates are read using DateTime. While various formats are supported, Y-m-d (e.g. 2013-12-31) formats are **strongly** recommended
* Starts dates must be provided in Y-m-d (e.g. 2013-12-31) for all day events and also include a time-component (e.g. 2013-12-31 11:30pm) for non-all-day events. There is no 
prescribed format for the time but 24-hour time is recommended. You do not need to specify seconds.
* Include/exclude dates should be given as comma-seperated list of dates in Y-m-d format.
* Categories and tags must be given as comma-seperated list of slugs
* It does not support venue meta-data (yet)

*Please note that in theory all dates (other than the start date) can be given in any format, however, to 
ensure dates are interpreted correctly it is strongly recommended to give dates in Y-m-d format. The start 
date must be in that format so that the importer can differentriate between all-day and non-all-day events.*
 

### Future Features ###

* Support venue meta data
* Support category colours
* Add filters for developers
* Add support for UID to prevent importing an event twice (perhaps, update the event?)
* Add support 'maps' for importing from other applications (where format of exported CSV file is prescribed).
* Support generic date formatting (try to 'guess' / ask for format )


## Installation ##

1. Upload the entire `/event-organiser-csv` directory to the `/wp-content/plugins/` directory.
2. Activate Event Organiser CSV through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##


## Screenshots ##

### 1. At *Tools > Import Events* select a file to import. ###
![At *Tools > Import Events* select a file to import.](http://s.wordpress.org/extend/plugins/event-organiser-csv/screenshot-1.png)

### 2. Select delimiter, and identify each column. ###
![Select delimiter, and identify each column.](http://s.wordpress.org/extend/plugins/event-organiser-csv/screenshot-2.png)

### 3. After importing the events you'll be notified if the it was successful. ###
![After importing the events you'll be notified if the it was successful.](http://s.wordpress.org/extend/plugins/event-organiser-csv/screenshot-3.png)



## Changelog ##

### 0.1.2 ###
* Fixed spelling errors in readme

### 0.1.1 ###
* Added support for post meta
* Fixed bugt with importing Venues with "&" in the name

### 0.1.0 ###
* First release
