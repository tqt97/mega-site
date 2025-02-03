<?php

return [
    'pricing_cache_ttl' => env('BOOKING_PRICING_CACHE_TTL', 5), // Default to 5 minutes, configurable
    'pricing_cache_store' => env('BOOKING_PRICING_CACHE_STORE', 'default'), // Default to default store, configurable
];
