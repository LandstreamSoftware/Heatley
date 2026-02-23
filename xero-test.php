<?php
$groupedData = [];

$tenants = [
    'Name:Tenant1',
    'Name:Tenant2',
    'Name:Tenant3'
];

foreach ($tenants as $tenant) {

    $groupedData[$tenant[0]];

}



echo '<pre>';
print_r($tenants);
echo '</pre>';