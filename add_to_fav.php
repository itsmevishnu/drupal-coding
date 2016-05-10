<?php

/*
 * This is used to perform the favourite auction 
 * functionality implementation
 */

/**
 * 
 * @global type $user used to get the current user's details
 * @param type $form is the form type name
 * @param type $form_state is the current values of the forms
 * @param type $my_id is the my id inputed by the user
 * @return string
 * This function is used to create a simple form with elements
 * add to auction and remove from auction
 */
function my_fav_form($form, &$form_state ) {
 
    $form['add_to_fav'] = array(
      '#type' => 'submit',
      '#id' => 'fav-button',
      '#value' => t('Add to favourite'),
      '#validate' => array('my_add_fav_validate'),
      '#ajax' => array(
        'callback' => 'my_add_fav_submit',
        'wrapper' => 'my-fav-form',
        'method' => 'replace',
        'effect' => 'fade',
      )
    );
  }
  else {
    $form['remove_fav'] = array(
      '#type' => 'submit',
      '#id' => 'fav-button',
      '#value' => t('Remove from favourite'),
      '#validate' => array('my_remove_fav_validate'),
      '#ajax' => array(
        'callback' => 'my_remove_fav_submit',
        'wrapper' => 'my-fav-form',
        'method' => 'replace',
        'effect' => 'fade',
      )
    );
  }
  return $form;
}

/**
 * 
 * @param type $form contain the forms
 * @param array $form_state conatain the values of the forms
 * @return array
 * This function is used to add a new table row to make
 * the current running auction to a favourite auction of
 * this current user
 */
function my_add_fav_submit($form, &$form_state) {
  global $user;

  if (form_get_errors('added_to_fav')) {
    $form_state['rebuild'] = TRUE;
    $commands = array();
    $commands[] = ajax_command_prepend(NULL, theme('status_messages'));
    return array('#type' => 'ajax', '#commands' => $commands);
  }

  $fav_id = db_insert('my_fav_table')
      ->fields(array(
        'user_id' => $user->uid,
        'fav_status' => 1,
        'created_at' => date('Y-m-d H:i:s', time()),
        'modified_at' => date('Y-m-d H:i:s', time())
      ))
      ->execute();

  $form_state['rebuild'] = TRUE;

  $command = array();
  $commands[] = ajax_command_replace('#fav-button' . $my_id, drupal_render(drupal_build_form('my_fav_form', $form_state)));
  return array('#type' => 'ajax', '#commands' => $commands);
}

/**
 * 
 * @param type $form contain the forms
 * @param array $form_state conatain the values of the forms at this state
 * @return array
 * This function is used to update a table row to remove
 * the current favourite auction for a current user
 */
function my_remove_fav_submit($form, &$form_state) {
  global $user;

  if (form_get_errors('removed_from_fav')) {
    $form_state['rebuild'] = TRUE;
    $commands = array();
    $commands[] = ajax_command_prepend(NULL, theme('status_messages'));
    return array('#type' => 'ajax', '#commands' => $commands);
  }

  $my_id = $form_state['my']['my_id'];
  db_update('my_fav_table')
      ->fields(array('fav_status' => 0, 'modified_at' => date('Y-m-d H:i:s', time())))
      ->condition('user_id', $user->uid, '=')
      ->condition('fav_status', 1, '=')
      ->execute();

  $form_state['rebuild'] = TRUE;

  $command = array();
  $commands[] = ajax_command_replace('#fav-button' . $my_id, drupal_render(drupal_build_form('my_fav_form', $form_state)));

  return array('#type' => 'ajax', '#commands' => $commands);
}

/**
 * 
 * @global type $user contain current user information
 * @param type $form contain the form
 * @param type $form_state conatain the values of the forms at this state
 * This fucntion check if the auction is already in the favourite table
 */
function my_add_fav_validate($form, &$form_state) {
  //validation codes here
  global $user;

  $query = db_select('my_fav_table', 'aft');
  $result = $query->fields('aft', array('fav_status'))
      ->condition('aft.user_id', $user->uid, '=')
      ->orderBy('aft.modified_at', 'DESC')
      ->range(0,1)
      ->execute()
      ->fetchAll();
 
   if ($result[0]->fav_status == 1) {

    form_set_error('added_to_fav', t('This auction is already mark as favourite,<br>Refresh the browser'));
  }

}

/**
 * 
 * @global type $user contain current user information
 * @param type $form contain the form
 * @param type $form_state conatain the values of the forms at this state
 * This fucntion check if the auction not a favourite
 */
function my_remove_fav_validate($form, &$form_state) {
  //validation codes here
  global $user;

  $query = db_select('my_fav_table', 'aft');
  $result = $query->fields('aft', array('fav_status'))
      ->condition('aft.user_id', $user->uid, '=')
      ->orderBy('aft.modified_at', 'DESC')
      ->range(0,1)
      ->execute()
      ->fetchAll();
  if ($result[0]->fav_status == 0 ) {

    form_set_error('removed_from_fav', t('This not favourire now, Please add to favourite<br>Refresh the browser'));
  }

}
