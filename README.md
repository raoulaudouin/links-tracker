# Links Tracker

Small PHP script to track how many times shared links have been clicked.

![Links Tracker Log](links-tracker-log.jpg)

## How To

To use the links tracker, copy the folder `links-tracker` anywhere on your server.

To track the link `https://bar.com/foo`, share the link `https://yourdomain.com/links-tracker?url=https://bar.com/foo`. The latter will track every click in a log and redirect to `https://bar.com/foo`. Every new link will be added as a new entry in the log.

The log can be accessed as a plain text spreadsheet (`links-tracker/log.txt`) or as a json array (`links-tracker/log.txt`).

To clear the log, go to `https://yourdomain.com/links-tracker?clear`.