<?php

namespace Drupal\upload_books\Tests\Unit\Form;

use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\upload_books\Form\BookImportForm;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Entity\User;

  /**
   * @group form_validation
   */
class BookImportFormTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();

    $translations = $this->createMock(TranslationInterface::class);
    $container->set('string_translation', $translations);

    $user = $this->getMockBuilder(User::class)
      ->disableOriginalConstructor()
      ->getMock();
    $container->set('current_user', $user);

    $email_validator = new EmailValidator();
    $container->set('email.validator', $email_validator);

    \Drupal::setContainer($container);
  }

  /**
   * {@inheritdoc}
   */
  public function testFormBuilding() {
    $user = \Drupal::currentUser();
    $import_form = new BookImportForm($user);
    $form = $import_form->buildForm([], new FormState());
    $this->assertArrayNotHasKey('reset', $form);

    // Enhance our mocked user.
    $user->expects($this->any())
      ->method('hasPermission')
      ->with($this->equalTo('administer books'))
      ->will($this->returnValue(TRUE));

    $form = $import_form->buildForm([], new FormState());
    $this->assertArrayHasKey('reset', $form);
  }

  /**
   * Test.
   *
   * @dataProvider emailFieldData
   *
   * string $string
   *   The tested string.
   * boolean $passes
   *   The passes result.
   */
  public function testUserEmailFieldValidation($string, $passes) {
    $user = \Drupal::currentUser();
    $import_form = new BookImportForm($user);
    $form = [];
    $form_state = new FormState();
    $form_state->setValue('email', $string);
    $import_form->validateForm($form, $form_state);
    if ($passes) {
      $this->assertCount(0, $form_state->getErrors());
    }
    else {
      $this->assertCount(1, $form_state->getErrors());
      $this->assertArrayHasKey('email', $form_state->getErrors());
    }
  }

  /**
   * Data provider for testUserEmailFieldValidation()
   *
   * @return array
   *   Fixtures for test.
   */
  public function emailFieldData() {
    return [
      [NULL, FALSE],
      ['', FALSE],
      ['test@test@', FALSE],
      ['test@test.com', TRUE],
      ['test123@test.com', TRUE],
    ];
  }

}
