<?php

namespace Drupal\upload_books\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Upload books settings for this site.
 */
class BookImportForm extends FormBase {

  /**
   * The Drupal account to use for checking for access to advanced search.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * BookImportForm constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The $account object to use for checking for access to advanced search.
   */
  public function __construct(AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'upload_books_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t("Book list"),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
        'upload_books_validate_csv' => [],
      ],
      '#required' => TRUE,
    ];

    if (\Drupal::currentUser()->hasPermission('administer books')) {
      $form['reset'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("Reset all books"),
      ];
    }

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t("User email"),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t("Submit"),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $email = $form_state->getValue('email');
    $is_valid = \Drupal::service('email.validator')->isValid($email);
    if (!$is_valid) {
      $form_state->setErrorByName('email', $this->t("This email is not valid."));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
