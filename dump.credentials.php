<?php
/**
 * Dumps the contents of the environment variable GOOGLE_CREDENTIALS_BASE64 to
 * a file.
 *
 * To setup Travis to run on your fork, download a service account credentials
 * json file, base64 encode it, and store it in the environment variable.
 */
file_put_contents(getenv('GOOGLE_APPLICATION_CREDENTIALS'),
    base64_decode(getenv('GOOGLE_CREDENTIALS_BASE64')));