<?php

require 'vendor/autoload.php'; 

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

function getEventData($url) {
    //cliente http
    $client = new Client();
    $response = $client->get($url);
    $html = $response->getBody()->getContents();
    
    //plataforma
    if (strpos($url, 'vividseats.com') !== false) {
        return parseVividSeats($html);
    } elseif (strpos($url, 'seatgeek.com') !== false) {
        return parseSeatGeek($html);
    } else {
        return "Plataforma no soportada.";
    }
}

function parseVividSeats($html) {
    $crawler = new Crawler($html);
    $tickets = [];

    //analizar sectores vst
    $crawler->filter('.js-listing-row')->each(function (Crawler $node) use (&$tickets) {
        $sector = $node->filter('.ListingRow__Section')->text();
        $fila = $node->filter('.ListingRow__Row')->text();
        $precio = $node->filter('.js-display-price')->text();
        $tickets[] = [
            'sector' => $sector,
            'fila' => $fila,
            'precio' => $precio
        ];
    });

    return $tickets;
}

function parseSeatGeek($html) {
    $crawler = new Crawler($html);
    $tickets = [];

    //analizar sectores, filas gs
    $crawler->filter('.listing')->each(function (Crawler $node) use (&$tickets) {
        $sector = $node->filter('.listing-section')->text();
        $fila = $node->filter('.listing-row')->text();
        $precio = $node->filter('.price .value')->text();
        $tickets[] = [
            'sector' => $sector,
            'fila' => $fila,
            'precio' => $precio
        ];
    });

    return $tickets;
}

//Recibo url
if ($argc > 1) {
    $url = $argv[1];
    $tickets = getEventData($url);

    if (is_array($tickets)) {
        echo "Entradas disponibles:\n";
        foreach ($tickets as $ticket) {
            echo "Sector: {$ticket['sector']}, Fila: {$ticket['fila']}, Precio: {$ticket['precio']}\n";
        }
    } else {
        echo $tickets;
    }
} else {
    echo "Proporcione URL del evento.\n";
    
}

