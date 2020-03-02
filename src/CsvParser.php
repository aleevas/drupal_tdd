<?php

namespace Drupal\upload_books;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\FileInterface;

/**
 * Class CsvValidator.
 *
 * @package Drupal\upload_books
 */
class CsvParser {

  use StringTranslationTrait;

  /**
   * Helper method to parse a CSV file.
   *
   * @param \Drupal\file\FileInterface $file
   *   The CSV file.
   *
   * @return array
   *   The CSV data.
   */
  public function parseFile(FileInterface $file) {
    $rows = [];
    $fh = fopen($file->getFileUri(), 'r');
    while ($row = fgetcsv($fh)) {
      $rows[] = $row;
    }
    fclose($fh);
    return $rows;
  }

  /**
   * Validate a CSV file.
   *
   * Check the CSV is in the correct format, using commas as a separator, and
   * with at least 2 columns per row.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file to be validated.
   *
   * @return array
   *   List of validation issues, if any.
   */
  public function validate(FileInterface $file) {
    $rows = $this->parseFile($file);

    // Analyze the file format. We should get 2 columns.
    $row = array_shift($rows);
    if (empty($row) || count($row) < 2) {
      return [
        $this->t("The CSV format is incorrect. Use commas."),
      ];
    }

    // Each row should have at least 2 columns, and 1st column cannot be empty.
    $i = 0;
    $errors = [];
    while ($row = array_shift($rows)) {
      $i++;
      $title = isset($row[0]) ? $row[0] : NULL;
      $author = isset($row[1]) ? $row[1] : NULL;
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
