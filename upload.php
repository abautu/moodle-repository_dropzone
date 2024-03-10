<?php
// Define the moodle required includes
require_once('../../config.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/repository/dropzone/lib.php');

// Check if an actual user is logged in (i.e. not guest)
require_login();

$max_file_size = 10*1024*1024*1024; // 10GB
$chunk_size = min(get_max_upload_file_size()/2, 5*1024*1024); // max 5MB chunk size

$fallback_upload_uuid = NULL;
$fallback_upload_message = NULL;

// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Check if a file was uploaded
        if (empty($_FILES['file'])) {
            throw new moodle_exception('nofile');
        }
        // next switch is copied from upload repository from moodle core
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_OK:
                break; // all good! no error
            case UPLOAD_ERR_INI_SIZE:
                throw new moodle_exception('upload_error_ini_size', 'repository_upload');
            case UPLOAD_ERR_FORM_SIZE:
                throw new moodle_exception('upload_error_form_size', 'repository_upload');
            case UPLOAD_ERR_PARTIAL:
                throw new moodle_exception('upload_error_partial', 'repository_upload');
            case UPLOAD_ERR_NO_FILE:
                throw new moodle_exception('upload_error_no_file', 'repository_upload');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new moodle_exception('upload_error_no_tmp_dir', 'repository_upload');
            case UPLOAD_ERR_CANT_WRITE:
                throw new moodle_exception('upload_error_cant_write', 'repository_upload');
            case UPLOAD_ERR_EXTENSION:
                throw new moodle_exception('upload_error_extension', 'repository_upload');
            default:
                throw new moodle_exception('nofile');
        }

        // Check if the chunk location is valid
        if ($_POST['dzchunkbyteoffset'] < 0 ||
            $_POST['dzchunkbyteoffset'] + $_FILES['file']['size'] > $max_file_size) {
            throw new Exception('File size exceeds maximum allowed');
        }

        // Save the chunk in the temporary file
        $filename = repository_dropzone::get_temporary_filename($_POST['dzuuid']);
        // Create the directory if it doesn't exist
        @mkdir(dirname($filename), 0700, true);
        if (!$file = fopen($filename, 'cb')) {
            throw new Exception('Failed to open file for writing');
        }
        // Write the chunk to the file
        fseek($file, $_POST['dzchunkbyteoffset']);
        fwrite($file, file_get_contents($_FILES['file']['tmp_name']));
        // Close the file
        fclose($file);
        file_put_contents($filename . '.txt', $_FILES['file']['name']);
        $fallback_upload_message = get_string('uploadedfile');
        $fallback_upload_uuid = $_POST['dzuuid'];
    } catch (Exception $e) {
        header('HTTP/1.0 Bad Request', true, 400);
        echo $e->getMessage();
        exit;
    }
}

// Start output
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('embedded');
$PAGE->requires->js_call_amd('repository_dropzone/dropzone', 'init', [
    $chunk_size, $max_file_size, $fallback_upload_uuid
]);
$PAGE->requires->css('/repository/dropzone/css/dropzone.min.css');
echo $OUTPUT->header();

// Output HTML for the dropzone.js file upload form
echo '<form action="upload.php" class="dropzone" id="fileupload" method="post" enctype="multipart/form-data">
    <div class="fallback">
    <div>
        <input name="file" type="file"/>
        <input type="submit" value="', get_string('upload'),'"/>
        <input type="hidden" name="dzchunkbyteoffset" value="0" />
        <input type="hidden" name="dzuuid" value="12345678-1234-1234-1234-', sprintf('%012x', rand()) ,'" />
    </div>
    <div>', $fallback_upload_message, '</div>
</form>';
// Display footer
echo $OUTPUT->footer();
