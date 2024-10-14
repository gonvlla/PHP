<?php
require 'vendor/autoload.php'; 

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

//devolvera los datos
function getEventData($url) {
    $client = new Client(); //para hacer solicitudes a la url
    $response = $client->get($url); //get a  la url
    //obtengo contenido html
    $html = $response->getBody()->getContents();
    
    //detectar plataforma
    if (strpos($url, 'vividseats.com') !== false) { //"
        return parseVividSeats($html);
    } elseif (strpos($url, 'seatgeek.com') !== false) {//Llama a la func q analiza esta url
        return parseSeatGeek($html);
    } else {
        return "Plataforma no soportada.";
    }
}

//extrae datos
function parseVividSeats($html) {
    //obj para analizar html
    $crawler = new Crawler($html);
    $tickets = []; //para entradas

    //recorro cada elemento HTML que contiene la info de las entradas
    $crawler->filter('.js-listing-row')->each(function (Crawler $node) use (&$tickets) { // Extrae el texto del sector de la entrada
        $sector = $node->filter('.ListingRow__Section')->text(); // extrae el texto de la fila de la entrada
        $fila = $node->filter('.ListingRow__Row')->text(); //precio entrada
        $precio = $node->filter('.js-display-price')->text(); //alta entrada al array $tickets
        $tickets[] = [
            'sector' => $sector,
            'fila' => $fila,
            'precio' => $precio
        ];
    });

    // Devuelve el array de entradas extraídas
    return $tickets;
}

//func para analizar pag
function parseSeatGeek($html) {
    //obj crawler para analizar html
    $crawler = new Crawler($html);
    $tickets = []; //Para  entradas

    //recorre  los  elementos html, para info
    $crawler->filter('.listing')->each(function (Crawler $node) use (&$tickets) {
        //textSectorEntrada
        $sector = $node->filter('.listing-section')->text();
        //textFila entrada
        $fila = $node->filter('.listing-row')->text();
        //precioEntrada
        $precio = $node->filter('.price .value')->text();
        //alta entradas
        $tickets[] = [
            'sector' => $sector,
            'fila' => $fila,
            'precio' => $precio
        ];
    });

    //entradas extraidas
    return $tickets;
}

//si se ejecuta el script desde la linea de comandos y se pasa un parametro(url)
if ($argc > 1) {
    //el primer argumento pasado es la URL del evento
    $url = $argv[1];
    //llama a la funcion para obtener los datos del evento a partir de la URL
    $tickets = getEventData($url);

    //si el resultado es un array, se extrajeron las entradas correctamente
    if (is_array($tickets)) {
        //muestra las entradas disponibles
        echo "Entradas disponibles:\n";
        foreach ($tickets as $ticket) {
            //imprime los detalles de cada entrada 
            echo "Sector: {$ticket['sector']}, Fila: {$ticket['fila']}, Precio: {$ticket['precio']}\n";
        }
    } else {
        echo $tickets;
    }
} else {
    //si no se proporciona una URL en los parametros, muestra un mensaje pidiendo una URL
    echo "Proporcione URL del evento.\n";
    
}
