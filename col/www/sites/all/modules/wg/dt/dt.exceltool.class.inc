<?php
/**
 * CSVToExcelConverter
 */
class DTExcelTool {

  public static function init() {
    date_default_timezone_set('Europe/London');
    $path_PHPExcel = drupal_realpath(libraries_get_path('PHPExcel'));
    //    error_reporting(E_ALL);
    require_once $path_PHPExcel.'/Classes/PHPExcel.php';
  }

  public static function xlsx2csv($xlsx_file, $out_file) {
    $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
    $cacheSettings = array(' memoryCacheSize ' => '8MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

    $fileType = 'Excel2007';
    $objReader = PHPExcel_IOFactory::createReader($fileType);
    $objReader->setReadDataOnly(true);
    $objExcel = $objReader->load($xlsx_file);
    $writer = PHPExcel_IOFactory::createWriter($objExcel, 'CSV');
    $writer->save($out_file);
  }

  public static function csv2xlsx($csv_file, $out_file) {
    //set cache
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

    //open csv file
    $objReader = new PHPExcel_Reader_CSV();
    $objPHPExcel = $objReader->load($csv_file);
    $in_sheet = $objPHPExcel->getActiveSheet();

    //open excel file
    $objPHPExcel = new PHPExcel();
    $out_sheet = $objPHPExcel->getActiveSheet();

    //row index start from 1
    $row_index = 0;
    foreach ($in_sheet->getRowIterator() as $row) {
      $row_index++;
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(false);

      //column index start from 0
      $column_index = -1;
      foreach ($cellIterator as $cell) {
        $column_index++;
        //echo $column_index.', '.$row_index."\n";
        $cellIndex = PHPExcel_Cell::stringFromColumnIndex($column_index).$row_index;
        $out_sheet->getStyle($cellIndex)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $out_sheet->setCellValueByColumnAndRow($column_index, $row_index, $cell->getValue());
      }
    }

    //write excel file
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($out_file);

  }
}

