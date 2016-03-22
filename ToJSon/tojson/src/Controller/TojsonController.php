<?php
namespace Drupal\tojson\Controller;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation;
use Drupal\Core\Form;
	//use Symfony\Component\HttpFoundation\RedirectResponse;
class TojsonController extends FormBase {
	public function getFormId() {
	  		return 'to_json_form'; // form id
	  	}
	  	/*
	  	* Function used to create the form in drupal
	  	*/
	  	public function buildForm(array $form, FormStateInterface $form_state) {
	  		$contentTypes = \Drupal::service('entity.manager')
	  		->getStorage('node_type')
	  		   ->loadMultiple(); //query for load all the content type in drupal
	  		   $contentTypesList = [];
	  		   foreach ($contentTypes as $contentType) {
	  		   	$contain = $contentType->toArray();
				$contentTypesName[] = $contain['name'];// get  names of all  content type
				$contentTypesList[] = $contain['type']; //get the machine name of the content type
			}
			$contentArray = array_combine($contentTypesList, $contentTypesName);
			$form['contenttype'] = array(
				'#type' => 'select',
				'#title' => t('Select Content Type'),
				'#options' => $contentArray,    	
				'#size' => 1,
				'#required' => TRUE,
		  	); //create a selection box in the drupal form
			$form['submit'] = array(
				'#type' => 'submit',
				'#value' => t('Submit'),
		    ); //create a submit button in drupal form
			return $form;
		}
	  	/*
		* Function used to validate the form	  	
	  	*/
		public function validateForm(array &$form, FormStateInterface $form_state) {
	  		//nothing to do
		}
	  	/*
	  	* Function executed when submit button is pressed in drupal form
	  	* Here the function create a json file for the selected content type
	  	*/
	  	public function submitForm(array &$form, FormStateInterface $form_state) {
	  		$path = drupal_realpath() . '/modules/custom/tojson/src/result.json';
	  		$fp = fopen( $path, 'w' ); //open the file
			$currentValue = $form_state->getValues('contenttype'); //get the current form state value
			$nodeType = \Drupal::entityQuery( 'node' )
			->condition( 'type', $currentValue['contenttype'] )
			    ->execute(); // drupal query to get the node details, 
			$nodes = entity_load_multiple( 'node', $nodeType); // load all the node details in array of object
			$i = 0;
			foreach($nodes as $node){
				$valueArray[$i] = $node->toArray(); // convert the object to array in drupal(the object is protected in nature)
				$i ++;
			}
			for($i =0; $i < count($valueArray); $i ++ ) { //read each node, convert to json and write to the file
				$json = $valueArray[$i];
				foreach ($json as $key => $value){
			        if(isset($json[$key][0]['value'])){ //for avoiding value as array
			        	$json[$key] = $json[$key][0]['value'];
			        	$nid[$i] = (int)$json['nid'];
			        }
			        if(isset($json[$key][0]['target_id'])) { // for get the inner array details
			        	$json[$key] = $json[$key][0]['target_id'];
			        }
			        $finalJson[$i] = json_encode($json, JSON_PRETTY_PRINT); // encode the array to the json
			    } 
			    $value = "";
			    $value .= "product[".$nid[$i]."] = ".$finalJson[$i].";\n";
		   		fwrite($fp, $value);//write the string to the file
		   	}
			fclose( $fp ); // close the file		
		}
	}
