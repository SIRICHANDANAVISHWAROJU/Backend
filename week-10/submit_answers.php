<?php

echo "<h2>Your Submitted Answers</h2>";

foreach ($_POST as $question => $answer) {

    echo "<p>$question : $answer</p>";

}

?>