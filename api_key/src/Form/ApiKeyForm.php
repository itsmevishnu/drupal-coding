<?php

/**
 * @file
 * This file contain the code for creating form
 */

namespace Drupal\api_key\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ApiKeyForm extends ConfigFormBase {

	/**
	 * Creates the form id.
	 * @return [type] [description]
	 */
	public function getFormId() {
		return 'api_key_settings';
	}

	protected function getEditableConfigNames() {
		return [
			'api_key.settings'
		];
	}

	/**
	 * {@inheritdoc}
	 * @param  array              $form       [description]
	 * @param  FormStateInterface $form_state [description]
	 * @return [type]                         [description]
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		$config = $this->config('api_key.settings');

		$form['api_key'] = [
			'#type' => 'textfield',
			'#title' => $this->t('API Key'),
			'#default_value' => $config->get('siteapikey'),
			'#description' => $this->t('Enter the API key for making the site 
				protected on API calls'),
			'#maxlength' => 32,
			'#required' => true,
			'#placeholder'=> $this->t('No API key yet')
		];
		//Checks old value is there
		$value = $this->isUpdate() ? $this->t('Update Configuration') : 
			$this->t('Save Configuration');

		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $value,
			'#button_type' => 'primary'
		];

		// return parent::buildForm($form, $form_state);
		return $form;
	}

	/**
	 * {@inheritdoc}
	 * @param  array              &$form      [description]
	 * @param  FormStateInterface $form_state [description]
	 * @return [type]                         [description]
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
		//Reads the new value form the form instance.
		$new_value = $form_state->getValue('api_key');

		//Changes the success message based on the condtions.
		if($this->isUpdate()) {
			//Sets update message.
			$message = $this->t('Site API key is changed from @old_value to 
				@new_value', [
				'@old_value'=> $this->oldValue(), 
				'@new_value'=> $new_value
			]);
		} else {
			//Sets success message.
			$message = $this->t('@new_value is set as site API key.',
				['@new_value'=> $new_value]);
		}

		//Save the value to the database
		$this->configFactory->getEditable('api_key.settings')
			->set('siteapikey', $new_value)
			->save();
		parent::submitForm($form, $form_state);
		
		//Prints the message.
		drupal_set_message($message,$type='status', $repeat=true);
	}

	/**
	 * Checks old value is there.
	 * @return boolean [description]
	 */
	public function isUpdate() {
		// Checks old value is there.
		$config = $this->config('api_key.settings');
		$old_value = $config->get('siteapikey');
		return !empty($old_value) ? true: false;
	}

	/**
	 * Return old value from the database
	 * @return [type] [description]
	 */
	public function oldValue() {
		$config = $this->config('api_key.settings');
		return $config->get('siteapikey');
	}

}