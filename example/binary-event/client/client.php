<?php

/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

use ElephantIO\Client;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/../../../vendor/autoload.php';

$version = Client::CLIENT_4X;
$url = 'http://localhost:4000';
$event = 'test-binary';

$logfile = __DIR__ . '/socket.log';
if (is_readable($logfile)) {
    @unlink($logfile);
}

// create a log channel
$logger = new Logger('client');
$logger->pushHandler(new StreamHandler($logfile, Level::Debug));

// create instance
echo sprintf("Connecting to %s\n", $url);
$client = new Client(Client::engine($version, $url), $logger);
$client->initialize();
$client->of('/binary-event');

// create bulk payload of 10MB data
$payload100k = file_get_contents(__DIR__ . '/../../../test/Payload/data/payload-100k.txt');
$payload = '';
for ($i = 0; $i < 9; $i++) {
    $payload .= $payload100k;
}

$client->emit($event, ['payload' => $payload]);
if ($retval = $client->wait($event)) {
    echo sprintf("Got a reply: %s\n", json_encode($retval->data));
}
$client->close();
