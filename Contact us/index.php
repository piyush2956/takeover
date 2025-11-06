<?php
if (isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    echo "<h1>Thank you for contacting us, $name!</h1>";
    echo "<p>Your email: $email</p>";
    echo "<p>Your message:</p>";
    echo "<p>$message</p>";

    // Optionally, you can store the information in a file
    $data = "Name: $name\nEmail: $email\nMessage: $message\n\n";
    file_put_contents('contact_requests.txt', $data, FILE_APPEND);
}
?>
