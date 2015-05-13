<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Adapter
    |--------------------------------------------------------------------------
    |
    | This option defined how guard will be checked permissions.
    |
    | Supported: "eloquent", "yaml"
    |
    */

    'adapter' => 'yaml',

    /*
    |--------------------------------------------------------------------------
    |  Adapter config file name
    |--------------------------------------------------------------------------
    |
    | This option defined adapter config file. Configuration file must be
    | exposed in config directory.
    |
    | Example: "security.yaml"
    |
    */

    'adapter_config_file' => '',

    /*
	|--------------------------------------------------------------------------
	| Models for eloquent adapter
	|--------------------------------------------------------------------------
	|
	| This option is required by guard generators. If you are using own models
    | (or extend models from package) change paths to correctly.
	|
	*/

    'model' => [
        'role'       => 'Szykra\Guard\Models\Role',
        'permission' => 'Szykra\Guard\Models\Permission',
    ]

];
