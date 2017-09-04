<?php
// Login script
require_once 'Auth.php';

if (isset($_POST['submit'])
    && isset($_POST['email'])
    && isset($_POST['password'])) {

    if (!Auth::validate($_POST['email'], $_POST['password'])) {
        Auth::flash('email / password combination is not valid');
    }

} else {
    Auth::flash('Please fill in all fields');
}

Auth::redirect(Auth::HOME_URL);

?>
