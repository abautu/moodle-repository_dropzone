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
 * @package    repository_dropzone
 * @copyright  2024 Andrei Bautu <abautu@gmail.com>
 * @author     Andrei Bautu <abautu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_dropzone extends repository_upload {

    public function get_upload_template() {
        global $OUTPUT;
        $form = $OUTPUT->render_from_template('core/filemanager_uploadform', []);
        $upload_frame_url = (new moodle_url('/repository/dropzone/upload.php'))->out_as_local_url();
        $form = preg_replace(
            '!<input +type="file"/>!',
            '<input type="hidden"/><iframe src="' . $upload_frame_url . '" frameborder="0" scrolling="no" style="width:100%"></iframe>',
            $form);
        return $form;
    }

    public static function get_temporary_filename($uuid) {
        global $CFG, $USER;
        // Check if the chunk upload ID is valid
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid)) {
            throw new Exception('Invalid chunk upload ID');
        }
        return $CFG->tempdir . '/dropzone/' . $USER->id . '/' . $uuid;
    }

    public static function get_form_element_name() {
        return 'repo_upload_file'; // hardcoded in filepicker.js and repository_upload
    }

    /**
     * Process uploaded file
     * @return array|bool
     */
    public function upload($saveasfilename, $maxbytes) {
        $elementname = self::get_form_element_name();
        if (!empty($_POST[$elementname])) {
            $file = self::get_temporary_filename($_POST[$elementname]);
            if (is_file($file)) {
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
