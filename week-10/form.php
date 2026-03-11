<html>
    <body>
        <h2>Upload CSV file</h2>
        <form method="POST" enctype="multipart/form-data" action="csv_processor.php">  
            <input type="file" name="file" required>
            <br><br>
            <input type="text" name="survey_name" placeholder="Survey Name" required>
            <br><br>
            <input type="submit" value="Generate Questionaire">
        </form>
    </body>
</html>
