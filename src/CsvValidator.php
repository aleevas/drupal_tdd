<?php

namespace Drupal\upload_books;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\FileInterface;

/**
 * Class CsvValidator.
 *
 * @package Drupal\upload_books
 */
class CsvValidator {

  use StringTranslationTrait;

  /**
   * Validate a CSV file.
   *
   * Check the CSV is in the correct format, using commas as a separator, and
   * with at least 2 columns per row.
   *
   * @param \Drupal\file\FileInterface $file
   *     The file to be validated.
   *
   * @return array
   *     List of validation issues, if any.
   */
  public function validate(FileInterface $file) {
    $rows = [];
    $fh = fopen($file->getFileUri(), 'r');
    while ($row = fgetcsv($fh)) {
      $rows[] = $row;
    }
    fclose($fh);
    // Analyze the file format. We should get 2 columns.
    $row = array_shift($rows);
    if (empty($row) || count($row) < 2) {
      return [
        $this->t("The CSV format is incorrect. Use commas."),
      ];
    }

    // Each row should have at least 2 columns, and the 1st columns cannot be empty.
    $i = 0;
    $errors = [];
    while ($row = array_shift($rows)) {
      $i++;
      @list($title, $author) = $row;
      if (empty($title)) {
        $errors[] = $this->t("The book title on line @line is empty. You must provide a title for each book.", ['@line' => $i]);
      }
      if (empty($author)) {
        $errors[] = $this->t("The author on line @line is empty. You must provide at least one author.", ['@line' => $i]);
      }
    }
    return $errors;
  }

}
