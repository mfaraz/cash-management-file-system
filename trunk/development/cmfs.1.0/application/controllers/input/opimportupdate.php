<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/MemberBase.php' );

// --

class opimportupdate extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('m_input');
        $this->load->model('m_output');
        $this->load->model('m_importupdates');
        $this->load->model('m_specialfield');
        // load library
        $this->load->library('tnotification');
    }

    // index
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->smarty->assign("template_content", "input/opimportupdate/index.html");
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // update
    public function update($file_name = '') {
        //set page rules
        $this->_set_page_rule("U");
        //set template content
        $this->smarty->assign("template_content", "input/opimportupdate/update.html");
        //plugins
        require_once( BASEPATH . 'plugins/excelreader/excelreader.php');
        //read data
        $file_path = "resource/doc/importupdate/input/" . $file_name . '.xls';
        //assign rs output 
        $this->smarty->assign('file_name', $file_name);
        //process
        $input_id = '';
        $rows = array();
        $fields = array();
        $rs_input = array();
        if (is_file($file_path)) {
            // load excel reader
            $obj_excel_reader = new Spreadsheet_Excel_Reader();
            $obj_excel_reader->setOutputEncoding('CP1251');
            // read excel
            $obj_excel_reader->read($file_path);
            // read
            if (!empty($obj_excel_reader->sheets)) {
                foreach ($obj_excel_reader->sheets as $key => $val) {
                    if ($key == 0) {
                        // get search parameter
                        $search_params = !empty($val['cells']['7']['3']) ? $val['cells']['7']['3'] : '%';
                        //-
                        $rs_input = array(
                            !empty($val['cells']['4']['3']) ? $val['cells']['4']['3'] : '-',
                            !empty($val['cells']['5']['3']) ? $val['cells']['5']['3'] : '-',
                            !empty($val['cells']['6']['3']) ? $val['cells']['6']['3'] : '-',
                            !empty($val['cells']['7']['3']) ? $val['cells']['7']['3'] : '-',
                            !empty($val['cells']['8']['3']) ? $val['cells']['8']['3'] : '-',
                            !empty($val['cells']['9']['3']) ? $val['cells']['9']['3'] : '-',
                            !empty($val['cells']['10']['3']) ? $val['cells']['10']['3'] : '',
                            !empty($val['cells']['11']['3']) ? $val['cells']['11']['3'] : '-'
                        );
                        //insert row
                        foreach ($val['cells'] as $k => $row) {
                            if ($k >= 14) {
                                $rows[$k] = array(
                                    !empty($val['cells']['4']['3']) ? $val['cells']['4']['3'] : '-',
                                    !empty($row['2']) ? $row['2'] : '-',
                                    !empty($row['3']) ? $row['3'] : '-'
                                );
                            }
                        }
                    }
                    if ($key == 1) {
                        foreach ($val['cells'] as $key => $value) {
                            if ($key >= 3) {
                                $fields[$key] =
                                        array(
                                            !empty($value['2']) ? $value['2'] : '-',
                                            !empty($value['3']) ? $value['3'] : '-',
                                            !empty($value['4']) ? $value['4'] : '-'
                                );
                            }
                        }
                    }
                }
            }
            // get list input versions
            $rs_id = $this->m_output->get_list_version_by_type($search_params);
            $this->smarty->assign("input_format", $rs_id);
        } else {
            // jika gagal
            $this->tnotification->set_error_message('File not Found');
        }
        //assign rs output 
        $this->smarty->assign('rs_input', $rs_input);
        //assign rows
        $this->smarty->assign('rows', $rows);
        //assign field
        $this->smarty->assign('fields', $fields);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // process update
    public function process_update() {
        // set page rules
        $this->_set_page_rule("U");
        // plugins
        require_once( BASEPATH . 'plugins/excelreader/excelreader.php');
        // cek input
        $this->tnotification->set_rules('input_id', 'Input ID', 'trim|required');
        $this->tnotification->set_rules('output_id', 'Versi Format Output', 'trim|required');
        if ($this->tnotification->run() !== FALSE) {
            // read data
            $file_path = "resource/doc/importupdate/input/" . $this->input->post('file_name') . '.xls';
            if (is_file($file_path)) {
                // load excel reader
                $obj_excel_reader = new Spreadsheet_Excel_Reader();
                $obj_excel_reader->setOutputEncoding('CP1251');
                // read excel
                $obj_excel_reader->read($file_path);
                // read
                $output_id = $this->input->post('output_id');
                if (!empty($obj_excel_reader->sheets)) {
                    foreach ($obj_excel_reader->sheets as $key => $val) {
                        if ($key == 0) {
                            if (!empty($val['cells']['4']['3']) && !empty($val['cells']['6']['3'])) {
                                if (is_numeric($val['cells']['4']['3'])) {
                                    $input_id = $val['cells']['4']['3'];
                                } else {
                                    // jika gagal
                                    $this->tnotification->set_error_message('File Format does not match');
                                    $this->tnotification->sent_notification("error", "Process fails");
                                    //-- default redirect
                                    redirect("input/opimportupdate");
                                }
                                $params = array(
                                    $val['cells']['4']['3'],
                                    $output_id,
                                    $val['cells']['6']['3'],
                                    !empty($val['cells']['7']['3']) ? $val['cells']['7']['3'] : '',
                                    !empty($val['cells']['8']['3']) ? $val['cells']['8']['3'] : '',
                                    !empty($val['cells']['9']['3']) ? $val['cells']['9']['3'] : '',
                                    !empty($val['cells']['10']['3']) ? $val['cells']['10']['3'] : ''
                                );
                                //delete if exist
                                $this->m_input->delete($val['cells']['4']['3']);
                                //insert 
                                $this->m_input->insert_update($params);
                                //insert row
                                foreach ($val['cells'] as $k => $row) {
                                    if ($k > 13) {
                                        if (!empty($row['2']) && !empty($row['3'])) {
                                            $params = array($val['cells']['4']['3'], $row['2'], $row['3']);
                                            //insert row
                                            $this->m_input->insert_rows($params);
                                        }
                                    }
                                }
                            } else {
                                // jika gagal
                                $this->tnotification->set_error_message('File Format does not match');
                                $this->tnotification->sent_notification("error", "Process fails");
                                //-- default redirect
                                redirect("input/opimportupdate/");
                            }
                        }
                        // insert field input description 
                        if ($key == 1) {
                            foreach ($val['cells'] as $key => $value) {
                                if ($key >= 3) {
                                    if (!empty($value['2'])) {
                                        $params = array(
                                            $input_id,
                                            $value['2'],
                                            !empty($value['3']) ? trim(mb_convert_encoding($value['3'], "UTF-8", "ISO-8859-9")) : '',
                                            !empty($value['4']) ? trim(mb_convert_encoding($value['4'], "UTF-8", "ISO-8859-9")) : ''
                                        );
                                        //insert
                                        $this->m_input->insert_field($params);
                                    }
                                }
                            }
                        }
                        //inset field mapping
                        if ($key == 2) {
                            $id_params = array($output_id, $input_id);
                            $this->m_input->delete_mapping_by_id($id_params);
                            foreach ($val['cells'] as $key => $fieldmap) {
                                if ($key >= 3) {
                                    if (!empty($fieldmap['2']) && !empty($fieldmap['3'])) {
                                        $params = array(
                                            $output_id,
                                            intval($fieldmap['2']),
                                            $input_id,
                                            intval($fieldmap['3']),
                                            !empty($fieldmap['4']) ? $fieldmap['4'] : '',
                                            !empty($fieldmap['5']) ? intval($fieldmap['5']) : 0
                                        );
                                        //insert
                                        $this->m_input->insert_mapping($params);
                                    }
                                }
                            }
                        }
                        //default success
                        $this->tnotification->sent_notification("success", "Data saved successfully");
                    }
                } else {
                    // default error
                    $this->tnotification->set_error_message('File is empty');
                    $this->tnotification->sent_notification("error", "Process fails");
                }
                if (is_file($file_path)) {
                    unlink($file_path);
                }
            } else {
                // jika gagal
                $this->tnotification->set_error_message('File not Found');
                $this->tnotification->sent_notification("error", "Process fails");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Process fails");
            //-- default redirect
            redirect("input/opimportupdate/update/" . $this->input->post('file_name'));
        }
        //-- default redirect
        redirect("input/opimportupdate");
    }

    // export to excel file
    public function export_to_excel($input_id = '') {
        //load library
        $this->load->library('phpexcel');
        //load data
        $rs_id = $this->m_input->get_version_detail_by_id($input_id);
        $rs_list = $this->m_input->get_all_field_by_versions($input_id);
        $rs_row = $this->m_input->get_all_input_rows_by_id($input_id);
        $rs_mapping = $this->m_input->get_list_field_mapping_by_id($input_id);
        //border style
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $styleBold = array(
            'font' => array(
                'bold' => true
                ));
        if (!empty($rs_id)) {
            //header
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B2', 'FORMAT INPUT' . strtoupper(isset($rs_id['input_format_type']) ? $rs_id['input_format_type'] : '-'));
            // Input versions
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B4', 'Input ID');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C4', (isset($rs_id['input_id']) ? $rs_id['input_id'] : ''));
            // Output versions
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B5', 'Output ID');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C5', (isset($rs_id['output_id']) ? $rs_id['output_id'] : ''));
            // Input Versions
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B6', 'Input Version');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C6', (isset($rs_id['input_version']) ? $rs_id['input_version'] : '-'));
            // Format Type 
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B7', 'Input Format Type');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C7', (isset($rs_id['input_format_type']) ? $rs_id['input_format_type'] : '-'));
            // File Type 
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B8', 'Input File Type');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C8', (isset($rs_id['input_file_type']) ? $rs_id['input_file_type'] : '-'));
            // Row Number 
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B9', 'Start Input Row');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C9', (isset($rs_id['input_row']) ? $rs_id['input_row'] : '-'));
            // Input Delimeter 
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B10', 'Input Delimiter');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C10', (isset($rs_id['input_delimiter']) ? $rs_id['input_delimiter'] : '-'));
            // Output Versions 
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B11', 'Output Version');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C11', (isset($rs_id['output_version']) ? $rs_id['output_version'] : '-'));
            //input row
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B13', 'Row Number');
            $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C13', 'Total Column');
            //set style header
            $this->phpexcel->setActiveSheetIndex(0)
                    ->getStyle('B13:C13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('808080');
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B13:C13')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B13:C13')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B13:C13')->applyFromArray($styleArray);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B13:C13')->applyFromArray($styleBold);
            $row_n = 14;
            foreach ($rs_row as $row) {
                $this->phpexcel->setActiveSheetIndex(0)->setCellValue('B' . $row_n, $row['row_number']);
                $this->phpexcel->setActiveSheetIndex(0)->setCellValue('C' . $row_n, $row['total_column']);
                $row_n++;
            }
            $row = $row_n - 1;
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B14:C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B14:C' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B14:C' . $row)->applyFromArray($styleArray);
            //set style
            $this->phpexcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
            $this->phpexcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(40);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B4:C11')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B4:C11')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B4:C11')->applyFromArray($styleArray);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B4:B11')->applyFromArray($styleBold);
            //set style header
            $this->phpexcel->setActiveSheetIndex(0)
                    ->getStyle('B2:C3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('808080');
            $this->phpexcel->setActiveSheetIndex(0)->mergeCells('B2:C3');
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B2:C3')->applyFromArray($styleArray);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B2:B3')->applyFromArray($styleBold);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B2:C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(0)->getStyle('B2:C3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->getActiveSheet()->setTitle('Info Format Input');
            // sheet 2
            $this->phpexcel->createSheet(1);
            $this->phpexcel->setActiveSheetIndex(1)->setCellValue('B2', 'No');
            $this->phpexcel->setActiveSheetIndex(1)->setCellValue('C2', 'Nama Field');
            $this->phpexcel->setActiveSheetIndex(1)->setCellValue('D2', 'Keterangan Field');
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('B2:D2')->applyFromArray($styleArray);
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('B2:D2')->applyFromArray($styleBold);
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('B2:D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('B2:D2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(1)->getColumnDimension('B')->setWidth(10);
            $this->phpexcel->setActiveSheetIndex(1)->getColumnDimension('C')->setWidth(60);
            $this->phpexcel->setActiveSheetIndex(1)->getColumnDimension('D')->setWidth(75);
            $this->phpexcel->setActiveSheetIndex(1)
                    ->getStyle('B2:D2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('808080');
            $row = 3;
            foreach ($rs_list as $value) {
                $this->phpexcel->setActiveSheetIndex(1)->setCellValue('B' . $row, $value['field_number']);
                $this->phpexcel->setActiveSheetIndex(1)->setCellValue('C' . $row, $value['field_name']);
                $this->phpexcel->setActiveSheetIndex(1)->setCellValue('D' . $row, $value['field_desc']);
                $this->phpexcel->setActiveSheetIndex(1)->getCell('C' . $row)->setValueExplicit((isset($value['field_name']) ? trim(mb_convert_encoding($value['field_name'], "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                $this->phpexcel->setActiveSheetIndex(1)->getCell('D' . $row)->setValueExplicit((isset($value['field_desc']) ? trim(mb_convert_encoding($value['field_desc'], "UTF-8", "ISO-8859-9")) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
                $row++;
            }
            $row = $row - 1;
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('B3:D' . $row)->applyFromArray($styleArray);
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('C3:D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('B3:B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(1)->getStyle('B3:D' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->getActiveSheet()->setTitle('Format Field');

            // sheet 3
            $this->phpexcel->createSheet(2);
            $this->phpexcel->setActiveSheetIndex(2)->setCellValue('B2', 'Output Field Number');
            $this->phpexcel->setActiveSheetIndex(2)->setCellValue('C2', 'Input Field Number');
            $this->phpexcel->setActiveSheetIndex(2)->setCellValue('D2', 'Alternatif');
            $this->phpexcel->setActiveSheetIndex(2)->setCellValue('E2', 'Order Number');
            $this->phpexcel->setActiveSheetIndex(2)->getStyle('B2:E2')->applyFromArray($styleArray);
            $this->phpexcel->setActiveSheetIndex(2)->getStyle('B2:E2')->applyFromArray($styleBold);
            $this->phpexcel->setActiveSheetIndex(2)->getStyle('B2:E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(2)->getStyle('B2:E2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(2)->getColumnDimension('B')->setWidth(25);
            $this->phpexcel->setActiveSheetIndex(2)->getColumnDimension('C')->setWidth(25);
            $this->phpexcel->setActiveSheetIndex(2)->getColumnDimension('D')->setWidth(25);
            $this->phpexcel->setActiveSheetIndex(2)->getColumnDimension('E')->setWidth(25);
            $this->phpexcel->setActiveSheetIndex(2)
                    ->getStyle('B2:E2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('808080');
            $row = 3;
            foreach ($rs_mapping as $value) {
                $this->phpexcel->setActiveSheetIndex(2)->setCellValue('B' . $row, $value['output_field_number']);
                $this->phpexcel->setActiveSheetIndex(2)->setCellValue('C' . $row, $value['input_field_number']);
                $this->phpexcel->setActiveSheetIndex(2)->setCellValue('D' . $row, $value['alternatif']);
                $this->phpexcel->setActiveSheetIndex(2)->setCellValue('E' . $row, $value['order_number']);
                $row++;
            }
            $row = $row - 1;
            $this->phpexcel->setActiveSheetIndex(2)->getStyle('B3:E' . $row)->applyFromArray($styleArray);
            $this->phpexcel->setActiveSheetIndex(2)->getStyle('B3:E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->phpexcel->setActiveSheetIndex(2)->getStyle('B3:E' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $this->phpexcel->getActiveSheet()->setTitle('Mapping Field');
            // download
            if ($rs_id['input_format_type'] == 'Batch Upload') {
                $file_name = 'batch_input_' . (isset($rs_id['input_id']) ? $rs_id['input_id'] : '') . '.xls';
            } else {
                $file_name = 'single_input_' . (isset($rs_id['input_id']) ? $rs_id['input_id'] : '') . '.xls';
            }
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=' . $file_name);
            header('Cache-Control: max-age=0');
            // output
            $obj_writer = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
            $obj_writer->save('php://output');
        }
    }

    // process import updates 
    public function process_upload() {
        // set page rules
        $this->_set_page_rule("C");
        // load
        $this->load->library('tupload');
        // cek file
        if (empty($_FILES['upload_file']['tmp_name'])) {
            $this->tnotification->set_error_message('File not found');
            $this->tnotification->sent_notification("error", "Process fails");
        }
        // upload
        if (!empty($_FILES['upload_file']['tmp_name'])) {
            // upload config
            $config['upload_path'] = 'resource/doc/importupdate/input/';
            $config['allowed_types'] = 'xls';
            $config['file_name'] = date("Ymds");
            $this->tupload->initialize($config);
            // process upload
            if ($this->tupload->do_upload('upload_file')) {
                //default notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data uploaded successfully");
                //redirect if succes upload
                redirect("input/opimportupdate/update/" . $config['file_name']);
            } else {
                // jika gagal
                $this->tnotification->set_error_message($this->tupload->display_errors());
                $this->tnotification->sent_notification("error", "Process fails");
            }
        }
        //-- default redirect
        redirect("input/opimportupdate");
    }

}