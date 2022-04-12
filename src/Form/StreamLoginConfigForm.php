<?php
/**
 * @file
 * Contains \Drupal\simple_conreg\Form\EventConfigForm
 */
namespace Drupal\stream_login\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\devel;
use Drupal\simple_conreg\SimpleConregEventStorage;
use Drupal\simple_conreg\SimpleConregConfig;
use Drupal\simple_conreg\SimpleConregTokens;

/**
 * Configure simple_conreg settings for this site.
 */
class StreamLoginConfigForm extends ConfigFormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'StreamLoginConfigForm';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'stream_login.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Fetch event name from Event table.
    if (!count($events = SimpleConregEventStorage::loadAll())) {
      // Event not in database. Display error.
      $form['simple_conreg_event'] = array(
        '#markup' => $this->t('No events found. Please set up an event.'),
        '#prefix' => '<h3>',
        '#suffix' => '</h3>',
      );
      return parent::buildForm($form, $form_state);
    }
    $eventOptions = [];
    foreach ($events as $event) {
      $eventOptions[$event['event_id']] = $event['event_name'];
    }

    // Get config for event.
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('simple_conreg.settings.'.$eid);

    $form['admin'] = array(
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-stream_login_event',
    );

    $form['stream_login_event'] = array(
      '#type' => 'details',
      '#title' => $this->t('Event Details'),
      '#tree' => TRUE,
      '#group' => 'admin',
    );

    $form['stream_login_event']['event'] = array(
      '#type' => 'select',
      '#title' => $this->t('Event'),
      '#options' => $eventOptions,
      '#default_value' => $config->get('event'),
    );

    $form['stream_login_intro'] = array(
      '#type' => 'details',
      '#title' => $this->t('Introduction'),
      '#tree' => TRUE,
      '#group' => 'admin',
    );

    $form['stream_login_intro']['intro'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Introduction text'),
      '#description' => $this->t('Text to display over the form explaining the login process.'),
      '#default_value' => $config->get('intro'),
    );

    return parent::buildForm($form, $form_state);
  }


  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::getContainer()->get('config.factory')->getEditable('stream_login.settings');
    $config->set('event', $vals['stream_login_event']['event']);
    $config->set('intro', $vals['stream_login_intro']['intro']);

    $config->save();

    parent::submitForm($form, $form_state);
  }
}
