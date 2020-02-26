<?php

namespace Drupal\upload_books\Tests\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\FileInterface;
use Drupal\Tests\PhpunitCompatibilityTrait;
use Drupal\Tests\UnitTestCase;
use Drupal\upload_books\CsvValidator;

/**
 * @group form_validation_example
 */
class CsvValidationTest extends UnitTestCase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    require_once __DIR__ . '/../../../upload_books.module';
//
    $container = new ContainerBuilder();
//
    $validator = new CsvValidator();
    $container->set('upload_books.csv_validator', $validator);
//
    $translations = $this->createMock(TranslationInterface::class);
    $container->set('string_translation', $translations);
//
    \Drupal::setContainer($container);
  }

  /**
   * Validation CSV file.
   */
  public function testValidation() {

    $file = $this->createMock(FileInterface::class);
    $file->expects($this->any())
      ->method('getFileUri')
      ->will($this->returnValue(__DIR__ . '/../../fixtures/books.incorrect_format.csv'));

    $this->assertEquals(
      [$this->t("The CSV format is incorrect. Use commas.")],
      upload_books_validate_csv($file)
    );

    $file = $this->createMock(FileInterface::class);
    $file->expects($this->any())
      ->method('getFileUri')
      ->will($this->returnValue(__DIR__ . '/../../fixtures/books.incorrect_data.csv'));

    $this->assertEquals(
      [
        $this->t("The author on line @line is empty. You must provide at least one author.", ['@line' => 1]),
        $this->t("The book title on line @line is empty. You must provide a title for each book.", ['@line' => 2]),
      ],
      upload_books_validate_csv($file)
    );

    $file = $this->createMock(FileInterface::class);
    $file->expects($this->any())
      ->method('getFileUri')
      ->will($this->returnValue(__DIR__ . '/../../fixtures/books.correct.csv'));

    $this->assertEquals(
      [],
      upload_books_validate_csv($file)
    );
  }

}
