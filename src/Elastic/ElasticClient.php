<?php   

/* Copyright (c) 2018-2019, Fares Abdullah, all rights reserved. */

namespace App\Elastic;

use GuzzleHttp\Client as GuzzleClient;

class ElasticClient {

	public static function request($method, $queryURL="", $body_array=null) {
		$message = "\nelasticsearch \"$method\" request: $queryURL\n body: " . ($body_array ? json_encode($body_array) : ""); // TODO: delete
//		echo $message; exit(1);
		$client = new GuzzleClient();
		$status_code = "";
		$body = "";

		try {
			$result = $client->request($method, "http://elasticsearch:9200$queryURL", $body_array ? [ 'json' => $body_array ] : []);
			$status_code = $result->getStatusCode();
			$body = $result->getBody();
		} catch (RequestException $e) {
			$message .= Psr7\str($e->getRequest());
			$message .= "\n";
			if ($e->hasResponse()) {
				$message .= Psr7\str($e->getResponse());
				$message .= "\n";
			}
			// TODO: improve error handling
			echo $message;
			exit(1);
		}
		return array('status' => $status_code, 'body' => json_decode($body, true));
	}

	public static function monumentalRequest($method, $queryURL="", $body_array=null) {
		if (strlen($queryURL) > 0 && substr($queryURL, 0, 1) !== "/") $queryURL = "/$queryURL";

		return ElasticClient::request($method, '/monumental/building' . $queryURL, $body_array);
	}

	public static function post($queryURL="", $body_array=null) {
		return ElasticClient::monumentalRequest('POST', $queryURL, $body_array);
	}

	public static function update($queryURL="", $body_array=null) {
		return ElasticClient::monumentalRequest('POST', $queryURL . "/_update", ['doc' => $body_array]);
	}

	public static function get($queryURL="", $body_array=null) {
		return ElasticClient::monumentalRequest('GET', $queryURL, $body_array);
	}

	public static function delete($queryURL="", $body_array=null) {
		return ElasticClient::monumentalRequest('DELETE', $queryURL, $body_array);
	}
}
