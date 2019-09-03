<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */
   
    'supportsCredentials' => false,
    'allowedOrigins' => ['http://localhost:3000', 'http://ec2-3-15-169-11.us-east-2.compute.amazonaws.com:3000'],
    'allowedOriginsPatterns' => [],
    'allowedHeaders' => ['Content-Type','Authorization'],
    'allowedMethods' => ['GET','POST','PUT','DELETE'],
    'exposedHeaders' => [],
    'maxAge' => 0,  

];
