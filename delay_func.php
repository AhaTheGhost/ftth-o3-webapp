<?php

require_once 'libs/phpspreadsheet/vendor/autoload.php'; // include "PhpSpreadsheet" library

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Font;

$web_app_version = "v2.8.7"; // Web app version

date_default_timezone_set("Asia/Baghdad");

$today = date('d/m/Y'); // Current date

if(isset($_POST['delaySLA']) || isset($_POST['partnerDelay'])){

	$file_size = $_FILES['ofile']['size'];

	if($file_size > 35 * 1024 * 1024) // If size is bigger than 35MB then show error
		header('Location: index.php?m=el');

	else {

		$file_temp = get_file_type($_FILES['ofile']);

		if(isset($_POST['delaySLA'])) // If delay SLA button pressed then go to, else
			filter_tickets_for_delay_sla($file_temp);
		else
			filter_tickets_for_partner_delay($file_temp);

	}

}

function get_file_type($fileType){

	if(strcasecmp(pathinfo($fileType['name'], PATHINFO_EXTENSION), 'csv') == 0) // If file type is '.csv'
		return $fileType['tmp_name'];

	elseif(strcasecmp(pathinfo($fileType['name'], PATHINFO_EXTENSION), 'xlsx') == 0) { // else if '.xlsx', then chnage to '.csv'

		$reader = IOFactory::createReader('Xlsx');
		$spreadsheet = $reader->load($fileType['tmp_name']);
		$writer = IOFactory::createWriter($spreadsheet, 'Csv');
		$newTmpCsvFile = stream_get_meta_data(tmpfile())['uri'];
		$writer->save($newTmpCsvFile);

		return $newTmpCsvFile;

	}

}

function create_sla_delay_array(){
	//array size is 52

	$partner_name = array('Albit', '3BG', 'Tarin', 'NewNet', 'Golden Data', 'Power Hand', 'FM Company', 'Delta', 'Empire Net', 'Blue Fiber', 'Lebanese', 'Fiber Speed Erbil', 'Fiber Speed Duhok', 'Trust', 'Waarpak', 'Danet', 'TNet Soran', 'Delta Zakho', 'First Line', 'Partners group', 'TimeNet', 'Big Line', 'O3', 'Slava', 'Strong Link');

	$new_csv_array[0] = array('Partner', 'Ticket Type', 'Total');

	foreach ($partner_name as $partner){

		$new_csv_array[] = array($partner, 'New installation', 0);
		$new_csv_array[] = array($partner, 'Maintenance', 0);

	}

	$new_csv_array[] = array('Total/Day', '', 0);

	return $new_csv_array;

}

function create_partner_delay_array(){

	$partner_name = array('Albit', '3BG', 'Tarin', 'NewNet', 'Golden Data', 'Power Hand', 'FM Company', 'Delta', 'Empire Net', 'Blue Fiber', 'Lebanese', 'Fiber Speed Erbil', 'Fiber Speed Duhok', 'Trust', 'Waarpak', 'Danet', 'TNet Soran', 'Delta Zakho', 'First Line', 'Partners group', 'TimeNet', 'Big Line', 'O3', 'Slava', 'Strong Link');

	foreach ($partner_name as $partner)
		$pd_array[$partner][] = array('Ticket Number', 'Date', 'Subject', 'From Email', 'Partner Name');

	return $pd_array;

}

function filter_tickets_for_delay_sla($oFile){

	$filterBySubject = ["Adding new FAT", "Cancellation", "Change cable direction", "Change FAT location", "Change package", "Civil and Pole issues", "FAT issue", "FAT label issue", "FAT low optical power", "FAT no power", "FAT swap", "O3 cable cut", "Problem", "Refill issue", "Voucher issue", "Cable down", "ONT issue"];

	$filterByFrom = ["Backoffice", "IP-Core"];

	$csv = array_map('str_getcsv', file($oFile));

	//Sort array by date adn order by oldest to newest
	function sort_date($fd, $sd) { return strtotime($fd[1]) - strtotime($sd[1]); }
	usort($csv, "sort_date");

	$new_csv_array = create_sla_delay_array(); // for SLA delays

	$new_array[0] = $main_array[0] = array('Ticket', 'Partner', 'Date Ticket Opened'); // For Ticket delyas (new)

	$inTotalTicket = 0; // total new installtion tickets

	$maTotalTicket = 0; // total maintenance tickets

	global $today;

	for($i = 0; $i < sizeof($csv[0]); $i++){

		if(strpos($csv[0][$i], 'Ticket Number') !== false)
			$ticketX = $i; //Get Ticket Number index(column)
		if($csv[0][$i] == "Date Created")
			$dateX = $i; //Get date index(column)
		if($csv[0][$i] == "Subject")
			$subjectX = $i; //Get Subject index(column)
		if($csv[0][$i] == "From")
			$fromX = $i; //Get From index(column)
		if($csv[0][$i] == "Partner Name")
			$partnerX = $i; //Get Partner Name index(column)

	}

	if(isset($ticketX) && isset($dateX) && isset($subjectX) && isset($fromX) && isset($partnerX)){ // If all exsists procced, else show error

		foreach ($csv as $row) {

			if(!is_numeric($row[$ticketX])) // to skip the header row
				continue;

			foreach ($filterBySubject as $sub) //Filter by subjects
				if(strpos($row[$subjectX], $sub) !== false)
					continue 2;

			foreach ($filterByFrom as $from) //Filter by BO nad IP
				if(strpos($row[$fromX], $from) !== false)
					continue 2;

			$strTime = date('d/m/Y', strtotime($row[$dateX])); //Remove time from date

			if(isset($_POST['notToday']) && $strTime == $today)
				continue;

			$arraySize = sizeof($new_csv_array[0]);

			for($i = 2; $i < $arraySize; $i++){ //loop to add the new dates

				if($strTime == $new_csv_array[0][$i]){ //if date exsits the get index

					$dateIndex = $i;
					break;

				} elseif ($new_csv_array[0][$i] == "Total") { //else create new date and get index

					$new_csv_array[0][$i] = $strTime;
					$new_csv_array[0][] = "Total";

					for($i = 1; $i < sizeof($new_csv_array); $i++)
						$new_csv_array[$i][] = 0;

					$dateIndex = $arraySize - 1;

				}

			}

			$datetime1 = new DateTime();
			$datetime2 = new DateTime($row[$dateX]);
			$interval = $datetime1->diff($datetime2);

			if(strpos($row[$subjectX], "w ins") !== false || strpos($row[$subjectX], "Reactivation") !== false){ //if the ticket type is new isntallation

				for($i = 1; $i < sizeof($new_csv_array); $i += 2){ //go to the first column of each partner (new installation) of the array

					// if partner match, then add the number
					if($new_csv_array[$i][0] == "Partners group" && ($row[$partnerX] == "Partners group" || $row[$partnerX] == "Pro Fiber") ){

						$new_csv_array[$i][$dateIndex] += 1;
						$inTotalTicket++;
						break;


					} elseif($new_csv_array[$i][0] == "Tarin" && ($row[$partnerX] == "Tarin Net" || $row[$partnerX] == "Tarin") ){

						$new_csv_array[$i][$dateIndex] += 1;
						$inTotalTicket++;
						break;


					} elseif($new_csv_array[$i][0] == "Golden Data" && ($row[$partnerX] == "Golden Data" || $row[$partnerX] == "Golden-Data") ){

						$new_csv_array[$i][$dateIndex] += 1;
						$inTotalTicket++;
						break;


					} elseif($row[$partnerX] == $new_csv_array[$i][0]){

						$new_csv_array[$i][$dateIndex] += 1;
						$inTotalTicket++;
						break;

					}
				}

				if($interval->format('%a') >= 2) // If date is more than 2 days, then add for the other worksheet for new installation
					$new_array[] = array($row[$ticketX], $row[$partnerX], date('d/m/Y H:i', strtotime($row[$dateX])));

			} else{ // else if the ticket type is maintenance

				for($i = 2; $i < sizeof($new_csv_array); $i += 2){ //go to the second column of each partner (maintenance) of the array

					// if partner match, then add the number
					if($new_csv_array[$i][0] == "Partners group" && ($row[$partnerX] == "Partners group" || $row[$partnerX] == "Pro Fiber") ){

						$new_csv_array[$i][$dateIndex] += 1;
						$maTotalTicket++;
						break;

					} elseif($new_csv_array[$i][0] == "Tarin" && ($row[$partnerX] == "Tarin Net" || $row[$partnerX] == "Tarin") ){

						$new_csv_array[$i][$dateIndex] += 1;
						$maTotalTicket++;
						break;


					} elseif($new_csv_array[$i][0] == "Golden Data" && ($row[$partnerX] == "Golden Data" || $row[$partnerX] == "Golden-Data") ){

						$new_csv_array[$i][$dateIndex] += 1;
						$maTotalTicket++;
						break;


					} elseif($row[$partnerX] == $new_csv_array[$i][0]){

						$new_csv_array[$i][$dateIndex] += 1;
						$maTotalTicket++;
						break;

					}

				}

				if($interval->format('%a') >= 1) // If date is more than 2 days, then add for the other worksheet for maintenance
					$main_array[] = array($row[$ticketX], $row[$partnerX], date('d/m/Y H:i', strtotime($row[$dateX])));

			}

		}

		$nesttedArraySize = sizeof($new_csv_array[0]) - 1;

		//Calculate the ticket total of each row of Partners' maintenance and new installation tickets
		for($i = 1; $i < sizeof($new_csv_array); $i++)
			for($j = 2; $j < $nesttedArraySize; $j++)
				$new_csv_array[$i][$nesttedArraySize] += $new_csv_array[$i][$j];

		//Calculate the ticket total of each column of the day
		for($i = 2; $i < $nesttedArraySize + 1; $i++){

	        $sumCol = 0;

	        for($j = 1; $j < sizeof($new_csv_array); $j++){
	        	$sumCol += $new_csv_array[$j][$i];

	        	if($new_csv_array[$j][$i] == 0 && $j != 51)
	        		$new_csv_array[$j][$i] = "";

	        }

	        $new_csv_array[51][$i] += $sumCol;

	    }

		$new_csv_array[] = array('Total New Installation', '', $inTotalTicket);
		$new_csv_array[] = array('Total Maintenane', '', $maTotalTicket);
		$new_csv_array[] = array('Total Tickets', '', $inTotalTicket + $maTotalTicket);

		convert_to_xlsx_for_delay_sla($new_csv_array, $new_array, $main_array);

		exit();

	} else{ header('Location: index.php?m=ec'); }

}

function filter_tickets_for_partner_delay($oFile){

	$filterBySubject = ["Adding new FAT", "Cancellation", "Change cable direction", "Change FAT location", "Change package", "Civil and Pole issues", "FAT issue", "FAT label issue", "FAT low optical power", "FAT no power", "FAT swap", "O3 cable cut", "Problem", "Refill issue", "Voucher issue", "Cable down", "ONT issue"];

	$filterByFrom = ["Backoffice", "IP-Core"];

	$csv = array_map('str_getcsv', file($oFile));

	//Sort array by date adn order by oldest to newest
	function sort_date($fd, $sd) { return strtotime($fd[1]) - strtotime($sd[1]); }
	usort($csv, "sort_date");

	$pd_array = create_partner_delay_array(); // For new partner delay excel

	global $today;

	for($i = 0; $i < sizeof($csv[0]); $i++){
		if(strpos($csv[0][$i], 'Ticket Number') !== false)
			$ticketX = $i; //Get Ticket Number index(column)
		if($csv[0][$i] == "Date Created")
			$dateX = $i; //Get date index(column)
		if($csv[0][$i] == "Subject")
			$subjectX = $i; //Get Subject index(column)
		if($csv[0][$i] == "From")
			$fromX = $i; //Get From index(column)
		if($csv[0][$i] == "From Email")
			$fromEmailX = $i; //Get From Email index(column)
		if($csv[0][$i] == "Partner Name")
			$partnerX = $i; //Get Partner Name index(column)
	}

	if(isset($ticketX) && isset($dateX) && isset($subjectX) && isset($fromX) && isset($fromEmailX) && isset($partnerX)){

		foreach ($csv as $row) {

			if(!is_numeric($row[$ticketX])) // to skip the header row
				continue;

			foreach ($filterBySubject as $sub) //Filter by subjects
				if(strpos($row[$subjectX], $sub) !== false)
					continue 2;

			foreach ($filterByFrom as $from) //Filter by BO nad IP
				if(strpos($row[$fromX], $from) !== false)
					continue 2;

			$datetime1 = new DateTime($_POST['pdDate']);
			$datetime2 = new DateTime($row[$dateX]);

			if($datetime2 > $datetime1) // If the date is bigger than the date picked then don't add
				continue;

			if (array_key_exists($row[$partnerX], $pd_array)) // If partner name exsistes, then add
   				$pd_array[$row[$partnerX]][] = array($row[$ticketX], $row[$dateX], $row[$subjectX], $row[$fromEmailX], $row[$partnerX]);

			elseif(($row[$partnerX] == "Partners group" || $row[$partnerX] == "Pro Fiber") )
				$pd_array['Partners group'][] = array($row[$ticketX], $row[$dateX], $row[$subjectX], $row[$fromEmailX], $row[$partnerX]);

			elseif(($row[$partnerX] == "Tarin Net" || $row[$partnerX] == "Tarin") )
				$pd_array['Tarin Net'][] = array($row[$ticketX], $row[$dateX], $row[$subjectX], $row[$fromEmailX], $row[$partnerX]);

			elseif(($row[$partnerX] == "Golden Data" || $row[$partnerX] == "Golden-Data") )
				$pd_array['Golden Data'][] = array($row[$ticketX], $row[$dateX], $row[$subjectX], $row[$fromEmailX], $row[$partnerX]);

		}

		foreach ($pd_array as $key => $partner) // If no ticket for current partner, then remove from array (to remove form worksheet)
			if(!isset($partner[1]))
				unset($pd_array[$key]);

		convert_to_xlsx_for_partner_delay($pd_array);

		exit();

	} else{ header('Location: index.php?m=epd'); }

}

function convert_to_xlsx_for_delay_sla($delay, $newArr, $mainArr){

	$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

	$spreadsheet = write_array_to_sheet($spreadsheet, $delay, 'SLA Delay'); // Added array to worksheet 'SLA Delay'

	$spreadsheet = write_array_to_sheet($spreadsheet, $newArr, 'New 48 Hrs'); // Added array to worksheet 'New 48 Hrs'

	$spreadsheet = write_array_to_sheet($spreadsheet, $mainArr, 'Main 24 Hrs'); // Added array to worksheet 'Main 24 Hrs'

	$spreadsheet->removeSheetByIndex(0);

	$newTmpXlsxFile = stream_get_meta_data(tmpfile())['uri']; // Write file as '.xlsx'
	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer->save($newTmpXlsxFile);

	butify_xlsx_for_delay_sla($newTmpXlsxFile);

}

function convert_to_xlsx_for_partner_delay($pd_array){

	$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

	foreach ($pd_array as $key => $partner) // Added each partner to a worksheet with their tickets.
		$spreadsheet = write_array_to_sheet($spreadsheet, $pd_array[$key], $key);

	$spreadsheet->removeSheetByIndex(0);

	$newTmpXlsxFile = stream_get_meta_data(tmpfile())['uri']; // Write file as '.xlsx'
	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer->save($newTmpXlsxFile);

	butify_xlsx_for_partner_delay($newTmpXlsxFile);

}

function write_array_to_sheet($spreadsheet, $array, $sheetTitle){

	$spreadsheet->createSheet();
	$spreadsheet->setActiveSheetIndex($spreadsheet->getSheetCount() - 1);
	$spreadsheet->getActiveSheet()->setTitle($sheetTitle);
	$sheet = $spreadsheet->getActiveSheet();

	for($i = 0; $i < count($array); $i++){ // Convert array to a worksheet

		$row = $array[$i];
		$j = 1;

		foreach($row as $x => $x_value) {
			$sheet->setCellValueByColumnAndRow($j, $i + 1, $x_value);
	  		$j = $j + 1;
		}

	}

	$spreadsheet->setActiveSheetIndex(0);

	return $spreadsheet;

}

function butify_xlsx_for_delay_sla($xlsxFile){

	$spreadsheet = IOFactory::load($xlsxFile);

	$sheet = $spreadsheet->getActiveSheet();

	$highestColumn = $sheet->getHighestColumn();
	$highestRow = $sheet->getHighestRow();

	// Fix date columns
	$date_format = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX16;

	for ($i = 'C'; $i < $highestColumn; $i++){

		$dateTime = date($sheet->getCell($i . "1")->getValue());

		$excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateTime);

		$sheet->getStyle($i . "1") ->getNumberFormat()->setFormatCode($date_format);

		$sheet->setCellValue($i . "1", intval($excelDateValue));

	}
	// Fix date columns

	// First row bold
	$styleArrayFirstRow = [
	            'font' => [
	                'bold' => true,
	            ]
	        ];

	$sheet->getStyle('A1:' . $highestColumn . '1' )->applyFromArray($styleArrayFirstRow);
	// First row bold

	// Freez first row and columns 1 & 2
	$sheet->freezePane('C2');
	// Freez first row and columns 1 & 2

	// AutoSize all columns
	for ($i = 'A'; $i <= $highestColumn; $i++){
	    $sheet->getColumnDimension("$i")->setAutoSize(TRUE);
	}
	// AutoSize all columns

	// Align all cells to center
	$center_horizontal = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
	$center_vertical = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

	foreach($sheet->getRowIterator() as $row) {
	    foreach($row->getCellIterator() as $cell) {
	        $cellCoordinate = $cell->getCoordinate();
	        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal($center_horizontal);
	        $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical($center_vertical);
	    }
	}
	// Align all cells to center

	// Backgorund colors
	$fill_bg = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;

	$sheet->getStyle("A1:$highestColumn" . "1")->getFill()->setFillType($fill_bg)->getStartColor()->setARGB('00B0F0');

	for ($i = 'A'; $i < $highestColumn; $i++){
		for ($j = 2; $j < $highestRow - 4; $j += 4){
			$sheet->getStyle("$i" . $j)->getFill()->setFillType($fill_bg)->getStartColor()->setARGB('D6DCE4');
			$sheet->getStyle("$i" . ($j + 1))->getFill()->setFillType($fill_bg)->getStartColor()->setARGB('D6DCE4');
		}
	}

	for ($j = 2; $j < $highestRow - 4; $j += 2){
			$sheet->getStyle($highestColumn . "$j")->getFill()->setFillType($fill_bg)->getStartColor()->setARGB('FFC000');
			$sheet->getStyle("$highestColumn" . ($j + 1))->getFill()->setFillType($fill_bg)->getStartColor()->setARGB('8EA9DB');
	}

	// Color header row

	// Set boarder for cells
	$styleArray = [
	    'borders' => [
	  		'allBorders' => [
	            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            	'color' => ['argb' => '000000']
	        ]
	   	 ]
	];

	$sheet->getStyle("A1:$highestColumn$highestRow")->applyFromArray($styleArray);
	// Set boarder for cells

	// Merge cells
	for ($j = 2; $j <= 51; $j += 2){
		$p = $j + 1;
		$sheet->mergeCells("A$j:A$p");
	}

	$sheet->mergeCells("A52:B52");
	$sheet->mergeCells("A53:B53");
	$sheet->mergeCells("A54:B54");
	$sheet->mergeCells("A55:B55");

	$sheet->mergeCells("C53:$highestColumn" . "53");
	$sheet->mergeCells("C54:$highestColumn" . "54");
	$sheet->mergeCells("C55:$highestColumn" . "55");
	// Merge cells


	// Other two sheets //


	$spreadsheet = new_delay_sheet_style($spreadsheet, 1, 'C848FF');
	$spreadsheet = new_delay_sheet_style($spreadsheet, 2, 'FF4898');

	$spreadsheet->setActiveSheetIndex(0);

	save_xlsx_for_delay_sla($spreadsheet);

}

function butify_xlsx_for_partner_delay($xlsxFile){

	$spreadsheet = IOFactory::load($xlsxFile);


	$sheetCount = $spreadsheet->getSheetCount();
	for ($c = 0; $c < $sheetCount; $c++) { // Do same style for ach worksheet

	    $sheet = $spreadsheet->getSheet($c);

	    $highestColumn = $sheet->getHighestColumn();
		$highestRow = $sheet->getHighestRow();

		// First row bold
		$styleArrayFirstRow = [
		            'font' => [
		                'bold' => true,
		            ]
		        ];

		$sheet->getStyle('A1:' . $highestColumn . '1' )->applyFromArray($styleArrayFirstRow);
		// First row bold

		// Freez first row
		$sheet->freezePane('A2');
		// Freez first row

		// AutoSize all columns
		for ($i = 'A'; $i <= $highestColumn; $i++){
		    $sheet->getColumnDimension("$i")->setAutoSize(TRUE);
		}
		// AutoSize all columns

		// Align all cells to center
		$center_horizontal = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		$center_vertical = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

		foreach($sheet->getRowIterator() as $row) {
		    foreach($row->getCellIterator() as $cell) {
		        $cellCoordinate = $cell->getCoordinate();
		        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal($center_horizontal);
		        $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical($center_vertical);
		    }
		}
		// Align all cells to center

		// Set boarder for cells
		$styleArray = [
		    'borders' => [
		  		'allBorders' => [
		            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
	            	'color' => ['argb' => '000000']
		        ]
		   	 ]
		];

		$sheet->getStyle("A1:$highestColumn$highestRow")->applyFromArray($styleArray);
		// Set boarder for cells

	}

	$spreadsheet->setActiveSheetIndex(0);

	save_xlsx_for_partner_delay($spreadsheet);

}

function new_delay_sheet_style($spreadsheet,$sheetIndex, $tabColor){

	$spreadsheet->setActiveSheetIndex($sheetIndex);

	$sheet = $spreadsheet->getActiveSheet();

	$highestColumn = $sheet->getHighestColumn();
	$highestRow = $sheet->getHighestRow();

	// First row bold and background color
	$fill_bg = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;

	$styleArrayFirstRow = [
	            'font' => [
	                'bold' => true,
	            ]
	        ];

	$sheet->getStyle('A1:' . $highestColumn . '1' )->applyFromArray($styleArrayFirstRow);

	$sheet->getStyle("A1:$highestColumn" . "1")->getFill()->setFillType($fill_bg)->getStartColor()->setARGB($tabColor);
	// First row bold and background color

	// Freez first row
	$sheet->freezePane('A2');
	// Freez first row

	// AutoSize all columns
	for ($i = 'A'; $i <= $highestColumn; $i++){
	    $sheet->getColumnDimension("$i")->setAutoSize(TRUE);
	}
	// AutoSize all columns

	// Align all cells to center
	$center_horizontal = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
	$center_vertical = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;

	foreach($sheet->getRowIterator() as $row) {
	    foreach($row->getCellIterator() as $cell) {
	        $cellCoordinate = $cell->getCoordinate();
	        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal($center_horizontal);
	        $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical($center_vertical);
	    }
	}
	// Align all cells to center

	// Set boarder for cells
	$styleArray = [
	    'borders' => [
	  		'allBorders' => [
	            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            	'color' => ['argb' => '000000']
	        ]
	   	 ]
	];

	$sheet->getStyle("A1:$highestColumn$highestRow")->applyFromArray($styleArray);
	// Set boarder for cells

	return $spreadsheet;

}

function save_xlsx_for_delay_sla($spreadsheet){

	header('Content-Disposition: attachment; filename="Tickets SLA Delay ' . date("d/m/Y") . '.xlsx"');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer->save('php://output');

}

function save_xlsx_for_partner_delay($spreadsheet){

	header('Content-Disposition: attachment; filename="Tickets Partner Delay ' . date("d/m/Y") . '.xlsx"');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer->save('php://output');

}

?>