<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// --
class m_utility extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /*
     * Date Format
     */

    // get format date
    function get_cmfs_date($format, $input) {
        $error_format = 0;
        // get format input client
        $format = strtolower(trim($format));
        $input = strtolower(trim($input));
        switch ($format) {
            case 'yyyymmdd':
                $result = date('Ymd');
                if (strlen($input) <= 8) {
                    $tahun = substr($input, 0, 4);
                    $bulan = substr($input, 4, 2);
                    $tgl = substr($input, 6, 2);

                    if (is_numeric($tahun) && is_numeric($bulan) && is_numeric($tgl)) {
                        $result = $tahun . $bulan . $tgl;
                    }
                }
                return $result;
                break;
            case 'yymmdd':
                $result = date('Ymd');
                if (strlen($input) <= 6) {
                    $pref_thn = 20;
                    $tahun = substr($input, 0, 2);
                    $bulan = substr($input, 2, 2);
                    $tgl = substr($input, 4, 2);

                    if (strlen($tahun) < 4) {
                        $tahun = $pref_thn . $tahun;
                    }
                    if (is_numeric($tahun) && is_numeric($bulan) && is_numeric($tgl)) {
                        $result = $tahun . $bulan . $tgl;
                    }
                }
                return $result;
                break;
            case 'yyyyddmm':
                $result = date('Ymd');
                if (strlen($input) <= 8) {
                    $tahun = substr($input, 0, 4);
                    $bulan = substr($input, 6, 2);
                    $tgl = substr($input, 4, 2);

                    if (is_numeric($tahun) && is_numeric($bulan) && is_numeric($tgl)) {
                        $result = $tahun . $bulan . $tgl;
                    }
                }
                return $result;
                break;
            case 'yyddmm':
                $result = date('Ymd');
                if (strlen($input) <= 6) {
                    $pref_thn = 20;
                    $tahun = substr($input, 0, 2);
                    $bulan = substr($input, 4, 2);
                    $tgl = substr($input, 2, 2);

                    if (strlen($tahun) < 4) {
                        $tahun = $pref_thn . $tahun;
                    }
                    if (is_numeric($tahun) && is_numeric($bulan) && is_numeric($tgl)) {
                        $result = $tahun . $bulan . $tgl;
                    }
                }
                return $result;
                break;
            case 'ddmmyy':
                $result = date('Ymd');
                if (strlen($input) <= 6) {
                    $pref_thn = 20;
                    $tahun = substr($input, 4, 2);
                    $bulan = substr($input, 2, 2);
                    $tgl = substr($input, 0, 2);

                    if (strlen($tahun) < 4) {
                        $tahun = $pref_thn . $tahun;
                    }
                    if (is_numeric($tahun) && is_numeric($bulan) && is_numeric($tgl)) {
                        $result = $tahun . $bulan . $tgl;
                    }
                }
                return $result;
                break;
            case 'ddmmyyyy':
                $result = date('Ymd');
                if (strlen($input) <= 8) {
                    $tahun = substr($input, 4, 4);
                    $bulan = substr($input, 2, 2);
                    $tgl = substr($input, 0, 2);

                    if (is_numeric($tahun) && is_numeric($bulan) && is_numeric($tgl)) {
                        $result = $tahun . $bulan . $tgl;
                    }
                }
                return $result;
                break;
            case 'dd/mm/yy':
                $tanggal = explode('/', $input);
                $pref_thn = 20;
                $pref_def = 0;
                $tahun = isset($tanggal[2]) ? $tanggal[2] : $error_format++;
                $bulan = isset($tanggal[1]) ? $tanggal[1] : $error_format++;
                $tgl = isset($tanggal[0]) ? $tanggal[0] : $error_format++;

                if (strlen($tahun) < 4) {
                    $tahun = $pref_thn . $tahun;
                }
                if (strlen($bulan) < 2) {
                    $bulan = $pref_def . $bulan;
                } else
                if (strlen($bulan) == 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmm');
                } else
                if (strlen($bulan) > 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmmm');
                }
                if (strlen($tgl) < 2) {
                    $tgl = $pref_def . $tgl;
                }


                (empty($bulan) ? $error_format++ : $error_format);
                if ($error_format > 0) {
                    return date('Ymd');
                } else {
                    return $tahun . $bulan . $tgl;
                }
                break;
            case 'yy/mm/dd':
                $tanggal = explode('/', $input);
                $pref_thn = 20;
                $pref_def = 0;
                $tahun = isset($tanggal[0]) ? $tanggal[0] : $error_format++;
                $bulan = isset($tanggal[1]) ? $tanggal[1] : $error_format++;
                $tgl = isset($tanggal[2]) ? $tanggal[2] : $error_format++;

                if (strlen($tahun) < 4) {
                    $tahun = $pref_thn . $tahun;
                }
                if (strlen($bulan) < 2) {
                    $bulan = $pref_def . $bulan;
                } else
                if (strlen($bulan) == 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmm');
                } else
                if (strlen($bulan) > 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmmm');
                }
                if (strlen($tgl) < 2) {
                    $tgl = $pref_def . $tgl;
                }

                (empty($bulan) ? $error_format++ : $error_format);
                if ($error_format > 0) {
                    return date('Ymd');
                } else {
                    return $tahun . $bulan . $tgl;
                }
                break;
            case 'yy/dd/mm':
                $tanggal = explode('/', $input);
                $pref_thn = 20;
                $pref_def = 0;
                $tahun = isset($tanggal[0]) ? $tanggal[0] : $error_format++;
                $bulan = isset($tanggal[2]) ? $tanggal[2] : $error_format++;
                $tgl = isset($tanggal[1]) ? $tanggal[1] : $error_format++;

                if (strlen($tahun) < 4) {
                    $tahun = $pref_thn . $tahun;
                }
                if (strlen($bulan) < 2) {
                    $bulan = $pref_def . $bulan;
                } else
                if (strlen($bulan) == 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmm');
                } else
                if (strlen($bulan) > 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmmm');
                }
                if (strlen($tgl) < 2) {
                    $tgl = $pref_def . $tgl;
                }

                (empty($bulan) ? $error_format++ : $error_format);
                if ($error_format > 0) {
                    return date('Ymd');
                } else {
                    return $tahun . $bulan . $tgl;
                }
                break;
            case 'dd-mm-yy':
                $tanggal = explode('-', $input);
                $pref_thn = 20;
                $pref_def = 0;
                $tahun = isset($tanggal[2]) ? $tanggal[2] : $error_format++;
                $bulan = isset($tanggal[1]) ? $tanggal[1] : $error_format++;
                $tgl = isset($tanggal[0]) ? $tanggal[0] : $error_format++;
                if (strlen($tahun) < 4) {
                    $tahun = $pref_thn . $tahun;
                }
                if (strlen($bulan) < 2) {
                    $bulan = $pref_def . $bulan;
                } else
                if (strlen($bulan) == 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmm');
                } else
                if (strlen($bulan) > 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmmm');
                }
                if (strlen($tgl) < 2) {
                    $tgl = $pref_def . $tgl;
                }

                (empty($bulan) ? $error_format++ : $error_format);
                if ($error_format > 0) {
                    return date('Ymd');
                } else {
                    return $tahun . $bulan . $tgl;
                }
                break;
            case 'yy-mm-dd':
                $tanggal = explode('-', $input);
                $pref_thn = 20;
                $pref_def = 0;
                $tahun = isset($tanggal[0]) ? $tanggal[0] : $error_format++;
                $bulan = isset($tanggal[1]) ? $tanggal[1] : $error_format++;
                $tgl = isset($tanggal[2]) ? $tanggal[2] : $error_format++;

                if (strlen($tahun) < 4) {
                    $tahun = $pref_thn . $tahun;
                }
                if (strlen($bulan) < 2) {
                    $bulan = $pref_def . $bulan;
                } else
                if (strlen($bulan) == 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmm');
                } else
                if (strlen($bulan) > 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmmm');
                }
                if (strlen($tgl) < 2) {
                    $tgl = $pref_def . $tgl;
                }

                (empty($bulan) ? $error_format++ : $error_format);
                if ($error_format > 0) {
                    return date('Ymd');
                } else {
                    return $tahun . $bulan . $tgl;
                }
                break;
            case 'yy-dd-mm':
                $tanggal = explode('-', $input);
                $pref_thn = 20;
                $pref_def = 0;
                $tahun = isset($tanggal[0]) ? $tanggal[0] : $error_format++;
                $bulan = isset($tanggal[2]) ? $tanggal[2] : $error_format++;
                $tgl = isset($tanggal[1]) ? $tanggal[1] : $error_format++;

                if (strlen($tahun) < 4) {
                    $tahun = $pref_thn . $tahun;
                }
                if (strlen($bulan) < 2) {
                    $bulan = $pref_def . $bulan;
                } else
                if (strlen($bulan) == 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmm');
                } else
                if (strlen($bulan) > 3) {
                    $bulan = $this->get_number_month_format($bulan, 'mmmm');
                }
                if (strlen($tgl) < 2) {
                    $tgl = $pref_def . $tgl;
                }

                (empty($bulan) ? $error_format++ : $error_format);
                if ($error_format > 0) {
                    return date('Ymd');
                } else {
                    return $tahun . $bulan . $tgl;
                }
                break;
            default :
                return date('Ymd');
        }
    }

    //get number month format
    function get_number_month_format($bulan, $format) {
        $bulan = strtolower($bulan);
        $format = strtolower($format);
        if (strcmp('mmm', $format) == 0) {
            switch ($bulan) {
                case 'jan':
                    return '01';
                    break;
                case 'feb':
                    return '02';
                    break;
                case 'mar':
                    return '03';
                    break;
                case 'apr':
                    return '04';
                    break;
                case 'mei':
                case 'may':
                    return '05';
                    break;
                case 'jun':
                    return '06';
                    break;
                case 'jul':
                    return '07';
                    break;
                case 'agu':
                case 'aug':
                    return '08';
                    break;
                case 'sep':
                    return '09';
                    break;
                case 'okt':
                case 'oct':
                    return '10';
                    break;
                case 'nop':
                case 'nov':
                    return '11';
                    break;
                case 'des':
                case 'dec':
                    return '12';
                    break;
                default:
                    return '';
            }
        } else if (strcmp('mmmm', $format) == 0) {
            switch ($bulan) {
                case 'januari':
                case 'january':
                    return '01';
                    break;
                case 'februari':
                case 'february':
                    return '02';
                    break;
                case 'maret':
                case 'march':
                    return '03';
                    break;
                case 'april':
                    return '04';
                    break;
                case 'mei':
                case 'may':
                    return '05';
                    break;
                case 'juni':
                case 'june':
                    return '06';
                    break;
                case 'juli':
                case 'july':
                    return '07';
                    break;
                case 'agustus':
                case 'august':
                    return '08';
                    break;
                case 'september':
                    return '09';
                    break;
                case 'oktober':
                case 'october':
                    return '10';
                    break;
                case 'november':
                    return '11';
                    break;
                case 'desember':
                case 'december':
                    return '12';
                    break;
                default:
                    return '';
            }
        }
    }

    /*
     * Sort array
     */

    function sort_by_one_key(array $array, $key, $asc = true) {
        $result = array();
        $values = array();
        foreach ($array as $id => $value) {
            $values[$id] = isset($value[$key]) ? $value[$key] : '';
        }
        if ($asc) {
            asort($values);
        } else {
            arsort($values);
        }
        foreach ($values as $key => $value) {
            $result[$key] = $array[$key];
        }
        return $result;
    }

    //replace if contains spesial character
    function replace_special_char(array $array, $delimiter) {
        $this->load->model('m_preferences');
        $result = array();
        // get spesial character
        $spesial = $this->m_preferences->get_preferences_by_group_name(array('settings', 'special_char'));
        $spesial = $spesial['pref_value'];
        //replace if match with spesial character and output delimiter
        $regex = "/[\\" . $spesial . "$delimiter]/";
        $result['value'] = preg_replace("$regex", " ", $array['value']);
        $result['status'] = $array['status'];
        //return result
        return $result;
    }

    //replace format email gdff
    function gdff_format_email($email = '') {
        $result = '';
        //--explode
        $fs_reg = explode("/", $email);
        //--
        $tw_reg = array();
        foreach ($fs_reg as $val) {
            if ($val != 'ACC' && !empty($val)) {
                $tw_reg[] = explode('email+', $val);
            }
        }
        //--
        end($tw_reg);
        $key_last = key($tw_reg);
        //--
        $email_address = '';
        $domain = '';
        //--
        foreach ($tw_reg as $key => $val) {
            if ($key < $key_last) {
                foreach ($val as $value) {
                    if (!empty($value)) {
                        $email_address .= $value;
                    }
                }
            }
        }
        //--
        if (!empty($tw_reg)) {
            foreach (end($tw_reg) as $val) {
                if (!empty($val)) {
                    $domain = str_replace('+', '@', $val);
                }
            }
        }
        $result = $email_address . $domain;
        //--
        //return result
        return $result;
    }

    //replace format email gdff
    function format_email_to_gdff($email = '') {
        $result = '';
        //--
        $pref_address = '/ACC/EMAIL+';
        $pref_domain = '//EMAIL++';
        //--explode
        $fs_reg = explode("@", $email);
        $result = $pref_address . $fs_reg[0] . $pref_domain . $fs_reg[1];
        //--
        //return result
        return $result;
    }

    //set path for mandiri encyption key path
    public function set_path() {
        //delete if exist file bat and regedit
        $file_reg = FCPATH . 'system\\plugins\\gpgengine\\regedit.reg';
        $file_bat = FCPATH . 'system\\plugins\\gpgengine\\batch.bat';
        if (is_file($file_reg)) {
            unlink($file_reg);
        }
        if (is_file($file_bat)) {
            unlink($file_bat);
        }

        // set path for encryption engine
        $fcpath = str_replace("\\", "\\\\", FCPATH);
        $loc_regfile = $fcpath . "system\\\\plugins\\\\gpgengine\\\\regedit.reg";
        $batch_command = "@ECHO OFF\n";
        $batch_command.= "REGEDIT /S $loc_regfile \n";
        $batch_command.= "CLS\n";
        $batch_command.= "EXIT\n";
        $loc_batch = FCPATH . "system\\plugins\\gpgengine\\batch.bat";
        //unlink if exist this file (batch.bat)
        if (is_file($loc_batch)) {
            unlink($loc_batch);
        }
        if (!$handle = fopen($loc_batch, 'a')) {
            
        }
        if (fwrite($handle, $batch_command) === FALSE) {
            
        }
        fclose($handle);
        //execute batch file
        $command_batch = FCPATH . "system\\plugins\\gpgengine\\batch.bat";
        $loc_regfile = FCPATH . "system\\plugins\\gpgengine\\regedit.reg";
        //--
        $gpgpath = $fcpath . "resource\\\\doc\\\\encryption\\\gpg\\\\key";
        $gpgpath_exe = $fcpath . "system\\\\plugins\\\\gpgengine\\\\gpg.exe";
        $gpgpath_locale = $fcpath . "system\\\\plugins\\\\gpgengine\\\\Locale";
        //--
        $reg_content = "REGEDIT4\n\n\n";
        $reg_content.= "[HKEY_LOCAL_MACHINE\Software\GNU]\n\n";
        $reg_content.= "[HKEY_LOCAL_MACHINE\Software\GNU\GNUPG]\n\n";
        $reg_content.= "[HKEY_LOCAL_MACHINE\Software\GNU\GNUPG]\n";
        $reg_content.= '"HomeDir"="' . $gpgpath . '"';
        $reg_content.= "\n";
        $reg_content.= '"gpgProgram"="' . $gpgpath_exe . '"';
        $reg_content.= "\n";
        $reg_content.= "[HKEY_CURRENT_USER\Control Panel\Mingw32]\n\n";
        $reg_content.= "[HKEY_CURRENT_USER\Control Panel\Mingw32\NLS]\n\n";
        $reg_content.= "[HKEY_CURRENT_USER\Control Panel\Mingw32\NLS]\n";
        $reg_content.= '"MODir"="' . $gpgpath_locale . '"';
        //create file regedit, unlink if exist this file (regedit.reg)
        if (is_file($loc_regfile)) {
            unlink($loc_regfile);
        }
        if (!$handle = fopen($loc_regfile, 'a')) {
            
        }
        if (fwrite($handle, $reg_content) === FALSE) {
            
        }
        fclose($handle);
        exec("$command_batch");
    }

}