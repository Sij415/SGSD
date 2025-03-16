<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Fetch Data</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Debug Fetch Data</h1>
    <button id="testDaily">Test Daily</button>
    <button id="testWeekly">Test Weekly</button>
    <button id="testMonthly">Test Monthly</button>
    <button id="testYearly">Test Yearly</button>

    <h2>Response:</h2>
    <pre id="response"></pre>

    <script>
        $(document).ready(function() {
            function fetchData(period) {
                $.ajax({
                    url: 'fetch_data.php',
                    method: 'GET',
                    data: { period: period },
                    dataType: 'json',
                    success: function(data) {
                        $('#response').text(JSON.stringify(data, null, 2));
                    },
                    error: function(xhr, status, error) {
                        $('#response').text("Error: " + error + "\nResponse Text:\n" + xhr.responseText);
                    }
                });
            }

            $('#testDaily').click(function() {
                fetchData('daily');
            });

            $('#testWeekly').click(function() {
                fetchData('weekly');
            });

            $('#testMonthly').click(function() {
                fetchData('monthly');
            });

            $('#testYearly').click(function() {
                fetchData('yearly');
            });
        });
    </script>
</body>
</html>