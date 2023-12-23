# Sample of Passwordless Login for Filament v3.1

## This is sample code, NOT a fully working app, and is NOT a plugin
This is a demo for implementing passwordless login via email links, using Filament 3.1

Best way to understand the code is to look at the commit history in this repo. 

Some context:
- these files are copied from a production repo, and altered slightly to simplify them and remove domain-specific 'context'
- **to be clear: this is not a fully-working app**
- the site this was used on holds a db table of Members who are allowed to Vote on various things for the company.
- The Members table is only used as a frame of reference, to determine if someone should be allowed to login.
- When a person attempts to access the site, they are taken to a login page, where they only enter their email address.
- If the email address matches an existing User, they are sent an email containing a signed URL which logs them in, with RememberMe set. 
- If the email address is not in the Users table, then we lookup in the Members table (and other tables, not in this demo, used for other statuses/stages), and if not found we display the default "sorry, bad email address".  If it IS found, then we create a User model record for them, and send them the signed URL via email. 
- There is some logic to ensure that the URL they are directed to after login is sensible for the type of user they are ... else a 403 may be triggered by the User::canAccessPanel() logic or the Panel's own security settings (no Panels provided in this demo).
- Custom 401, 403, 404 error pages are included in this demo as a simple way to showing how to help redirect a user who accesses either an expired page, or a page they don't have access to, or a page they "used to" have access to but can no longer access due to Domain logic status changes. (A blank 403-Forbidden page isn't very friendly, and is hard to recover from.)

Inspiration for the passwordless login came from a Filament-v2 package by BradyRenting, and some other similar packages ... all which required some alterations to work with Filament v3.

Apologies if anything is missing from this demo when trying to copy relevant files to this separate repo. 

License: MIT. Free to use and alter according to your needs.
If you come up with something to add to it which can benefit others using Filament, please package it up and share it. Even if it's just as a demo repo such as this one. Or try your hand at packaging it for easy installation as a Plugin to Filament! 
