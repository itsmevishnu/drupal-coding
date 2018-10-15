<?php

/**
 * @file
 * Contain \Drupal\api_key\Controller\ApiController
 */

namespace Drupal\api_key\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class ApiKeyController extends ControllerBase {

	/**
	 * Generates Json of the given page.
	 * @param  [type] $api [description]
	 * @param  [type] $id  [description]
	 * @return [type]      [description]
	 */
	public function generateJson($api, $id) {
		//Reads the site api key
		$site_api = \Drupal::config('api_key.settings')->get('siteapikey');

		if($api !== $site_api ){
			//Sets error message for invalid token.
			$data = ['message'=>$this->t('Invalid token,access Denied')];
			$this->showJsonOutput($data);
		}
		
		//Checks the given  id is a valid page id or not.
		$query = \Drupal::entityQuery('node');
		$page = $query->condition('type', 'page')
			->condition('nid', $id)
			->execute();
		//Sets message for no page found.
		
		if( empty($page)) {
			$data = ['message'=>$this->t('There is no page found')];
		} else {
			$data = ['data' => Node::load($id)->toArray()];
		}

		$this->showJsonOutput($data);
		
	}

	/**
	 * Show the json output to the browser/API testing tool.
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function showJsonOutput($data){
		header('Content-Type: application/json');
		die(json_encode($data, JSON_PRETTY_PRINT));
	}

}
