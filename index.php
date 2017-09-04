<?php
require_once 'app/auth/Auth.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Currency Converter</title>

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="css/index.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
</head>
<body>

    <div class="wrapper">
        <h1>Currency Converter</h1>

        <?php if (!Auth::check()) { ?>
        <div class="login-box">
            <p>
                Some nice introduction here. Could be any text to your liking.
            </p>
            <?php foreach (Auth::getMessages() as $message) { ?>
                <p class="login-message-red"><?php echo $message; ?></p>
            <?php } ?>
            <form action="app/auth/login.php" method="post">
                <h4>Email</h4>
                <input type="email" name="email">
                <h4>Passowrd</h4>
                <input type="password" name="password">
                <input type="submit" name="submit" value="Login" class="mat-button login-button">
            </form>
        </div>
        <?php } else { ?>
        <p>
            Welcome <?php echo Auth::getUsername(); ?>. Click <a href="app/auth/logout.php">here</a> to logout.
        </p>

        <!-- Currency from box -->
        <div class="currency-from-box">
            <h3>Currency I have</h3>
            <select class="select-currency-from" name="currency-from"></select>
            <h4>Amount</h4>
            <input type="number" name="currency-from-amount" value="1" class="amount-from">
        </div>

        <!-- Currency to box -->
        <div class="currency-to-box">
            <div class="swap-row">
                <h3>Currency I want</h3>
                <button type="button" name="swap-button" class="mat-button swap-button">Swap <i class="fa fa-exchange"></i></button>
            </div>
            <select class="select-currency-to" name="currency-to"></select>
            <h4>Amount</h4>
            <input type="number" name="currency-from-amount" disabled class="amount-to">
        </div>

        <div class="clearfix"></div>

        <!-- Convert button -->
        <div class="convert-button-box">
            <button type="button" name="convert-button" class="mat-button convert-button">Convert</button>
        </div>

        <!-- Conversion rate info box -->
        <div class="conversion-rate-box">
            <div class="conversion-rate-info">Conversion rates as on <span class="conversion-rate-date">01-09-2017</span></div>
            <div class="conversion-rate-from-box">
                <span class="conversion-rate-from">EUR/EUR</span>
                <span id="conversion-rate-from-value" class="rate-green">1</span>
            </div>
            <div class="conversion-rate-to-box">
                <span class="conversion-rate-to">EUR/EUR</span>
                <span id="conversion-rate-to-value" class="rate-blue">1</span>
            </div>
        </div>

        <!-- Graph -->
        <div class="chart-box">
            <canvas id="currency-chart" class="currency-chart"></canvas>
        </div>
        <?php } ?>
    </div>

</body>
<?php if (Auth::check()) { ?>
<!-- Jquery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>

<script type="text/javascript">

    // Variable init
    var ctx = $('#currency-chart');
    var chart = null;
    var options = {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: false,
                }
            }]
        }
    };
    var currencies = [
        {id: 'EUR', text: 'EUR (Euro)'},
        {id: 'USD', text: 'USD (US Dollar)'},
        {id: 'GBP', text: 'GBP (United Kingdom Pound)'},
        {id: 'CAD', text: 'CAD (Canada Dollar)'},
        {id: 'SGD', text: 'SGD (Singapore Dollar)'},
        {id: 'SEK', text: 'SEK (Sweden Krona)'},
        {id: 'JPY', text: 'JPY (Japanese Yen)'},
        {id: 'AUD', text: 'AUD (Australian Dollar)'},
        {id: 'CHF', text: 'CHF (Swiss Franc)'},
        {id: 'CNY', text: 'CNY (Reminbi)'},
        {id: 'NZD', text: 'NZD (New Zealand Dollar)'},
        {id: 'MXN', text: 'MXN (Mexican Peso)'},
        {id: 'HKD', text: 'HKD (Hong Kong Dollar)'},
        {id: 'NOK', text: 'NOK (Norwegian Krone)'},
        {id: 'KRW', text: 'KRW (South Korean Won)'},
        {id: 'TRY', text: 'TRY (Turkish Lira)'},
        {id: 'RUB', text: 'RUB (Russian Ruble)'},
        {id: 'INR', text: 'INR (Indian Rupee)'},
        {id: 'BRL', text: 'BRL (Brazilian Real)'},
        {id: 'ZAR', text: 'ZAR (South African Rand)'},
    ]

    $(document).ready(function() {
        // Setup Select2
        $('.select-currency-from').select2({
            data: currencies
        });
        $('.select-currency-to').select2({
            data: currencies
        });

        // Add event handlers
        $('.convert-button').click(function() {
            convertAmount();
        });
        $('.swap-button').click(function() {
            swap();
        });
        $('select').on('change', function() {
            updateChart();
            convertAmount();
        });

        // Initialise UI with data
        createChart();
        setConversionDate();
        convertAmount();
    });

    /**
     * Sets the current date in the UI
     */
    function setConversionDate() {
        var date = new Date();
        var dateString =
            + ('0' + date.getDate()).slice(-2);
            + '-'
            + ('0' + (date.getMonth()+1)).slice(-2)
            + '-'
            + date.getFullYear()
        $('.conversion-rate-date').text();
    }

    /**
     * Sets the conversion rate info on the screen. (e.g. CUR1 / CUR2 : number).
     * @param conversionRate Conversion rate
     */
    function setConversionRateInfo(conversionRate) {
        // Get information
        var currencyFrom = $('.select-currency-from').select2('data')[0].id;
        var currencyTo = $('.select-currency-to').select2('data')[0].id;
        var reversedConversionRate = round(1/conversionRate, 3);

        // Render new information
        $('.conversion-rate-from').text(currencyFrom + '/' + currencyTo);
        $('#conversion-rate-from-value').text(conversionRate);
        $('.conversion-rate-to').text(currencyTo + '/' + currencyFrom);
        $('#conversion-rate-to-value').text(reversedConversionRate);

        // Set correct colour
        if (conversionRate >= 1) {
            $('#conversion-rate-from-value').attr('class', 'rate-green');
            $('#conversion-rate-to-value').attr('class', 'rate-blue');
        } else {
            $('#conversion-rate-from-value').attr('class', 'rate-blue');
            $('#conversion-rate-to-value').attr('class', 'rate-green');
        }
    }

    /**
     * Swaps the current selected currencies and updates all UI components
     */
    function swap() {
        var amountFrom = $('.amount-from').val();
        var amountTo = $('.amount-to').val();
        var keyCurrencyFrom = $('.select-currency-from').select2('data')[0].id;
        var keyCurrencyTo = $('.select-currency-to').select2('data')[0].id;

        $('.amount-from').val(amountTo);
        $('.amount-to').val(amountFrom);
        $('.select-currency-from').val(keyCurrencyTo).trigger('change');
        $('.select-currency-to').val(keyCurrencyFrom).trigger('change');
    }

    /**
     * Converts the 'currency I have' amount to the correct new amount
     */
    function convertAmount() {
        sendAjaxRequest('converter', function(response) {
            var amount = $('.amount-from').val();
            var conversionRate = response.conversionRate;
            var newAmount = round(amount * conversionRate, 3);
            $('.amount-to').val(newAmount);
            setConversionRateInfo(response.conversionRate);
        });
    }

    /**
     * Updates the chart with the new information
     */
    function updateChart() {
        sendAjaxRequest('graph', function(response) {
            chart.data.labels = response.labels;
            chart.data.datasets.forEach((dataset) => {
                dataset.data = response.data;
            });
            updateChartLegend();
            chart.update();
        });
    }

    /**
     * Creates the chart/graph
     */
    function createChart() {
        Chart.defaults.global.elements.line.fill = false;
        sendAjaxRequest('graph', function(response) {
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: response.labels,
                    datasets: [{
                        label: 'Exchange rate',
                        data: response.data,
                        backgroundColor: [
                            'rgba(99, 148, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgba(99, 115, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: options
            });
            updateChartLegend();
        });
    }

    /**
     * Updates the chart legend with the correct currencies
     */
    function updateChartLegend() {
        chart.data.datasets.forEach((dataset) => {
            var currencyFrom = $('.select-currency-from').select2('data')[0].id;
            var currencyTo = $('.select-currency-to').select2('data')[0].id;
            dataset.label = currencyFrom + '/' + currencyTo;
        });
        chart.update();
    }

    /**
     * Sends an AJAX request to the back-end core with a request type (either
     * 'graph' or 'converter'), instructing the back-end to retrieve the correct
     * data using the correct webservice. When the data is presented, the callback
     * will be executed with the result.
     *
     * @param requestType The request type
     * @param callBack The callback that is executed when there is a result
     */
    function sendAjaxRequest(requestType, callback) {

        // Create date string with correct format
        var date = new Date();
        var dateString = date.getFullYear()
            + '-'
            + ('0' + (date.getMonth()+1)).slice(-2)
            + '-'
            + ('0' + date.getDate()).slice(-2);

        $.ajax({
            url: 'app/core.php',
            type: 'POST',
            cache: false,
            data: {
                requestType: requestType,
                requestData: {
                    currencyFrom: $('.select-currency-from').select2('data')[0].id,
                    currencyTo: $('.select-currency-to').select2('data')[0].id,
                    date: dateString
                }
            },
            success: function(response) {
                callback(JSON.parse(response));
            },
            error: function(error) {
                // TODO Simple alert for now, better option would be a styled modal
                alert(error.responseText);
            }
        });
    }

    /**
     * Custom round function.
     *
     * @param number The number to be rounded
     * @param precision The number of digits after the point/comma
     */
    function round(number, precision) {
        return Math.round(number * Math.pow(10, precision)) / Math.pow(10, precision);
    }

</script>
<?php } ?>
</html>
