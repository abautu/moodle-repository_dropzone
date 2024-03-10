# moodle-repository_dropzone

Upload files to Moodle (using dropzone.js)

This repository plugin allows you to use dropzone.js instead of the regular Moodle upload file repository.

Some benefits include:
- visual feedback of the upload status (users can see a progress bar of the upload status)
- chunked uploads (large files are splitted into smaller pieces and uploaded separately; if one chunk fails to upload, there will be automatic retries)
- can upload files larger than currently allowed settings in your load-balancer/nginx/apache (for example, your CloudFlare account allows maximum 50MB uploads, but you need to upload a 2GB course backup).
