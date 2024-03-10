# moodle-repository_dropzone

## Description

Upload files to Moodle (using dropzone.js)

This repository plugin allows you to use dropzone.js instead of the regular Moodle upload file repository.

## Why use this plugin?

Some benefits include:
- visual feedback of the upload status (users can see a progress bar of the upload status)
- chunked uploads (large files are divided into smaller pieces and uploaded separately; if one chunk fails to upload, there will be automatic retries)
- can upload files larger than currently allowed settings in your load-balancer/nginx/apache (for example, your CloudFlare account allows maximum 50MB uploads, but you need to upload a 2GB course backup).
- it has some fallback mechanism, in case Dropzone.js fails to load

## Installation

1. Copy the repository folder to your /repository/ folder in your Moodle installation.
2. Visit the notifications page to install the plugin.
3. Go to Site administration > Plugins > Repositories > Manage repositories and enable the plugin.
4. Go to a course and add a file resource. You should see the dropzone.js option in the file picker.
5. Try to upload (via click or drag-and-drop) a large file (e.g. 1GB) and see how it works.

## Troubleshooting
1. If you see the repository, but nothing shows in the file upload area (where dropzone.js widget is supposed to be), some reasons might be:
 - your site blocks loading itself in an iframe (via X-Frame-Options or Content-Security-Policy headers - you can check this in the browser console)
 - your site blocks loading any iframe content (via Content-Security-Policy headers - you can check this in the browser console)
 - your theme has some custom CSS for Moodle embedded pages (which moves the dropzone.js widget out of the visible area)
