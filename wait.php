<?php
$x = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pause Execution</title>
    <style>
        /* Spinner animation */
        .spinner {
            margin: 50px auto;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            text-align: center;
            font-family: Arial, sans-serif;
            margin-top: 20px;
        }
        #delayed-content {
            display: none; /* Hide initially */
        }
    </style>
</head>
<body>
    <h1>Main Page</h1>
    <p>This is the main page content. Additional logic will execute after the delay.</p>
    <?php echo "x=".$x;?>

    <!-- Spinner and message -->
    <div id="spinner-container">
        <div class="spinner" id="spinner"></div>
        <div class="message" id="message">Processing, please wait...</div>
    </div>

    <script>
        // Define a function to fetch data and pause execution
        async function pauseAndResume() {
            // Show spinner and message
            document.getElementById("spinner").style.display = "block";
            document.getElementById("message").style.display = "block";

            // Make an asynchronous request to process.php
            const response = await fetch("process.php");
            const result = await response.text();

            // Hide spinner and message
            document.getElementById("spinner").style.display = "none";
            document.getElementById("message").style.display = "none";

            // Log the result
            console.log("Server response:", result);

            // Show delayed content
            document.getElementById("delayed-content").style.display = "block";
        }

        // Call the function and wait for it to complete
        pauseAndResume();
    </script>

    <!-- Delayed content -->
    <div id="delayed-content">
        <p>This content will only appear after the delay.</p>
        <?php 
        echo "x=".$x;?>
        <script>
            // Further logic that depends on the delay
            console.log("Execution resumed after the delay!");
        </script>
    </div>
</body>
</html>
