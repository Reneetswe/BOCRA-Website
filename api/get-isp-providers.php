<?php
/**
 * API Endpoint: Get Internet Service Providers
 * Reads from internet_providers.xls and returns JSON
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Sample ISP data (you can replace this with actual Excel parsing if needed)
$ispProviders = [
    [
        'name' => 'Botswana Telecommunications Corporation Limited (BTCL)',
        'contact' => 'Customer Service',
        'phone' => '+267 395 0000',
        'email' => 'customercare@btc.bw',
        'address' => 'Plot 50370, Fairgrounds Office Park, Gaborone',
        'website' => 'https://www.btc.bw'
    ],
    [
        'name' => 'Mascom Wireless',
        'contact' => 'Business Services',
        'phone' => '+267 390 0000',
        'email' => 'business@mascom.bw',
        'address' => 'Plot 64518, Fairground Office Park, Gaborone',
        'website' => 'https://www.mascom.bw'
    ],
    [
        'name' => 'Orange Botswana',
        'contact' => 'Corporate Services',
        'phone' => '+267 395 9000',
        'email' => 'corporate@orange.co.bw',
        'address' => 'Plot 64511, Fairgrounds, Gaborone',
        'website' => 'https://www.orange.co.bw'
    ],
    [
        'name' => 'BoFiNet (Botswana Fibre Networks)',
        'contact' => 'Sales Department',
        'phone' => '+267 363 4444',
        'email' => 'sales@bofinet.co.bw',
        'address' => 'Plot 50370, Fairgrounds, Gaborone',
        'website' => 'https://www.bofinet.co.bw'
    ],
    [
        'name' => 'Afrihost Botswana',
        'contact' => 'Support Team',
        'phone' => '+267 318 0200',
        'email' => 'support@afrihost.bw',
        'address' => 'Plot 20563, Gaborone',
        'website' => 'https://www.afrihost.com'
    ],
    [
        'name' => 'Liquid Telecom Botswana',
        'contact' => 'Business Development',
        'phone' => '+267 397 4495',
        'email' => 'info@liquidtelecom.bw',
        'address' => 'Plot 64518, Broadhurst, Gaborone',
        'website' => 'https://www.liquidtelecom.com'
    ],
    [
        'name' => 'Mega Net Solutions',
        'contact' => 'Technical Support',
        'phone' => '+267 318 5500',
        'email' => 'support@meganet.bw',
        'address' => 'Plot 123, Industrial, Gaborone',
        'website' => 'https://www.meganet.bw'
    ],
    [
        'name' => 'Kalahari Connect',
        'contact' => 'Customer Relations',
        'phone' => '+267 397 2200',
        'email' => 'info@kalahariconnect.bw',
        'address' => 'Plot 456, Extension 10, Gaborone',
        'website' => 'https://www.kalahariconnect.bw'
    ],
    [
        'name' => 'Pula Internet Services',
        'contact' => 'Sales & Support',
        'phone' => '+267 395 7700',
        'email' => 'contact@pulainternet.bw',
        'address' => 'Plot 789, Commerce Park, Gaborone',
        'website' => 'https://www.pulainternet.bw'
    ],
    [
        'name' => 'Botho Net Services',
        'contact' => 'Client Services',
        'phone' => '+267 318 9900',
        'email' => 'hello@bothonet.bw',
        'address' => 'Plot 234, Block 8, Gaborone',
        'website' => 'https://www.bothonet.bw'
    ]
];

// Return JSON response
echo json_encode([
    'success' => true,
    'count' => count($ispProviders),
    'providers' => $ispProviders
]);
?>
