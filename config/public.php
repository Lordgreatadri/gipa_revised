<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public contact channels
    |--------------------------------------------------------------------------
    |
    | Shown on the Contact us page. Placeholders until GIPA confirms the
    | operational addresses. Do not treat these as published business rules.
    |
    */

    'contact' => [
        'email' => env('PUBLIC_CONTACT_EMAIL', 'info@example.gov.gh'),
        'phone' => env('PUBLIC_CONTACT_PHONE', '+233 (0) 00 000 0000'),
        'phone_href' => env('PUBLIC_CONTACT_PHONE_HREF', '+233000000000'),
        'office' => env('PUBLIC_CONTACT_OFFICE', 'Head Office: Accra, Ghana'),
        'response_time' => env('PUBLIC_CONTACT_RESPONSE_TIME', 'Within 3 working days'),
    ],

    /*
    | Desk hours shown on the contact page. Times are Africa/Accra.
    | extra_closures holds announced dates that are not on the fixed
    | calendar, such as Eid al-Fitr and Eid al-Adha: 'Y-m-d' => 'Name'.
    */
    'office' => [
        'timezone' => 'Africa/Accra',
        'opens' => '08:00',
        'closes' => '17:00',
        'extra_closures' => [
            // '2026-03-20' => 'Eid al-Fitr',
        ],
    ],

];
