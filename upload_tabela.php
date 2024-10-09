<?php


require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;



if(isset($_POST['save_excel_data'])) {
    if(isset($_FILES['import_file']['name']) && $_FILES['import_file']['name'] != '') {
        $fileName = $_FILES['import_file']['name'];
        $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $filename_without_ext = pathinfo($fileName, PATHINFO_FILENAME);
        $allowed_ext = ['xls','csv','xlsx'];
        $jsonFilePath = $filename_without_ext .".json";
        if(in_array($file_ext, $allowed_ext)) {
            $inputFileNamePath = $_FILES['import_file']['tmp_name'];
            $spreadsheet = IOFactory::load($inputFileNamePath);

            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            $userResponseData = [];

            $headerRow = array_shift($data); // Remove header row
            $columns = array_flip($headerRow); // Map header names to their indices

           
            foreach ($data as $row) {
                $tableEntry = [];
                
                foreach ($columns as $columnName => $columnIndex) {
                    $value = isset($row[$columnIndex]) ? $row[$columnIndex] : null;
                    $tableEntry[$columnName] = is_null($value) ? 0 : $value;
                }

                $userResponseData[] = $tableEntry;
            }
            $tableArrayList = [
                $filename_without_ext => $userResponseData
            ];
            
            $newJsonString = json_encode($tableArrayList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if(file_put_contents($jsonFilePath, data: $newJsonString) !== false) {
                $_SESSION['message'] = "Tabela convertida para JSON com sucesso";
            } else {
                $_SESSION['message'] = "Falha ao salvar JSON";
            }

            header('Location: converter_tabela.php');
            exit(0);
        } else {
            $_SESSION['message'] = "Arquivo inválido";
            header('Location: converter_tabela.php');
            exit(0);
        }
    } else {
        $_SESSION['message'] = "Nenhum arquivo selecionado";
        header('Location: converter_tabela.php');
        exit(0);
    }
}
