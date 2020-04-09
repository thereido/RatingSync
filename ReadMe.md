RatingSync

A website for rating movies, tv series and episodes.

Features
=============
- Account registration (disabled by default)
- Search titles
- Rate titles
- Users manage lists
- Filter viewing ratings & lists

Noticeable Missing Features
===========================
- Change/reset password (not implemented)
- Browsing for titles (not implemented)
- Search by people or year (not implemented)

Requirements
==============
- PHP 5.6
- MySQL 5.6
- cURL
- PHPUnit 8.4
- OMDbAPI key http://omdbapi.com/apikey.aspx

Setup
==============
- Enable php extension mysqli in php.ini
- Enable php extension curl in php.ini
- Add an include_path location for this site in php.ini or use what is already there
- Copy <repo path>/DomainConstants.php to <include_path>
- Update your values in DomainConstants.php in <include_path>
- Make these directories writable for the web server user
    - <doc_root>/RatingSync/php/output
    - <doc_root>/RatingSync/image
- If you have a favicon image copy it to the location you set in DomainConstants.php
- If you have logo image copy it to <doc_root>/RatingSync/image/logo.png
- Change http://localhost:8080 to your host URL in these javascript files
    - <doc_root>/RatingSync/Chrome/constants.js
    - <doc_root>/RatingSync/Chrome/popup.html
- Create a database using the values you set in DomainConstants.php
- Create tables using <repo path>/sql/db_tables_create.sql
- Set up initial data using <repo path>/sql/db_insert_initial.sql

Enable registering users
========================
Change <repo path>/RatingSync/php/Login/index.php
  - Uncomment a block marked as "UNCOMMENT when you are ready to register users"
  - Remove the "hidden" attribute in elements
    - The parent div of id="register-form-link"
    - form id="register-form"
    - form id="verify-form"
