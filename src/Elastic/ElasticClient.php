<?php   

/* Copyright (c) 2018-2019, Fares Abdullah, all rights reserved. */

namespace App\Elastic;

use GuzzleHttp\Client as GuzzleClient;

class ElasticClient {

	public static function elasticRequest($method, $queryURL="", $body_array=null) {
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

	public static function elasticMonumentalRequest($method, $queryURL="", $body_array=null) {
		if (strlen($queryURL) > 0 && substr($queryURL, 0, 1) !== "/") $queryURL = "/$queryURL";

		return ElasticClient::elasticRequest($method, '/monumental/building' . $queryURL, $body_array);
	}

	public static function elasticPost($queryURL="", $body_array=null) {
		return ElasticClient::elasticMonumentalRequest('POST', $queryURL, $body_array);
	}

	public static function elasticGet($queryURL="", $body_array=null) {
		return ElasticClient::elasticMonumentalRequest('GET', $queryURL, $body_array);
	}

	public static function elasticDelete($queryURL="", $body_array=null) {
		return ElasticClient::elasticMonumentalRequest('DELETE', $queryURL, $body_array);
	}
}
