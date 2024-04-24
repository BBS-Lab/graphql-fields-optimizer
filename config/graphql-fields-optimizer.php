<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Field Name Security Check
    |--------------------------------------------------------------------------
    |
    | This option allows you to enable or disable security checks on field names
    | used in GraphQL queries. Enabling this feature helps prevent potential
    | security vulnerabilities such as SQL injection by ensuring that all
    | field names in the queries are valid and allowed.
    |
    | It's recommended to enable this feature, especially in production
    | environments, to enhance the security of your application. Note that
    | enabling this setting might incur a slight performance overhead due to
    | the validation processes involved.
    |
    */
    'enable_security' => false,
];