
/**
 * CurrencyConverter namespace
 */
var CurrencyConverter = (function() {

    var self = {};

    /**
     * Sets the current date in the UI
     */
    self.setConversionDate = function() {
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
    var setConversionRateInfo = function(conversionRate) {
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
    self.swap = function() {
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
    self.convertAmount = function() {
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
    self.updateChart = function() {
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
    self.createChart = function() {
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
    var updateChartLegend = function() {
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
    var sendAjaxRequest = function(requestType, callback) {

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
    var round = function(number, precision) {
        return Math.round(number * Math.pow(10, precision)) / Math.pow(10, precision);
    }

    return self;
}());
