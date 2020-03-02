<?php

namespace Drupal\upload_books\Tests\Unit\Form;

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

}
