<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This plugin is used to upload files using dropzone.js
 *
 * It works even with very large files, by splitting them into chunks
 * and uploading them one by one.
 *
 * @package    repository_dropzone
 * @copyright  2024 Andrei Bautu <abautu@gmail.com>
 * @author     Andrei Bautu <abautu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/repository/upload/lib.php');

/**
 * A repository plugin to allow user uploading files using dropzone.js.
 *
 * This class changes the way files are uploaded, but it delegates to repository_upload
 * (from Moodle core) the actual upload processing (check file size limits, run
 * antivirus scans, move file to final location in the file system, save data in the
 * database, etc).
 *
 * @package    repository_dropzone
 * @copyright  2024 Andrei Bautu <abautu@gmail.com>
 * @author     Andrei Bautu <abautu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_dropzone extends repository_upload {

    /**
     * This function is called by initialise_filepicker to provide the HTML
     * template for the repository.
     *
     * We reuse the filemanager_uploadform template, but we replace the HTML
     * file element with an iframe that will contain the dropzone.js uploader.
     *
     * We use a preg_replace to find and replace the file input element, because
     * the template could be changed in some themes (I never seen this, but it's
     * possible), including attributes/whitespace different from the default one
     * ('<input  type="file"/>' the two spaces is not a typo here).
     */
    public function get_upload_template() {
        global $OUTPUT;jQuery('#fileupload').parents('body')[0].scrollHeight
        $form = $OUTPUT->render_from_template('core/filemanager_uploadform', []);
        $upload_frame_url = (new moodle_url('/repository/dropzone/upload.php'))->out_as_local_url();
        $form = preg_replace(
            '!<input[^>]* type="file"[^>]*>!',
            '<input type="hidden"/><iframe src="' . $upload_frame_url . '" frameborder="0" scrolling="no" style="width:100%"></iframe>',
            $form);
        return $form;
    }

    /**
     * Computes the temporary filename for a dropzone uploads.
     *
     * Dropzone.js generates a UUID for each upload. We use this UUID to store
     * the file data for later use.
     *
     * To avoid collisions (highly unprobable) and hacker attacks (highly probable),
     * we store the files in distinct directory for each the user. In this way,
     * even if a hacker guesses the UUID (or iterates over some ranges), he
     * won't be able to access the file of other users.
     */
    public static function get_temporary_filename($uuid) : string {
        global $CFG, $USER;
        // Check if the chunk upload ID is valid
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid)) {
            throw new Exception('Invalid chunk upload ID');
        }
        return $CFG->tempdir . '/dropzone/' . $USER->id . '/' . $uuid;
    }

    /**
     * Helper function to avoid hardcoding the form element name in different places
     *
     * @return string
     */
    public static function get_form_element_name() : string {
        // Do not change this value: it is hardcoded in filepicker.js and repository_upload
        return 'repo_upload_file';
    }

    /**
     * Process uploaded file
     *
     * This function is called by file picker after the file is uploaded. On a normal file upload,
     * we would receive the actual file in the same HTTP POST request with the file metadata (author,
     * copyright, save as filename, etc).
     *
     * However, with dropzone.js, the file is uploaded before the File Picker submits its data. In
     * this case, the HTTP POST will contain the file metadata and the upload UUID generated by
     * dropzone.js. We use this UUID to retrieve the file data and filename from the temporary
     * directory and make it look like it was just uploaded, so that repository_upload (using
     * parent::upload call) can process it.
     *
     * @return array|bool
     */
    public function upload($saveasfilename, $maxbytes) {
        // find the UUID of the uploaded file
        $elementname = self::get_form_element_name();
        $upload_id = optional_param($elementname, NULL, PARAM_ALPHANUMEXT);
        if ($upload_id) {
            // if we have a UUID, we need to find the file data
            $file = self::get_temporary_filename($upload_id);
            if (is_file($file)) {
                // if we have the file data, we also need the original filename
                $filename = file_get_contents($file . '.txt');
                // fake a regular file upload and reuse code ;)
                $_FILES[$elementname] = [
                    'name' => $filename,
                    'type' => mimeinfo('type', $filename),
                    'tmp_name' => $file,
                    'error' => 0,
                    'size' => filesize($file),
                ];
            }
        }
        return parent::upload($saveasfilename, $maxbytes);
    }
}
