Summary
-------
* Author: Patrick Dawkins <pjcdawkins@gmail.com>
* License: GPL
* Requires: PHP >= 5.3

DrooPHP will be a PHP library to enable counting results for an STV election.

Counts are based on the contents of a ballot (.blt) file.

An aim is to emulate Droop - the Python project. See:
http://code.google.com/p/droop/

Significant .blt parsing differences between DrooPHP and Droop:
* DrooPHP needs new lines (LF or CRLF) to separate BLT file information, as is
  customary (but not required) in the BLT standard.
* DrooPHP does not support multi-line or C-style comments /* like this */.

References
----------
* How to Conduct an Election by the Single Transferable Vote, R. A. Newland,
  F. S. Britton (Electoral Reform Society of Great Britain and Ireland, London,
  1997):
  http://www.cix.co.uk/~rosenstiel/stvrules/
* BLT File format (Droop):
  http://code.google.com/p/droop/wiki/BltFileFormat
* Reference STV rules (Proportional Representation Foundation):
  http://prfound.org/resources/reference/
* Single transferable vote (Wikipedia):
  http://en.wikipedia.org/wiki/Single_transferable_vote
* Counting Single Transferable Votes (Wikipedia):
  http://en.wikipedia.org/wiki/Counting_Single_Transferable_Votes
