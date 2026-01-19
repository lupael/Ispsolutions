<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the advanced analytics service.
    |
    */

    /**
     * Average customer lifetime in months
     * Used to calculate Customer Lifetime Value (CLV)
     * This is an estimate and should be tuned based on historical data
     */
    'avg_customer_life_months' => env('ANALYTICS_AVG_CUSTOMER_LIFE_MONTHS', 12),

    /**
     * Churn rate threshold (percentage)
     * Used to identify high churn risk
     */
    'churn_rate_threshold' => env('ANALYTICS_CHURN_RATE_THRESHOLD', 5.0),

    /**
     * Minimum data points required for predictive analytics
     */
    'min_data_points' => env('ANALYTICS_MIN_DATA_POINTS', 30),
];
